<?php
/**
 * @package plugins.schedule
 * @subpackage api.objects
 */
class BorhanLiveEntryScheduleResource extends BorhanScheduleResource
{
	/**
	 * @var string
	 * @minLength 1
	 * @maxLength 256
	 */
	public $entryId;
	
	/*
	 * Mapping between the field on this object (on the left) and the setter/getter on the entry object (on the right)  
	 */
	private static $map_between_objects = array 
	(	
		'entryId',
	);
		 
	/* (non-PHPdoc)
	 * @see BorhanObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * {@inheritDoc}
	 * @see BorhanScheduleResource::getScheduleResourceType()
	 */
	protected function getScheduleResourceType()
	{
		return ScheduleResourceType::LIVE_ENTRY;
	}
		 
	/* (non-PHPdoc)
	 * @see BorhanObject::validateForInsert($propertiesToSkip)
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyNotNull('entryId');
		return parent::validateForInsert($propertiesToSkip);
	}
		 
	/* (non-PHPdoc)
	 * @see BorhanObject::validateForUpdate($sourceObject, $propertiesToSkip)
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		if($this->entryId instanceof BorhanNullField)
		{
			throw new BorhanAPIException(BorhanErrors::PROPERTY_VALIDATION_CANNOT_BE_NULL, $this->getFormattedPropertyNameWithClassName('entryId'));
		}
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::toObject($object_to_fill, $props_to_skip)
	 */
	public function toObject($sourceObject = null, $propertiesToSkip = array())
	{
		if(is_null($sourceObject))
		{
			$sourceObject = new LiveEntryScheduleResource();
		}
		
		return parent::toObject($sourceObject, $propertiesToSkip);
	}
}