<?php
/**
 * @package plugins.youTubeDistribution
 * @subpackage api.objects
 */
class BorhanYouTubeDistributionJobProviderData extends BorhanConfigurableDistributionJobProviderData
{
	/**
	 * @var string
	 */
	public $videoAssetFilePath;
	
	/**
	 * @var string
	 */
	public $thumbAssetFilePath;
	
	/**
	 * @var string
	 */
	public $captionAssetIds;
	
	/**
	 * @var string
	 */
	public $sftpDirectory;
	
	/**
	 * @var string
	 */
	public $sftpMetadataFilename;
	
	/**
	 * @var string
	 */
	public $currentPlaylists;

	/**
	 * @var string
	 */
	public $newPlaylists;

	/**
	 * @var string
	 */
	public $submitXml;

	/**
	 * @var string
	 */
	public $updateXml;

	/**
	 * @var string
	 */
	public $deleteXml;

	/**
	 * @var string
	 */
	public $googleClientId;

	/**
	 * @var string
	 */
	public $googleClientSecret;

	/**
	 * @var string
	 */
	public $googleTokenData;

	public function __construct(BorhanDistributionJobData $distributionJobData = null)
	{
	    parent::__construct($distributionJobData);
	    
		if(!$distributionJobData)
			return;
			
		if(!($distributionJobData->distributionProfile instanceof BorhanYouTubeDistributionProfile))
			return;
		
		$flavorAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->flavorAssetIds));
		if(count($flavorAssets)) // if we have specific flavor assets for this distribution, grab the first one
			$flavorAsset = reset($flavorAssets);
		else // take the source asset
			$flavorAsset = assetPeer::retrieveOriginalReadyByEntryId($distributionJobData->entryDistribution->entryId);
		
		if($flavorAsset) 
		{
			$syncKey = $flavorAsset->getSyncKey(flavorAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			if(kFileSyncUtils::fileSync_exists($syncKey))
			    $this->videoAssetFilePath = kFileSyncUtils::getLocalFilePathForKey($syncKey, false);
		}
		
		$thumbAssets = assetPeer::retrieveByIds(explode(',', $distributionJobData->entryDistribution->thumbAssetIds));
		if(count($thumbAssets))
		{
			$syncKey = reset($thumbAssets)->getSyncKey(thumbAsset::FILE_SYNC_FLAVOR_ASSET_SUB_TYPE_ASSET);
			if(kFileSyncUtils::fileSync_exists($syncKey))
			    $this->thumbAssetFilePath = kFileSyncUtils::getLocalFilePathForKey($syncKey, false);
		}
		
		//Add caption Asset id's
		$this->captionAssetIds = $distributionJobData->entryDistribution->assetIds;
		
		$entryDistributionDb = EntryDistributionPeer::retrieveByPK($distributionJobData->entryDistributionId);
		if ($entryDistributionDb)
			$this->currentPlaylists = $entryDistributionDb->getFromCustomData('currentPlaylists');
		else
			BorhanLog::err('Entry distribution ['.$distributionJobData->entryDistributionId.'] not found');  

		if ($distributionJobData->distributionProfile->feedSpecVersion != YouTubeDistributionFeedSpecVersion::VERSION_2)
			return;
			
		if (is_null($this->fieldValues))
			return;
			//23.5.13 this return is a hack because of bad inheritance of kYouTubeDistributionJobProviderData causing some YouTube distribution 
			//batch jobs to not have fieldValues. it can be removed at some point. 
			
		$videoFilePath = $this->videoAssetFilePath;
		$thumbnailFilePath = $this->thumbAssetFilePath;
		$captionAssetIds = $this->captionAssetIds;

		$feed = null;
		$fieldValues = unserialize($this->fieldValues);
		if ($distributionJobData instanceof BorhanDistributionSubmitJobData)
		{
			$feed = YouTubeDistributionRightsFeedHelper::initializeDefaultSubmitFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $captionAssetIds);
			$this->submitXml = $feed->getXml();
		}
		elseif ($distributionJobData instanceof BorhanDistributionUpdateJobData)
		{
			$remoteIdHandler = YouTubeDistributionRemoteIdHandler::initialize($distributionJobData->remoteId);
			$feed = YouTubeDistributionRightsFeedHelper::initializeDefaultUpdateFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $remoteIdHandler);
			$this->updateXml = $feed->getXml();
		}
		elseif ($distributionJobData instanceof BorhanDistributionDeleteJobData)
		{
			$remoteIdHandler = YouTubeDistributionRemoteIdHandler::initialize($distributionJobData->remoteId);
			$feed = YouTubeDistributionRightsFeedHelper::initializeDefaultDeleteFeed($distributionJobData->distributionProfile, $fieldValues, $videoFilePath, $thumbnailFilePath, $remoteIdHandler);
			$this->deleteXml = $feed->getXml();
		}

		$this->newPlaylists = isset($fieldValues[BorhanYouTubeDistributionField::PLAYLISTS]) ? $fieldValues[BorhanYouTubeDistributionField::PLAYLISTS] : null;
		if ($feed)
		{
			$this->sftpDirectory = $feed->getDirectoryName();
			$this->sftpMetadataFilename = $feed->getMetadataTempFileName();
		}

		$distributionProfileId = $distributionJobData->distributionProfile->id;
		$this->loadGoogleConfig($distributionProfileId);
	}
		
	private static $map_between_objects = array
	(
		"videoAssetFilePath",
		"thumbAssetFilePath",
		"captionAssetIds",
		"sftpDirectory",
		"sftpMetadataFilename",
		"currentPlaylists",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	/**
	 * @param int $distributionProfileId
	 */
	protected function loadGoogleConfig($distributionProfileId)
	{
		$appConfigId = 'youtubepartner'; // config section for configuration/google_auth.ini
		$authConfig = kConf::get($appConfigId, 'google_auth', null);

		$this->googleClientId = isset($authConfig['clientId']) ? $authConfig['clientId'] : null;
		$this->googleClientSecret = isset($authConfig['clientSecret']) ? $authConfig['clientSecret'] : null;
	
		$distributionProfile = DistributionProfilePeer::retrieveByPK($distributionProfileId);
		/* @var $distributionProfile YoutubeApiDistributionProfile */

		$tokenData = $distributionProfile->getGoogleOAuth2Data();
		if ($tokenData)
		{
			$this->googleTokenData = json_encode($tokenData);
		}
	}
}
