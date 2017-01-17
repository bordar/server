<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanFeatureStatus extends BorhanObject
{
	/**
	 * @var BorhanFeatureStatusType
	 */
	public $type;
	
	/**
	 * @var int
	 */
	public $value;
	
	private static $map_between_objects = array
	(
		"type",
		"value",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}		
}