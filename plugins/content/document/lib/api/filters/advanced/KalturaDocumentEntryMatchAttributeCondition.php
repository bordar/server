<?php

/**
 * Auto-generated class.
 * 
 * Used to search BorhanDocumentEntry attributes. Use BorhanDocumentEntryMatchAttribute enum to provide attribute name.
*/
class BorhanDocumentEntryMatchAttributeCondition extends BorhanSearchMatchAttributeCondition
{
	/**
	 * @var BorhanDocumentEntryMatchAttribute
	 */
	public $attribute;

	private static $mapBetweenObjects = array
	(
		"attribute" => "attribute",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects() , self::$mapBetweenObjects);
	}

	protected function getIndexClass()
	{
		return 'entryIndex';
	}
}

