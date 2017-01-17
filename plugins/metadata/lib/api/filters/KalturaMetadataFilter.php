<?php
/**
 * @package plugins.metadata
 * @subpackage api.filters
 */
class BorhanMetadataFilter extends BorhanMetadataBaseFilter
{	
	static private $map_between_objects = array
	(
		"metadataObjectTypeEqual" => "_eq_object_type",
	);

	/* (non-PHPdoc)
	 * @see BorhanMetadataBaseFilter::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/**
	 * Instantiate default value
	 */
	public function __construct()
	{
		// default value for backward compatibility
		$this->metadataObjectTypeEqual = MetadataObjectType::ENTRY;
	}

	/* (non-PHPdoc)
	 * @see BorhanFilter::getCoreFilter()
	 */
	protected function getCoreFilter()
	{
		return new MetadataFilter();
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::toObject()
	 */
	public function toObject($object_to_fill = null, $props_to_skip = array()) 
	{
		if($this->metadataObjectTypeEqual == BorhanMetadataObjectType::USER)
		{
			if ($this->objectIdEqual)
			{
				$kuser = kuserPeer::getKuserByPartnerAndUid(kCurrentContext::getCurrentPartnerId(), $this->objectIdEqual);
				if($kuser)				
					$this->objectIdEqual = $kuser->getId();
			}
				
			if ($this->objectIdIn)
			{
				$kusers = kuserPeer::getKuserByPartnerAndUids(kCurrentContext::getCurrentPartnerId(), explode(',', $this->objectIdIn));
				
				$kusersIds = array();
				foreach($kusers as $kuser)				
					$kusersIds[] = $kuser->getId();
				
				$this->objectIdIn = implode(',', $kusersIds);
			}
		}
		
		return parent::toObject($object_to_fill, $props_to_skip);
	}
	
	/* (non-PHPdoc)
	 * @see BorhanRelatedFilter::getListResponse()
	 */
	public function getListResponse(BorhanFilterPager $pager, BorhanDetachedResponseProfile $responseProfile = null)
	{
		if (kEntitlementUtils::getEntitlementEnforcement() && (is_null($this->objectIdIn) && is_null($this->objectIdEqual))&& kConf::hasParam('metadata_list_without_object_filtering_partners') &&
        !in_array(kCurrentContext::getCurrentPartnerId(), kConf::get('metadata_list_without_object_filtering_partners')))
			throw new BorhanAPIException(MetadataErrors::MUST_FILTER_ON_OBJECT_ID);

		if (!$this->metadataObjectTypeEqual)
			throw new BorhanAPIException(MetadataErrors::MUST_FILTER_ON_OBJECT_TYPE);
				
		if ($this->metadataObjectTypeEqual == MetadataObjectType::CATEGORY)
		{
			if ($this->objectIdEqual)
			{
				$categoryIds = array($this->objectIdEqual);
			}
			else if ($this->objectIdIn)
			{
				$categoryIds = explode(',', $this->objectIdIn);
			}
			
			if($categoryIds)
			{
				$categories = categoryPeer::retrieveByPKs($categoryIds);
				if(!count($categories))
				{
					$response = new BorhanMetadataListResponse();
					$response->objects = new BorhanMetadataArray();
					$response->totalCount = 0;
					return $response;
				}
				
				$categoryIds = array();
				foreach($categories as $category)
					$categoryIds[] = $category->getId();
				
				$this->objectIdEqual = null;
				$this->objectIdIn = implode(',', $categoryIds);
			}
		}
	
		$metadataFilter = $this->toObject();

		$c = BorhanCriteria::create(MetadataPeer::OM_CLASS);
		$metadataFilter->attachToCriteria($c);

		$pager->attachToCriteria($c);
		$list = MetadataPeer::doSelect($c);
		
		$response = new BorhanMetadataListResponse();
		$response->objects = BorhanMetadataArray::fromDbArray($list, $responseProfile);
		
		if($c instanceof SphinxMetadataCriteria)
		{
			$response->totalCount = $c->getRecordsCount();
		}
		elseif($pager->pageIndex == 1 && count($response->objects) < $pager->pageSize)
		{
			$response->totalCount = count($response->objects);
		}
		else
		{
			$pager->detachFromCriteria($c);
			$response->totalCount = MetadataPeer::doCount($c);
		}
		
		return $response;
	}
}
