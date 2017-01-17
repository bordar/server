<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanStorageProfile extends BorhanObject implements IFilterable
{
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $id;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $createdAt;
	
	/**
	 * @var time
	 * @readonly
	 * @filter gte,lte,order
	 */
	public $updatedAt;
	
	/**
	 * @var int
	 * @readonly
	 * @filter eq,in
	 */
	public $partnerId;
	
	/**
	 * @var string
	 */
	public $name;
	
	/**
	 * @var string
	 * @filter eq,in
	 */
	public $systemName;
	
	/**
	 * @var string
	 */
	public $desciption;
	
	/**
	 * @var BorhanStorageProfileStatus
	 * @filter eq,in
	 */
	public $status;
	
	/**
	 * @var BorhanStorageProfileProtocol
	 * @filter eq,in
	 */
	public $protocol;
	
	/**
	 * @var string
	 */
	public $storageUrl;
	
	/**
	 * @var string
	 */
	public $storageBaseDir;
	
	/**
	 * @var string
	 */
	public $storageUsername;
	
	/**
	 * @var string
	 */
	public $storagePassword;
	
	/**
	 * @var bool
	 */
	public $storageFtpPassiveMode;
	
	/**
	 * @var int
	 */
	public $minFileSize;
	
	/**
	 * @var int
	 */
	public $maxFileSize;
	
	/**
	 * @var string
	 */
	public $flavorParamsIds;
	
	/**
	 * @var int
	 */
	public $maxConcurrentConnections;
	
	/**
	 * @var string
	 */
	public $pathManagerClass;
	
	/**
	 * @var BorhanKeyValueArray
	 */
	public $pathManagerParams;
	
	/**
	 * No need to create enum for temp field
	 * 
	 * @var int
	 */
	public $trigger;
	
	/**
	 * Delivery Priority
	 * 
	 * @var int
	 */
	public $deliveryPriority;
	
	/**
	 * 
	 * @var BorhanStorageProfileDeliveryStatus
	 */
	public $deliveryStatus;
	
	/**
	 * 
	 * @var BorhanStorageProfileReadyBehavior
	 */
	public $readyBehavior;
	
	/**
	 * Flag sugnifying that the storage exported content should be deleted when soure entry is deleted
	 * @var int
	 */
	public $allowAutoDelete;
	
	/**
	 * Indicates to the local file transfer manager to create a link to the file instead of copying it
	 * @var bool
	 */
	public $createFileLink;
	
	/**
	 * Holds storage profile export rules
	 * 
	 * @var BorhanRuleArray
	 */
	public $rules;
	
	/**
	 * Delivery profile ids
	 * @var BorhanKeyValueArray
	 */
	public $deliveryProfileIds;
	
	/**
	 * @var string
	 */
	public $privateKey;
	
    /**
	 * @var string
	 */
	public $publicKey;
	
	/**
	 * @var string
	 */
	public $passPhrase;

	/**
	 * @var bool
	 */
	public $shouldExportThumbs;

	private static $map_between_objects = array
	(
		"id",
		"createdAt",
		"updatedAt",
		"partnerId",
		"name",
		"systemName",
		"desciption",
		"status",
		"protocol",
		"storageUrl",
		"storageBaseDir",
		"storageUsername",
		"storagePassword",
		"storageFtpPassiveMode",
		"minFileSize",
		"maxFileSize",
		"flavorParamsIds",
		"maxConcurrentConnections",
		"pathManagerClass",
		"trigger",
		"deliveryPriority",
		"deliveryStatus",
		"readyBehavior",
		"allowAutoDelete",
		"createFileLink",
		"rules",
		"pathManagerParams",	
		"deliveryProfileIds",
		"privateKey",
		"publicKey",
		"passPhrase",
		"shouldExportThumbs",
	);
	
	/* (non-PHPdoc)
	 * @see BorhanObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}	
	
	/* (non-PHPdoc)
	 * @see BorhanObject::toInsertableObject()
	 */
	public function toInsertableObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(is_null($object_to_fill))
			$object_to_fill = new StorageProfile();
			
		return parent::toInsertableObject($object_to_fill, $props_to_skip);
	}

	/* (non-PHPdoc)
	 * @see BorhanObject::validateForUpdate()
	 */
	public function validateForUpdate($sourceObject, $propertiesToSkip = array())
	{
		$this->validatePropertyMinLength("name", 1, true);
	
		if($this->systemName)
		{
			$c = BorhanCriteria::create(StorageProfilePeer::OM_CLASS);
			$c->add(StorageProfilePeer::ID, $sourceObject->getId(), Criteria::NOT_EQUAL);
			$c->add(StorageProfilePeer::SYSTEM_NAME, $this->systemName);
			if(StorageProfilePeer::doCount($c))
				throw new BorhanAPIException(BorhanErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
		}
		
		return parent::validateForUpdate($sourceObject, $propertiesToSkip);
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::validateForInsert()
	 */
	public function validateForInsert($propertiesToSkip = array())
	{
		$this->validatePropertyMinLength("name", 1);
		
		if($this->systemName)
		{
			$c = BorhanCriteria::create(StorageProfilePeer::OM_CLASS);
			$c->add(StorageProfilePeer::SYSTEM_NAME, $this->systemName);
			if(StorageProfilePeer::doCount($c))
				throw new BorhanAPIException(BorhanErrors::SYSTEM_NAME_ALREADY_EXISTS, $this->systemName);
		}
		
		return parent::validateForInsert($propertiesToSkip);
	}
	
	protected function insertObject(&$res, $key, $value) {
		if(strpos($key, ".") === FALSE) {
			$res[$key] = intval($value);
			return;
		}
	
		list($key, $newKey) = explode(".", $key, 2);
		if(!array_key_exists($key, $res))
			$res[$key] = array();
		$this->insertObject($res[$key], $newKey, $value);
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::toObject()
	 */
	public function toObject ( $object_to_fill = null , $props_to_skip = array() )
	{
		if(is_null($object_to_fill))
			$object_to_fill = new StorageProfile();
		
		
		$object_to_fill =  parent::toObject($object_to_fill, $props_to_skip);
		
		// path manager params
		$dbPathManagerParams = $object_to_fill->getPathManagerParams();
		if (!is_null($this->pathManagerParams) && count($this->pathManagerParams) > 0)
		{
    		foreach ($this->pathManagerParams as $param)
    		{
    		    $dbPathManagerParams[$param->key] = $param->value;
    		}
		}
		$object_to_fill->setPathManagerParams($dbPathManagerParams);
		
		// Delivery Profile Ids
		$deliveryProfileIds = $this->deliveryProfileIds;
		
		$deliveryProfiles = array();
		if($deliveryProfileIds)
			foreach($deliveryProfileIds->toArray() as $keyValue) 
				$this->insertObject($deliveryProfiles, $keyValue->key, $keyValue->value);
			
		$object_to_fill->setDeliveryProfileIds($deliveryProfiles);
		
		return $object_to_fill;
	}
	
	/* (non-PHPdoc)
	 * @see BorhanObject::fromObject()
	 */
	public function doFromObject($source_object, BorhanDetachedResponseProfile $responseProfile = null)
	{
	    parent::doFromObject($source_object, $responseProfile);
	    
		if($this->shouldGet('pathManagerParams', $responseProfile))
			$this->pathManagerParams = BorhanKeyValueArray::fromKeyValueArray($source_object->getPathManagerParams());
		if($this->shouldGet('deliveryProfileIds', $responseProfile))
			$this->deliveryProfileIds = BorhanKeyValueArray::fromKeyValueArray($source_object->getDeliveryProfileIds());
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getExtraFilters()
	 */
	public function getExtraFilters()
	{
		return array();
	}
	
	/* (non-PHPdoc)
	 * @see IFilterable::getFilterDocs()
	 */
	public function getFilterDocs()
	{
		return array();
	}
    
    /**
     * Function returns BorhanStorageProfile sub-type according to protocol
     * @var string $protocol
     * 
     * @return BorhanStorageProfile
     */
    public static function getInstanceByType ($protocol)
    {
        $obj = null;
        switch ($protocol) {
            case StorageProfileProtocol::FTP:
            case StorageProfileProtocol::SFTP:
            case StorageProfileProtocol::SCP:
            case StorageProfileProtocol::BORHAN_DC:
            case StorageProfileProtocol::LOCAL:
                $obj = new BorhanStorageProfile();                
                break;
            case StorageProfileProtocol::S3:
                $obj = new BorhanAmazonS3StorageProfile();
                break;
            default:
                $obj = BorhanPluginManager::loadObject('BorhanStorageProfile', $protocol);
                break;
        }
        
        if (!$obj)
            $obj = new BorhanStorageProfile();
        
        return $obj;
    }
}