<?php
/**
 * @package plugins.exampleIntegration
 * @subpackage api.objects
 */
class BorhanExampleIntegrationJobProviderData extends BorhanIntegrationJobProviderData
{
	/**
	 * Just an example
	 * 
	 * @var string
	 */
	public $exampleUrl;
	
	private static $map_between_objects = array
	(
		"exampleUrl" ,
	);

	/* (non-PHPdoc)
	 * @see BorhanObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
}
