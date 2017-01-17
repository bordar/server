<?php
/**
 * This worker converts recorded live media files to MPEG-TS
 *
 * @package Scheduler
 * @subpackage Conversion
 */
class KAsyncConvertLiveSegment extends KJobHandlerWorker
{
	/**
	 * constants duplicated from assetParams.php
	 */
	const TAG_RECORDING_ANCHOR = 'recording_anchor';

	/**
	 * @var string
	 */
	protected $localTempPath;
	
	/**
	 * @var string
	 */
	protected $sharedTempPath;
	
	/**
	 * (non-PHPdoc)
	 * @see KBatchBase::getJobType()
	 */
	protected function getJobType()
	{
		return BorhanBatchJobType::CONVERT_LIVE_SEGMENT;
	}
	
	public static function getType()
	{
		return BorhanBatchJobType::CONVERT_LIVE_SEGMENT;
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::run()
	 */
	public function run($jobs = null)
	{
		// creates a temp file path
		$this->localTempPath = self::$taskConfig->params->localTempPath;
		$this->sharedTempPath = self::$taskConfig->params->sharedTempPath;
		
		$res = self::createDir($this->localTempPath);
		if(! $res)
		{
			BorhanLog::err("Cannot continue conversion without temp local directory");
			return null;
		}
		$res = self::createDir($this->sharedTempPath);
		if(! $res)
		{
			BorhanLog::err("Cannot continue conversion without temp shared directory");
			return null;
		}
		
		return parent::run($jobs);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(BorhanBatchJob $job)
	{
		return $this->convert($job, $job->data);
	}
	
	protected function convert(BorhanBatchJob $job, BorhanConvertLiveSegmentJobData $data)
	{
		$this->updateJob($job, "File conversion started", BorhanBatchJobStatus::PROCESSING);
		$jobData = $job->data;

		$ffmpegBin = KBatchBase::$taskConfig->params->ffmpegCmd;
		$ffprobeBin = isset(KBatchBase::$taskConfig->params->ffprobeCmd)? KBatchBase::$taskConfig->params->ffprobeCmd: "ffprobe";

		$fileName = "{$job->entryId}_{$jobData->assetId}_{$data->mediaServerIndex}.{$job->id}.ts";
		$localTempFilePath = $this->localTempPath . DIRECTORY_SEPARATOR . $fileName;
		$sharedTempFilePath = $this->sharedTempPath . DIRECTORY_SEPARATOR . $fileName;

		$result = $this->convertRecordedToMPEGTS($ffmpegBin, $ffprobeBin, $data->srcFilePath, $localTempFilePath);
		if(! $result)
			return $this->closeJob($job, BorhanBatchJobErrorTypes::RUNTIME, null, "Failed to convert file", BorhanBatchJobStatus::FAILED);

		// write AMF data to a file in shared storage
		self::generateAmfData($job, $data, $localTempFilePath);

		return $this->moveFile($job, $data, $localTempFilePath, $sharedTempFilePath);
	}

	protected function generateAmfData(BorhanBatchJob $job, BorhanConvertLiveSegmentJobData $data, $localTempFilePath)
	{
		$mediaInfoBin = isset(KBatchBase::$taskConfig->params->mediaInfoCmd)? KBatchBase::$taskConfig->params->mediaInfoCmd: "mediainfo";

		// only extract the data if it's the primary server since we don't use this data in the secondary
		if ($data->mediaServerIndex == BorhanEntryServerNodeType::LIVE_PRIMARY) {
			try {

				// get the asset to check if it has a assetParams::TAG_RECORDING_ANCHOR tag.
				// note that assetParams::TAG_RECORDING_ANCHOR is not exposed in the API so I use it's string value.
				KBatchBase::impersonate($job->partnerId);
				$asset = KBatchBase::$kClient->flavorAsset->get($data->assetId);
				KBatchBase::unimpersonate();
				if (strpos($asset->tags,self::TAG_RECORDING_ANCHOR) == false) {
					return;
				}

				// Extract AMF data from all data frames in the segment
				$amfParser = new KAMFMediaInfoParser($data->srcFilePath);
				$amfArray = $amfParser->getAMFInfo();

				// run mediaInfo on $localTempFilePath to get it's duration, and store it in the job data
				$mediaInfoParser = new KMediaInfoMediaParser($localTempFilePath, $mediaInfoBin);
				$duration = $mediaInfoParser->getMediaInfo()->videoDuration;

				array_unshift($amfArray, $duration);

				$amfFileName = "{$data->entryId}_{$data->assetId}_{$data->mediaServerIndex}_{$data->fileIndex}.data";
				$localTempAmfFilePath = $this->localTempPath . DIRECTORY_SEPARATOR . $amfFileName;
				$sharedTempAmfFilePath = $this->sharedTempPath . DIRECTORY_SEPARATOR . $amfFileName;

				file_put_contents($localTempAmfFilePath, serialize($amfArray));

				self::moveDataFile($data, $localTempAmfFilePath, $sharedTempAmfFilePath);
			}
			catch(Exception $ex) {
				KBatchBase::unimpersonate();
				BorhanLog::warning('failed to extract AMF data or duration data ' . print_r($ex));
			}
		}
	}

	/**
	 * @param BorhanBatchJob $job
	 * @param BorhanConcatJobData $data
	 * @param string $localTempFilePath
	 * @param string $sharedTempFilePath
	 * @return BorhanBatchJob
	 */
	protected function moveFile(BorhanBatchJob $job, BorhanConvertLiveSegmentJobData $data, $localTempFilePath, $sharedTempFilePath)
	{
		$this->updateJob($job, "Moving file from [$localTempFilePath] to [$sharedTempFilePath]", BorhanBatchJobStatus::MOVEFILE);
		
		kFile::moveFile($localTempFilePath, $sharedTempFilePath, true);
		clearstatcache();
		$fileSize = kFile::fileSize($sharedTempFilePath);
		
		$this->setFilePermissions($sharedTempFilePath);
		
		if(! $this->checkFileExists($sharedTempFilePath, $fileSize))
			return $this->closeJob($job, BorhanBatchJobErrorTypes::APP, BorhanBatchJobAppErrors::NFS_FILE_DOESNT_EXIST, 'File not moved correctly', BorhanBatchJobStatus::RETRY);
		
		$data->destFilePath = $sharedTempFilePath;
		return $this->closeJob($job, null, null, 'Succesfully moved file', BorhanBatchJobStatus::FINISHED, $data);
	}

	protected function moveDataFile(BorhanConvertLiveSegmentJobData $data, $localTempAmfFilePath, $sharedTempAmfFilePath)
	{
		BorhanLog::debug('moving file from ' . $localTempAmfFilePath . ' to ' . $sharedTempAmfFilePath);
		kFile::moveFile($localTempAmfFilePath, $sharedTempAmfFilePath, true);
		clearstatcache();
		$fileSize = kFile::fileSize($sharedTempAmfFilePath);
		$this->setFilePermissions($sharedTempAmfFilePath);
		if(! $this->checkFileExists($sharedTempAmfFilePath, $fileSize))
			BorhanLog::warning('failed to move file to ' . $sharedTempAmfFilePath);
		else
			$data->destDataFilePath = $sharedTempAmfFilePath;
	}
	
	protected function convertRecordedToMPEGTS($ffmpegBin, $ffprobeBin, $inFilename, $outFilename)
	{
		$cmdStr = "$ffmpegBin -i $inFilename -c copy -bsf:v h264_mp4toannexb -f mpegts -y $outFilename 2>&1";
		
		BorhanLog::debug("Executing [$cmdStr]");
		$output = system($cmdStr, $rv);

		/*
		 * Anomaly detection -
		*	Look for the time of the first KF in the source file.
		*	Should be less than 200 msec
		*	Currnetly - just logging
		*/
		$detectInterval = 10;		// sec
		$maxKeyFrameTime = 0.200;	// sec
		$kfArr=KFFMpegMediaParser::retrieveKeyFrames($ffprobeBin, $inFilename,0,$detectInterval);
		BorhanLog::log("KeyFrames:".print_r($kfArr,1));
		if(count($kfArr)==0){
			BorhanLog::log("Anomaly detection: NO Keyframes in the detection interval ($detectInterval sec)");
		}
		else if($kfArr[0]>$maxKeyFrameTime){
			BorhanLog::log("Anomaly detection: ERROR, first KF at ($kfArr[0] sec), max allowed ($maxKeyFrameTime sec)");
		}
		else {
			BorhanLog::log("Anomaly detection: OK, first KF at ($kfArr[0] sec), max allowed ($maxKeyFrameTime sec)");
		}
		
		return ($rv == 0) ? true : false;
	}
}