<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanConcatJobData extends BorhanJobData
{
	/**
	 * Source files to be concatenated
	 * @var BorhanStringArray
	 */
	public $srcFiles;
	
	/**
	 * Output file
	 * @var string
	 */
	public $destFilePath;
	
	/**
	 * Flavor asset to be ingested with the output
	 * @var string
	 */
	public $flavorAssetId;
	
	/**
	 * Clipping offset in seconds
	 * @var float
	 */
	public $offset;
	
	/**
	 * Clipping duration in seconds
	 * @var float
	 */
	public $duration;

	/**
	 * duration of the concated video
	 * @var float
	 */
	public $concatenatedDuration;

	private static $map_between_objects = array
	(
		'srcFiles',
		'destFilePath',
		'flavorAssetId',
		'offset',
		'duration',
		'concatenatedDuration',
	);

	/* (non-PHPdoc)
	 * @see BorhanObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::toObject()
	 */
	public function toObject($dbData = null, $props_to_skip = array()) 
	{
		if(is_null($dbData))
			$dbData = new kConcatJobData();
			
		return parent::toObject($dbData, $props_to_skip);
	}
}
