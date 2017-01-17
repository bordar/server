<?php

/**
 * @package plugins.scheduledTask
 * @subpackage lib.objectTaskEngine
 */
class KObjectTaskDeleteLocalContentEngine extends KObjectTaskEntryEngineBase
{

	/**
	 * @param BorhanBaseEntry $object
	 */
	function processObject($object)
	{
		$client = $this->getClient();
		$entryId = $object->id;
		BorhanLog::info("Deleting local content for entry [$entryId]");
		$flavors = $this->getEntryFlavors($object, $client);
		if (!count($flavors))
			return;

		foreach ($flavors as $flavor)
		{
			$this->deleteFlavor($flavor->id, $flavor->partnerId);
		}
	}

	protected function getEntryFlavors($object){
		$client = $this->getClient();
		$pager = new BorhanFilterPager();
		$pager->pageSize = 500; // use max size, throw exception in case we got more than 500 flavors where pagination is not supported
		$filter = new BorhanFlavorAssetFilter();
		$filter->entryIdEqual = $object->id;
		$this->impersonate($object->partnerId);
		try
		{
			$flavorsResponse = $client->flavorAsset->listAction($filter);
			$this->unimpersonate();
		}
		catch(Exception $ex)
		{
			$this->unimpersonate();
			throw $ex;
		}
		if ($flavorsResponse->totalCount > $pager->pageSize)
			throw new Exception('Too many flavors were found where pagination is not supported');

		$flavors = $flavorsResponse->objects;
		BorhanLog::info('Found '.count($flavors). ' flavors');
		return $flavors;
	}


	/**
	 * @param $id
	 * @param $partnerId
	 */
	protected function deleteFlavor($id, $partnerId)
	{
		$client = $this->getClient();
		$this->impersonate($partnerId);
		try
		{
			$client->flavorAsset->deleteLocalContent($id);
			BorhanLog::info("Local content of flavor id [$id] was deleted");
			$this->unimpersonate();
		}
		catch(Exception $ex)
		{
			$this->unimpersonate();
			BorhanLog::err($ex->getMessage());
			BorhanLog::err("Failed to delete local content of flavor id [$id]");
		}
	}
}