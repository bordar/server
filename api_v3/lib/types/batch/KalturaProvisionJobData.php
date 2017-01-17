<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanProvisionJobData extends BorhanJobData
{
	/**
	 * @var string
	 */
	public $streamID;
	
	/**
	 * @var string
	 */
	public $backupStreamID;
	
	/**
	 * @var string
	 */
	public $rtmp;
 	
	/**
	 * @var string
	 */
	public $encoderIP;
 	
	/**
	 * @var string
	 */
	public $backupEncoderIP;
 	
	/**
	 * @var string
	 */
	public $encoderPassword;
 	
	/**
	 * @var string
	 */
	public $encoderUsername;
 	
	/**
	 * @var int
	 */
	public $endDate;
 	
	/**
	 * @var string
	 */
	public $returnVal;
	
	/**
	 * @var int
	 */
	public $mediaType;
	
	/**
	 * @var string
	 */
	public $primaryBroadcastingUrl;
	
	/**
	 * @var string
	 */
	public $secondaryBroadcastingUrl;
	
	/**
	 * @var string
	 */
	public $streamName;
    
	private static $map_between_objects = array
	(
		"streamID",
		"backupStreamID",
		"rtmp",
		"encoderIP",
		"backupEncoderIP",
		"encoderPassword",
		"encoderUsername",
		"endDate",
		"returnVal",
		"mediaType",
		"primaryBroadcastingUrl",
		"secondaryBroadcastingUrl",
		"streamName",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}

	
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new kProvisionJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
	
	/**
	 * @param string $subType
	 * @return int
	 */
	public function toSubType($subType)
	{
		// TODO - change to pluginable enum to support more providers
		return $subType;
	}
	
	/**
	 * @param int $subType
	 * @return string
	 */
	public function fromSubType($subType)
	{
		switch ($subType)
		{
			case BorhanSourceType::AKAMAI_LIVE:
			case BorhanSourceType::AKAMAI_UNIVERSAL_LIVE:
				return $subType;
				break;
			default:
				return kPluginableEnumsManager::coreToApi('EntrySourceType', $subType);
				break;
		}
	}
	

	/**
	 * Return instance of BorhanProvisionJobData according to job sub-type
	 * @param int $jobSubType
	 * @return BorhanProvisionJobData
	 */
	public static function getJobDataInstance ($jobSubType)
	{
		BorhanLog::info ("Determining correct job data based on jobSubType $jobSubType");
		switch ($jobSubType)
		{
			case BorhanSourceType::AKAMAI_LIVE:
				return new BorhanAkamaiProvisionJobData();
				break;
			case BorhanSourceType::AKAMAI_UNIVERSAL_LIVE:
				return new BorhanAkamaiUniversalProvisionJobData();
				break;
			default:
				return BorhanPluginManager::loadObject('BorhanProvisionJobData', $jobSubType);
				break;
		
		}
	}
}
