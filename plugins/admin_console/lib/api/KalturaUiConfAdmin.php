<?php
/**
 * @package plugins.adminConsole
 * @subpackage api.objects
 */
class BorhanUiConfAdmin extends BorhanUiConf
{
	/**
	 * @var bool
	 */
	public $isPublic;
	
	public function doFromObject($source_object, BorhanDetachedResponseProfile $responseProfile = null)
	{
		if ($source_object instanceof uiConf)
		{
			if ($source_object->getDisplayInSearch() == mySearchUtils::DISPLAY_IN_SEARCH_BORHAN_NETWORK)
				$this->isPublic = true;
			else
				$this->isPublic = false;
		}
		
		return parent::doFromObject($source_object, $responseProfile);
	}
	
	public function toObject($object_to_fill = null, $props_to_skip = array()) 
	{
		if ($object_to_fill instanceof uiConf)
		{
			if ($this->isPublic === true)
				$object_to_fill->setDisplayInSearch(mySearchUtils::DISPLAY_IN_SEARCH_BORHAN_NETWORK);
			else
				$object_to_fill->setDisplayInSearch(mySearchUtils::DISPLAY_IN_SEARCH_NONE);
		}
		return parent::toObject($object_to_fill, $props_to_skip);
	}
}