<?php
/**
 * @package plugins.doubleClickDistribution
 * @subpackage api.objects
 */
class BorhanDoubleClickDistributionProfile extends BorhanConfigurableDistributionProfile
{
	/**
	 * @var string
	 */
	public $channelTitle;
	
	/**
	 * @var string
	 */
	public $channelLink;
	
	/**
	 * @var string
	 */
	public $channelDescription;
	
	/**
	 * @readonly
	 * @var string
	 */
	public $feedUrl;
	
	/**
	 * @var string
	 */
	public $cuePointsProvider;
	
	/**
	 * @var string
	 */
	public $itemsPerPage;

	/**
	 * @var bool
	 */
	public $ignoreSchedulingInFeed;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)  
	 */
	private static $map_between_objects = array 
	(
		'channelTitle',
		'channelLink',
		'channelDescription',
		'feedUrl',
		'cuePointsProvider',
		'itemsPerPage',
		'ignoreSchedulingInFeed',
	);
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::validateForInsert($propertiesToSkip)
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$partnerId = kCurrentContext::getCurrentPartnerId();
		$partner = PartnerPeer::retrieveByPK($partnerId);
		if(!$partner)
			throw new BorhanAPIException(BorhanErrors::PARTNER_NOT_FOUND, $partnerId);
			
		if(!$partner->getPluginEnabled(DoubleClickDistributionPlugin::DEPENDENTS_ON_PLUGIN_NAME_CUE_POINT))
			throw new BorhanAPIException(BorhanErrors::PLUGIN_NOT_AVAILABLE_FOR_PARTNER, DoubleClickDistributionPlugin::DEPENDENTS_ON_PLUGIN_NAME_CUE_POINT, $partnerId);
		
		return parent::validateForInsert($propertiesToSkip);
	}
}