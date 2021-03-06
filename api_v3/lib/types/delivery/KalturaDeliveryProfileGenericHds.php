<?php
/**
 * @package api
 * @subpackage objects
 */
class KalturaDeliveryProfileGenericHds extends KalturaDeliveryProfile {
	
	/**
	 * @var string
	 */
	public $pattern;
	
	
	/**
	 * rendererClass
	 * @var string
	 */
	public $rendererClass;
	
	
	private static $map_between_objects = array
	(
			"pattern",
			"rendererClass",
	);
	
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}

