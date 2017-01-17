<?php
/**
 * @package plugins.voicebase
 * @subpackage api.objects
 */
class BorhanVoicebaseJobProviderData extends BorhanIntegrationJobProviderData
{
	/**
	 * Entry ID
	 * @var string
	 */
	public $entryId;
	
	/**
	 * Flavor ID
	 * @var string
	 */
	public $flavorAssetId;
	
	/**
	 * input Transcript-asset ID
	 * @var string
	 */
	public $transcriptId;
	
	/**
	 * Caption formats
	 * @var string
	 */
	public $captionAssetFormats;
	
	/**
	 * Api key for service provider
	 * @var string
	 * @readonly
	 */
	public $apiKey;
	
	/**
	 * Api key for service provider
	 * @var string
	 * @readonly
	 */
	public $apiPassword;
	
	/**
	 * Transcript content language
	 * @var BorhanLanguage
	 */
	public $spokenLanguage;
	
	/**
	 * Transcript Content location
	 * @var string
	 * @readonly
	 */
	public $fileLocation;
	
	/**
	 * should replace remote media content
	 * @var bool
	 */
	public $replaceMediaContent;
	
	private static $map_between_objects = array
	(
		"entryId",
		"flavorAssetId",
		"transcriptId" => "inputTranscriptId",
		"captionAssetFormats",
		"apiKey",
		"apiPassword",
		"spokenLanguage",
		"fileLocation",
		"replaceMediaContent",
	);
	
	/* (non-PHPdoc)
	 * @see BorhanObject::getMapBetweenObjects()
	 */
	public function getMapBetweenObjects ( )
	{
		return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
	}
	
	public function validateForUsage($sourceObject, $propertiesToSkip = array())
	{
		$entryId = $this->entryId;
		$entry = entryPeer::retrieveByPK($entryId);
		if(!$entry || $entry->getType() != entryType::MEDIA_CLIP || !in_array($entry->getMediaType(), array(entry::ENTRY_MEDIA_TYPE_VIDEO,entry::ENTRY_MEDIA_TYPE_AUDIO)))
			throw new BorhanAPIException(BorhanErrors::INVALID_ENTRY_ID, $entryId);
		
		$flavorAssetId = $this->flavorAssetId;
		if($flavorAssetId)
		{
			$flavorAsset = assetPeer::retrieveById($flavorAssetId);
			if(!$flavorAsset || $flavorAsset->getEntryId() != $entryId)
				throw new BorhanAPIException(BorhanErrors::FLAVOR_ASSET_ID_NOT_FOUND, $flavorAssetId);
		}
	
		$transcriptId = $this->transcriptId;
		if($transcriptId)
		{
			$transcript = assetPeer::retrieveById($transcriptId);
			if (!$transcript || $transcript->getEntryId() != $entryId || $transcript->getType() != TranscriptPlugin::getAssetTypeCoreValue(TranscriptAssetType::TRANSCRIPT))
				throw new BorhanAPIException(BorhanErrors::INVALID_OBJECT_ID, $transcriptId);
		}
	
		$voicebaseParamsMap = kConf::get('voicebase','integration');	
		$supportedLanguages = $voicebaseParamsMap['languages'];
		if($this->spokenLanguage)
		{
			if (!isset($supportedLanguages[$this->spokenLanguage]))
				throw new BorhanAPIException(BorhanVoicebaseErrors::LANGUAGE_NOT_SUPPORTED, $this->spokenLanguage);
		}
		else
			$this->spokenLanguage = $voicebaseParamsMap['default_language'];
	
		return parent::validateForUsage($sourceObject, $propertiesToSkip = array());
	}
	
	public function toObject($object_to_fill = null, $props_to_skip = array())
	{
		$object = parent::toObject($object_to_fill, $props_to_skip);
	
		$entryId = $object->getEntryId();
		$entry = entryPeer::retrieveByPK($entryId);
		$partnerId = $entry->getPartnerId();
		$transcriptId = $object->getInputTranscriptId();
	
		$voicebaseOptionsObj = VoicebasePlugin::getPartnerVoicebaseOptions($partnerId);
		$object->setApiKey($voicebaseOptionsObj->apiKey);
		$object->setApiPassword($voicebaseOptionsObj->apiPassword);
		
		if(!$object->getFlavorAssetId())
		{
			$sourceAsset = assetPeer::retrieveOriginalReadyByEntryId($entryId);
			if(!$sourceAsset)
				throw new BorhanAPIException(BorhanVoicebaseErrors::NO_FLAVOR_ASSET_FOUND, $entryId);
			$object->setFlavorAssetId($sourceAsset->getId());
		}

		$voicebaseParamsMap = kConf::get('voicebase','integration');

		if(!$object->getSpokenLanguage())
		{
			$object->setSpokenLanguage($voicebaseParamsMap['default_language']);
		}

		$formatsString = $object->getCaptionAssetFormats();
		if($formatsString)
		{
			$formatsArray = explode(',', $formatsString);
			$excludedFormats = $voicebaseParamsMap['exclude_formats'];
			$sanitizedFormatsArray = array();
			foreach($formatsArray as $format)
			{
				$format = preg_replace("/[^A-Z_]/", "", $format);
				if(!constant("BorhanCaptionType::" . $format) || in_array($format, $excludedFormats))
					throw new BorhanAPIException(BorhanVoicebaseErrors::INVALID_TYPES,$formatsString);
				$sanitizedFormatsArray[] = $format;
			}
			$sanitizedFormats = implode(",", $sanitizedFormatsArray);
			$object->setCaptionAssetFormats($sanitizedFormats);
		}
		else
		{
			$defaultFormats = implode(",", $voicebaseParamsMap['default_formats']);
			$object->setCaptionAssetFormats($defaultFormats);
		}
	
		if($transcriptId)
		{
			$transcript = assetPeer::retrieveById($transcriptId);
			$key = $transcript->getSyncKey(asset::FILE_SYNC_ASSET_SUB_TYPE_ASSET);
			$fileSync = FileSyncPeer::retrieveByFileSyncKey($key, true);
			$object->setFileLocation($fileSync->getFullPath());
		}
		
		return $object;
	}
}
