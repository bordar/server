<?php
/**
 * @package plugins.huluDistribution
 * @subpackage api.objects
 */
class BorhanHuluDistributionProfile extends BorhanConfigurableDistributionProfile
{
	/**
	 * @var string
	 */
	public $sftpHost;
	
	/**
	 * 
	 * @var string
	 */
	public $sftpLogin;
	
	/**
	 * @var string
	 */
	public $sftpPass;
	
	/**
	 * @var string
	 */
	public $seriesChannel;
	
	/**
	 * @var string
	 */
	public $seriesPrimaryCategory;
	
	/**
	 * @var BorhanStringArray
	 */
	public $seriesAdditionalCategories;
	
	/**
	 * @var string
	 */
	public $seasonNumber;
	
	/**
	 * @var string
	 */
	public $seasonSynopsis;
	
	/**
	 * @var string
	 */
	public $seasonTuneInInformation;
	
	/**
	 * @var string
	 */
	public $videoMediaType;
	
	/**
	 * @var bool
	 */
	public $disableEpisodeNumberCustomValidation;
	
	/**
	 * @var BorhanDistributionProtocol
	 */
	 public $protocol;
	 
	 /**
	 * @var string
	 */
	public $asperaHost;
	
	/**
	 * @var string
	 */
	public $asperaLogin;
	
	/**
	 * @var string
	 */
	public $asperaPass;
	 
	 /**
	 * @var int
	 */
	 public $port;
	 
	 /**
     * @var string
     */
    public $passphrase;
    
    /**
	 * @var string
	 */
	 public $asperaPublicKey;

	/**
	 * @var string
	 */
	 public $asperaPrivateKey;
	
	/*
	 * mapping between the field on this object (on the left) and the setter/getter on the object (on the right)  
	 */
	private static $map_between_objects = array 
	(
		'sftpHost',
		'sftpLogin',
		'sftpPass',
		'seriesChannel',
		'seriesPrimaryCategory',
		'seasonNumber',
		'seasonSynopsis',
		'seasonTuneInInformation',
		'videoMediaType',
		'disableEpisodeNumberCustomValidation',
		'asperaHost',
		'asperaLogin',
		'asperaPass',
		'protocol',
		'port',
		'passphrase',
		'asperaPublicKey',
		'asperaPrivateKey',
	);
		 
	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}
	
	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			return null;
			
		parent::toObject($dbObject, $skip);
		
		if (!is_null($this->seriesAdditionalCategories))
		{
			$seriesAdditionalCategoriesArray = array();
			foreach($this->seriesAdditionalCategories as $stringObj)
				$seriesAdditionalCategoriesArray[] = $stringObj->value;
				
			$dbObject->setSeriesAdditionalCategories($seriesAdditionalCategoriesArray);
		}
					
		return $dbObject;
	}
	
	public function doFromObject($source_object, BorhanDetachedResponseProfile $responseProfile = null)
	{
		parent::doFromObject($source_object, $responseProfile);
		
		$this->seriesAdditionalCategories = BorhanStringArray::fromStringArray($source_object->getSeriesAdditionalCategories());
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
			
		if(!$partner->getPluginEnabled(HuluDistributionPlugin::DEPENDENTS_ON_PLUGIN_NAME_CUE_POINT))
			throw new BorhanAPIException(BorhanErrors::PLUGIN_NOT_AVAILABLE_FOR_PARTNER, HuluDistributionPlugin::DEPENDENTS_ON_PLUGIN_NAME_CUE_POINT, $partnerId);
		
		return parent::validateForInsert($propertiesToSkip);
	}
}