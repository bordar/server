<?php
/**
 * @package plugins.thumbCuePoint
 * @subpackage api.objects
 */
class KalturaThumbCuePoint extends KalturaCuePoint
{
	/**
	 * @var string
	 * @insertonly
	 */
	public $timedThumbAssetId;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $description;
	
	/**
	 * @var string
	 * @filter like,mlikeor,mlikeand
	 */
	public $title;

	public function __construct()
	{
		$this->cuePointType = ThumbCuePointPlugin::getApiValue(ThumbCuePointType::THUMB);
	}
	
	private static $map_between_objects = array
	(
		"timedThumbAssetId",
		"title" => "name",
		"description" => "text",
	);
	
	/* (non-PHPdoc)
	 * @see KalturaCuePoint::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaObject::toInsertableObject()
	 */
	public function toInsertableObject($object_to_fill = null, $props_to_skip = array())
	{
		if(is_null($object_to_fill))
			$object_to_fill = new ThumbCuePoint();
			
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see KalturaCuePoint::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull("timedThumbAssetId");
		
		$this->validateTimedThumbAssetId();
		
		parent::validateForInsert($propertiesToSkip);
	}
	
	public function validateTimedThumbAssetId()
	{
		$timedThumb = assetPeer::retrieveByPK($this->timedThumbAssetId);
		
		if(!$timedThumb || $timedThumb->getType() != kPluginableEnumsManager::apiToCore('assetType', KalturaAssetType::TIMED_THUMB_ASSET))
			throw new KalturaAPIException(KalturaErrors::THUMB_ASSET_ID_IS_NOT_TIMED_THUMB_TYPE, $this->timedThumbAssetId);
	}
}
