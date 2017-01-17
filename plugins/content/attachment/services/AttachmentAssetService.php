<?php

/**
 * Retrieve information and invoke actions on attachment Asset
 *
 * @service attachmentAsset
 * @package plugins.attachment
 * @subpackage api.services
 */
class AttachmentAssetService extends BorhanAssetService
{
	public function initService($serviceId, $serviceName, $actionName)
	{
		parent::initService($serviceId, $serviceName, $actionName);
		
		$this->applyPartnerFilterForClass('conversionProfile2');
		$this->applyPartnerFilterForClass('assetParamsOutput');
		$this->applyPartnerFilterForClass('asset');
		$this->applyPartnerFilterForClass('assetParams');
	}
	
	protected function getEnabledMediaTypes()
	{
		$liveStreamTypes = BorhanPluginManager::getExtendedTypes(entryPeer::OM_CLASS, BorhanEntryType::LIVE_STREAM);
		
		$mediaTypes = array_merge($liveStreamTypes, parent::getEnabledMediaTypes());
		$mediaTypes[] = entryType::AUTOMATIC;
		$mediaTypes = array_unique($mediaTypes);
		return $mediaTypes;
	}
	
	/* (non-PHPdoc)
	 * @see BorhanBaseService::partnerRequired()
	 */
	protected function partnerRequired($actionName)
	{
		if ($actionName === 'serve') 
			return false;

		return parent::partnerRequired($actionName);
	}

	/* (non-PHPdoc)
	 * @see BorhanBaseService::borhanNetworkAllowed()
	 */
	protected function borhanNetworkAllowed($actionName)
	{
		if(
			$actionName == 'get' ||
			$actionName == 'list' ||
			$actionName == 'getUrl'
			)
		{
			$this->partnerGroup .= ',0';
			return true;
		}
			
		return parent::borhanNetworkAllowed($actionName);
	}
	
    /**
     * Add attachment asset
     *
     * @action add
     * @param string $entryId
     * @param BorhanAttachmentAsset $attachmentAsset
     * @return BorhanAttachmentAsset
     * @throws BorhanErrors::ENTRY_ID_NOT_FOUND
	 * @throws BorhanErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws BorhanErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws BorhanErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 * @throws BorhanErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws BorhanErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 * @validateUser entry entryId edit
     */
    function addAction($entryId, BorhanAttachmentAsset $attachmentAsset)
    {
		$dbEntry = entryPeer::retrieveByPK($entryId);
		if(!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()))
			throw new BorhanAPIException(BorhanErrors::ENTRY_ID_NOT_FOUND, $entryId);

		$dbAsset = $attachmentAsset->toInsertableObject();
		$dbAsset->setEntryId($entryId);
		$dbAsset->setPartnerId($dbEntry->getPartnerId());
		$dbAsset->setStatus(AttachmentAsset::ASSET_STATUS_QUEUED);
		$dbAsset->save();

