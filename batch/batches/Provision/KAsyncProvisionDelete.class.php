<?php
/**
 * Will provision new live stram.
 *
 * 
 * @package Scheduler
 * @subpackage Provision
 */
class KAsyncProvisionDelete extends KJobHandlerWorker
{
	/* (non-PHPdoc)
	 * @see KBatchBase::getType()
	 */
	public static function getType()
	{
		return BorhanBatchJobType::PROVISION_DELETE;
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::exec()
	 */
	protected function exec(BorhanBatchJob $job)
	{
		return $this->provision($job, $job->data);
	}
	
	/* (non-PHPdoc)
	 * @see KJobHandlerWorker::getMaxJobsEachRun()
	 */
	protected function getMaxJobsEachRun()
	{
		return 1;
	}
	
	protected function provision(BorhanBatchJob $job, BorhanProvisionJobData $data)
	{
		$job = $this->updateJob($job, null, BorhanBatchJobStatus::QUEUED);
	
		$engine = KProvisionEngine::getInstance( $job->jobSubType , $data);
		
		if ( $engine == null )
		{
			$err = "Cannot find provision engine [{$job->jobSubType}] for job id [{$job->id}]";
			return $this->closeJob($job, BorhanBatchJobErrorTypes::APP, BorhanBatchJobAppErrors::ENGINE_NOT_FOUND, $err, BorhanBatchJobStatus::FAILED);
		}
		
		BorhanLog::info( "Using engine: " . $engine->getName() );
	
		$results = $engine->delete($job, $data);

		if($results->status == BorhanBatchJobStatus::FINISHED)
			return $this->closeJob($job, null, null, null, BorhanBatchJobStatus::FINISHED, $results->data);
			
		return $this->closeJob($job, BorhanBatchJobErrorTypes::APP, null, $results->errMessage, $results->status, $results->data);
	}
}
