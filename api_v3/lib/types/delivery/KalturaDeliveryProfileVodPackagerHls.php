<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanDeliveryProfileVodPackagerHls extends BorhanDeliveryProfileVodPackagerPlayServer
{
	/**
	 * @var bool
	 */
	public $allowFairplayOffline;

	private static $map_between_objects = array
	(
		'allowFairplayOffline',
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}