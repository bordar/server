<?php
/**
 * @package api
 * @subpackage filters
 */
class BorhanEntryCuePointSearchFilter extends BorhanSearchItem
{
	/**
	 * @var string
	 */
	public $cuePointsFreeText;
	
	/**
	 * @dynamicType BorhanCuePointType
	 * @var string
	 */
	public $cuePointTypeIn;
	
	/**
	 * @var int
	 */
	public $cuePointSubTypeEqual;
	
	private static $map_between_objects = array
	(
		"cuePointsFreeText",
		"cuePointTypeIn",
		"cuePointSubTypeEqual",
	);

	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(!$object_to_fill)
			$object_to_fill = new EntryCuePointSearchFilter();
			
		return parent::toObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::validateForUsage($sourceObject, $propertiesToSkip)
	 */
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		parent::validateForUsage($sourceObject, $propertiesToSkip);
		
		if(isset($this->cuePointSubTypeEqual) && !isset($this->cuePointTypeIn))
			throw new BorhanAPIException( BorhanErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, $this->getFormattedPropertyNameWithClassName('cuePointSubTypeEqual') );
	}
}