		$asset = BorhanAsset::getInstance($dbAsset);
		$asset->fromObject($dbAsset, $this->getResponseProfile());
		return $asset;
    }
    
    /**
     * Update content of attachment asset
     *
     * @action setContent
     * @param string $id
     * @param BorhanContentResource $contentResource
     * @return BorhanAttachmentAsset
     * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 * @throws BorhanErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws BorhanErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws BorhanErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 * @throws BorhanErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws BorhanErrors::RESOURCE_TYPE_NOT_SUPPORTED 
	 * @validateUser asset::entry id edit
     */
    function setContentAction($id, BorhanContentResource $contentResource)
    {
   		$dbAttachmentAsset = assetPeer::retrieveById($id);
   		if (!$dbAttachmentAsset || !($dbAttachmentAsset instanceof AttachmentAsset))
   			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbAttachmentAsset->getentry();
    	if(!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()))
    		throw new BorhanAPIException(BorhanErrors::ENTRY_ID_NOT_FOUND, $dbAttachmentAsset->getEntryId());
		
		
   		$previousStatus = $dbAttachmentAsset->getStatus();
		$contentResource->validateEntry($dbAttachmentAsset->getentry());
		$contentResource->validateAsset($dbAttachmentAsset);
		$kContentResource = $contentResource->toObject();
    	$this->attachContentResource($dbAttachmentAsset, $kContentResource);
		$contentResource->entryHandled($dbAttachmentAsset->getentry());
		kEventsManager::raiseEvent(new kObjectDataChangedEvent($dbAttachmentAsset));
		
    	$newStatuses = array(
    		AttachmentAsset::ASSET_STATUS_READY,
    		AttachmentAsset::ASSET_STATUS_VALIDATING,
    		AttachmentAsset::ASSET_STATUS_TEMP,
    	);
    	
    	if($previousStatus == AttachmentAsset::ASSET_STATUS_QUEUED && in_array($dbAttachmentAsset->getStatus(), $newStatuses))
   			kEventsManager::raiseEvent(new kObjectAddedEvent($dbAttachmentAsset));
   		
		$attachmentAsset = BorhanAsset::getInstance($dbAttachmentAsset);
		$attachmentAsset->fromObject($dbAttachmentAsset, $this->getResponseProfile());
		return $attachmentAsset;
    }
	
    /**
     * Update attachment asset
     *
     * @action update
     * @param string $id
     * @param BorhanAttachmentAsset $attachmentAsset
     * @return BorhanAttachmentAsset
     * @throws BorhanErrors::ENTRY_ID_NOT_FOUND
     * @validateUser asset::entry id edit
     */
    function updateAction($id, BorhanAttachmentAsset $attachmentAsset)
    {
		$dbAttachmentAsset = assetPeer::retrieveById($id);
		if (!$dbAttachmentAsset || !($dbAttachmentAsset instanceof AttachmentAsset))
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $id);
    	
		$dbEntry = $dbAttachmentAsset->getentry();
    	if(!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()))
    		throw new BorhanAPIException(BorhanErrors::ENTRY_ID_NOT_FOUND, $dbAttachmentAsset->getEntryId());
		
		
    	$dbAttachmentAsset = $attachmentAsset->toUpdatableObject($dbAttachmentAsset);
    	$dbAttachmentAsset->save();
		
		$attachmentAsset = BorhanAsset::getInstance($dbAttachmentAsset);
		$attachmentAsset->fromObject($dbAttachmentAsset, $this->getResponseProfile());
		return $attachmentAsset;
    }
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param string $fullPath
	 * @param bool $copyOnly
	 */
	protected function attachFile(AttachmentAsset $attachmentAsset, $fullPath, $copyOnly = false)
	{
		$ext = pathinfo($fullPath, PATHINFO_EXTENSION);
		
		$attachmentAsset->incrementVersion();
		$attachmentAsset->setFileExt($ext);
		$attachmentAsset->setSize(kFile::fileSize($fullPath));
		$attachmentAsset->save();
		
		$syncKey = $attachmentAsset->getSyncKey(AttachmentAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		
		try {
			kFileSyncUtils::moveFromFile($fullPath, $syncKey, true, $copyOnly);
		}
		catch (Exception $e) {
			
			if($attachmentAsset->getStatus() == AttachmentAsset::ASSET_STATUS_QUEUED || $attachmentAsset->getStatus() == AttachmentAsset::ASSET_STATUS_NOT_APPLICABLE)
			{
				$attachmentAsset->setDescription($e->getMessage());
				$attachmentAsset->setStatus(AttachmentAsset::ASSET_STATUS_ERROR);
				$attachmentAsset->save();
			}												
			throw $e;
		}

		$finalPath = kFileSyncUtils::getLocalFilePathForKey($syncKey);
		list($width, $height, $type, $attr) = getimagesize($finalPath);
		
		$attachmentAsset->setWidth($width);
		$attachmentAsset->setHeight($height);
		$attachmentAsset->setSize(kFile::fileSize($finalPath));
		
		$attachmentAsset->setStatus(AttachmentAsset::ASSET_STATUS_READY);
		$attachmentAsset->save();
	}
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param string $url
	 */
	protected function attachUrl(AttachmentAsset $attachmentAsset, $url)
	{
    	$fullPath = myContentStorage::getFSUploadsPath() . '/' . basename($url);
		if (KCurlWrapper::getDataFromFile($url, $fullPath))
			return $this->attachFile($attachmentAsset, $fullPath);
			
		if($attachmentAsset->getStatus() == AttachmentAsset::ASSET_STATUS_QUEUED || $attachmentAsset->getStatus() == AttachmentAsset::ASSET_STATUS_NOT_APPLICABLE)
		{
			$attachmentAsset->setDescription("Failed downloading file[$url]");
			$attachmentAsset->setStatus(AttachmentAsset::ASSET_STATUS_ERROR);
			$attachmentAsset->save();
		}
		
		throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_DOWNLOAD_FAILED, $url);
    }
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param kUrlResource $contentResource
	 */
	protected function attachUrlResource(AttachmentAsset $attachmentAsset, kUrlResource $contentResource)
	{
    	$this->attachUrl($attachmentAsset, $contentResource->getUrl());
    }
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param kLocalFileResource $contentResource
	 */
	protected function attachLocalFileResource(AttachmentAsset $attachmentAsset, kLocalFileResource $contentResource)
	{
		if($contentResource->getIsReady())
			return $this->attachFile($attachmentAsset, $contentResource->getLocalFilePath(), $contentResource->getKeepOriginalFile());
			
		$attachmentAsset->setStatus(asset::ASSET_STATUS_IMPORTING);
		$attachmentAsset->save();
		
		$contentResource->attachCreatedObject($attachmentAsset);
    }
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param FileSyncKey $srcSyncKey
	 */
	protected function attachFileSync(AttachmentAsset $attachmentAsset, FileSyncKey $srcSyncKey)
	{
		$attachmentAsset->incrementVersion();
		$attachmentAsset->save();
		
        $newSyncKey = $attachmentAsset->getSyncKey(AttachmentAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
        kFileSyncUtils::createSyncFileLinkForKey($newSyncKey, $srcSyncKey);
                
		$finalPath = kFileSyncUtils::getLocalFilePathForKey($newSyncKey);
		list($width, $height, $type, $attr) = getimagesize($finalPath);
		
		$attachmentAsset->setWidth($width);
		$attachmentAsset->setHeight($height);
		$attachmentAsset->setSize(kFile::fileSize($finalPath));
		
		$attachmentAsset->setStatus(AttachmentAsset::ASSET_STATUS_READY);
		$attachmentAsset->save();
    }
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param kFileSyncResource $contentResource
	 */
	protected function attachFileSyncResource(AttachmentAsset $attachmentAsset, kFileSyncResource $contentResource)
	{
    	$syncable = kFileSyncObjectManager::retrieveObject($contentResource->getFileSyncObjectType(), $contentResource->getObjectId());
    	$srcSyncKey = $syncable->getSyncKey($contentResource->getObjectSubType(), $contentResource->getVersion());
    	
        return $this->attachFileSync($attachmentAsset, $srcSyncKey);
    }
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param IRemoteStorageResource $contentResource
	 * @throws BorhanErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 */
	protected function attachRemoteStorageResource(AttachmentAsset $attachmentAsset, IRemoteStorageResource $contentResource)
	{
		$resources = $contentResource->getResources();
		
		$attachmentAsset->setFileExt($contentResource->getFileExt());
        $attachmentAsset->incrementVersion();
		$attachmentAsset->setStatus(AttachmentAsset::ASSET_STATUS_READY);
        $attachmentAsset->save();
        	
        $syncKey = $attachmentAsset->getSyncKey(AttachmentAsset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		foreach($resources as $currentResource)
		{
			$storageProfile = StorageProfilePeer::retrieveByPK($currentResource->getStorageProfileId());
			$fileSync = kFileSyncUtils::createReadyExternalSyncFileForKey($syncKey, $currentResource->getUrl(), $storageProfile);
		}
    }
    
    
	/**
	 * @param AttachmentAsset $attachmentAsset
	 * @param kContentResource $contentResource
	 * @throws BorhanErrors::UPLOAD_TOKEN_INVALID_STATUS_FOR_ADD_ENTRY
	 * @throws BorhanErrors::UPLOADED_FILE_NOT_FOUND_BY_TOKEN
	 * @throws BorhanErrors::RECORDED_WEBCAM_FILE_NOT_FOUND
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 * @throws BorhanErrors::STORAGE_PROFILE_ID_NOT_FOUND
	 * @throws BorhanErrors::RESOURCE_TYPE_NOT_SUPPORTED
	 */
	protected function attachContentResource(AttachmentAsset $attachmentAsset, kContentResource $contentResource)
	{
    	switch($contentResource->getType())
    	{
			case 'kUrlResource':
				return $this->attachUrlResource($attachmentAsset, $contentResource);
				
			case 'kLocalFileResource':
				return $this->attachLocalFileResource($attachmentAsset, $contentResource);
				
			case 'kFileSyncResource':
				return $this->attachFileSyncResource($attachmentAsset, $contentResource);
				
			case 'kRemoteStorageResource':
			case 'kRemoteStorageResources':
				return $this->attachRemoteStorageResource($attachmentAsset, $contentResource);
				
			default:
				$msg = "Resource of type [" . get_class($contentResource) . "] is not supported";
				BorhanLog::err($msg);
				
				if($attachmentAsset->getStatus() == AttachmentAsset::ASSET_STATUS_QUEUED || $attachmentAsset->getStatus() == AttachmentAsset::ASSET_STATUS_NOT_APPLICABLE)
				{
					$attachmentAsset->setDescription($msg);
					$attachmentAsset->setStatus(asset::ASSET_STATUS_ERROR);
					$attachmentAsset->save();
				}
				
				throw new BorhanAPIException(BorhanErrors::RESOURCE_TYPE_NOT_SUPPORTED, get_class($contentResource));
    	}
    }
	
	/**
	 * Get download URL for the asset
	 * 
	 * @action getUrl
	 * @param string $id
	 * @param int $storageId
	 * @return string
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_IS_NOT_READY
	 */
	public function getUrlAction($id, $storageId = null)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof AttachmentAsset))
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $id);

		$this->validateEntryEntitlement($assetDb->getEntryId(), $id);
		
		if ($assetDb->getStatus() != asset::ASSET_STATUS_READY)
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_IS_NOT_READY);
		
		$entryDb = $assetDb->getentry();
		if(is_null($entryDb))
			throw new BorhanAPIException(BorhanErrors::ENTRY_ID_NOT_FOUND, $assetDb->getEntryId());
		
		if($storageId)
			return $assetDb->getExternalUrl($storageId);
			
		return $assetDb->getDownloadUrl(true);
	}
	
	/**
	 * Get remote storage existing paths for the asset
	 * 
	 * @action getRemotePaths
	 * @param string $id
	 * @return BorhanRemotePathListResponse
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_IS_NOT_READY
	 */
	public function getRemotePathsAction($id)
	{
		$assetDb = assetPeer::retrieveById($id);
		if (!$assetDb || !($assetDb instanceof AttachmentAsset))
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $id);

		if ($assetDb->getStatus() != asset::ASSET_STATUS_READY)
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_IS_NOT_READY);

		$c = new Criteria();
		$c->add(FileSyncPeer::OBJECT_TYPE, FileSyncObjectType::ASSET);
		$c->add(FileSyncPeer::OBJECT_SUB_TYPE, asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
		$c->add(FileSyncPeer::OBJECT_ID, $id);
		$c->add(FileSyncPeer::VERSION, $assetDb->getVersion());
		$c->add(FileSyncPeer::PARTNER_ID, $assetDb->getPartnerId());
		$c->add(FileSyncPeer::STATUS, FileSync::FILE_SYNC_STATUS_READY);
		$c->add(FileSyncPeer::FILE_TYPE, FileSync::FILE_SYNC_FILE_TYPE_URL);
		$fileSyncs = FileSyncPeer::doSelect($c);
			
		$listResponse = new BorhanRemotePathListResponse();
		$listResponse->objects = BorhanRemotePathArray::fromDbArray($fileSyncs, $this->getResponseProfile());
		$listResponse->totalCount = count($listResponse->objects);
		return $listResponse;
	}
	
	/**
	 * Serves attachment by its id
	 *  
	 * @action serve
	 * @param string $attachmentAssetId
	 * @return file
	 *  
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 */
	public function serveAction($attachmentAssetId)
	{
		$attachmentAsset = null;
		if (!kCurrentContext::$ks)
		{	
			$attachmentAsset = kCurrentContext::initPartnerByAssetId($attachmentAssetId);
			
			if (!$attachmentAsset || $attachmentAsset->getStatus() == asset::ASSET_STATUS_DELETED)
				throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $attachmentAssetId);
				
			// enforce entitlement
			$this->setPartnerFilters(kCurrentContext::getCurrentPartnerId());
			kEntitlementUtils::initEntitlementEnforcement();
		}
		else 
		{	
			$attachmentAsset = assetPeer::retrieveById($attachmentAssetId);
		}
		
		if (!$attachmentAsset || !($attachmentAsset instanceof AttachmentAsset))
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $attachmentAssetId);
		
		$entry = entryPeer::retrieveByPK($attachmentAsset->getEntryId());
		if(!$entry)
		{
			//we will throw attachment asset not found, as the user is not entitled, and should not know that the entry exists.
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $attachmentAssetId);
		}
		
		$securyEntryHelper = new KSecureEntryHelper($entry, kCurrentContext::$ks, null, ContextType::DOWNLOAD);
		$securyEntryHelper->validateForDownload();
		
		$ext = $attachmentAsset->getFileExt();
		if(is_null($ext))
			$ext = 'txt';
			
		$fileName = $attachmentAsset->getFilename();
		if (!$fileName)	
			$fileName = $attachmentAsset->getEntryId()."_" . $attachmentAsset->getId() . ".$ext";
		
		header("Content-Disposition: attachment; filename=\"$fileName\"");
		return $this->serveAsset($attachmentAsset, $fileName);
	}

	/**
	 * @action get
	 * @param string $attachmentAssetId
	 * @return BorhanAttachmentAsset
	 * 
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 */
	public function getAction($attachmentAssetId)
	{
		$attachmentAssetsDb = assetPeer::retrieveById($attachmentAssetId);
		if (!$attachmentAssetsDb || !($attachmentAssetsDb instanceof AttachmentAsset))
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $attachmentAssetId);
		
		$attachmentAsset = BorhanAsset::getInstance($attachmentAssetsDb);
		$attachmentAsset->fromObject($attachmentAssetsDb, $this->getResponseProfile());
		return $attachmentAsset;
	}
	
	/**
	 * List attachment Assets by filter and pager
	 * 
	 * @action list
	 * @param BorhanAssetFilter $filter
	 * @param BorhanFilterPager $pager
	 * @return BorhanAttachmentAssetListResponse
	 */
	function listAction(BorhanAssetFilter $filter = null, BorhanFilterPager $pager = null)
	{
		if(!$filter)
		{
			$filter = new BorhanAttachmentAssetFilter();
		}
		elseif(! $filter instanceof BorhanAttachmentAssetFilter)
		{
			$filter = $filter->cast('BorhanAttachmentAssetFilter');
		}
			
		if(!$pager)
		{
			$pager = new BorhanFilterPager();
		}

		$types = BorhanPluginManager::getExtendedTypes(assetPeer::OM_CLASS, AttachmentPlugin::getAssetTypeCoreValue(AttachmentAssetType::ATTACHMENT));
		return $filter->getTypeListResponse($pager, $this->getResponseProfile(), $types);
	}
	
	/**
	 * @action delete
	 * @param string $attachmentAssetId
	 * 
	 * @throws BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND
	 * @validateUser asset::entry attachmentAssetId edit
	 */
	public function deleteAction($attachmentAssetId)
	{
		$attachmentAssetDb = assetPeer::retrieveById($attachmentAssetId);
		if (!$attachmentAssetDb || !($attachmentAssetDb instanceof AttachmentAsset))
			throw new BorhanAPIException(BorhanAttachmentErrors::ATTACHMENT_ASSET_ID_NOT_FOUND, $attachmentAssetId);
	
		$dbEntry = $attachmentAssetDb->getentry();
    	if(!$dbEntry || !in_array($dbEntry->getType(), $this->getEnabledMediaTypes()))
    		throw new BorhanAPIException(BorhanErrors::ENTRY_ID_NOT_FOUND, $attachmentAssetDb->getEntryId());
		
		
		$attachmentAssetDb->setStatus(AttachmentAsset::ASSET_STATUS_DELETED);
		$attachmentAssetDb->setDeletedAt(time());
		$attachmentAssetDb->save();
	}
}
