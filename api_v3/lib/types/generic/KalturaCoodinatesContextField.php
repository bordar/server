<?php
/**
 * Represents the current request country context as calculated based on the IP address
 * 
 * @package api
 * @subpackage objects
 */
class BorhanCoordinatesContextField extends BorhanStringField
{
	/**
	 * The ip geo coder engine to be used
	 * 
	 * @var BorhanGeoCoderType
	 */
	public $geoCoderType = geoCoderType::BORHAN;
	
	static private $map_between_objects = array
	(
		'geoCoderType',
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::toObject()
	 */
	public function toObject($dbObject = null, $skip = array())
	{
		if(!$dbObject)
			$dbObject = new kCoordinatesContextField();
			
		return parent::toObject($dbObject, $skip);
	}
}