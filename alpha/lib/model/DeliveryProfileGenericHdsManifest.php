<?php

class DeliveryProfileGenericHdsManifest extends DeliveryProfileGenericHds {

	/**
	 * @return array $manifestInfo
	 */
	public function buildServeFlavors()
	{
		if ($this->params->getManifestFileSync())
		{
			/** @var FileSync $manifestFileSync */
			$manifestFileSync = $this->params->getManifestFileSync();
			$this->initDeliveryDynamicAttributes($this->params->getManifestFileSync());
			if ($manifestFileSync->getFileType() == FileSync::FILE_SYNC_FILE_TYPE_FILE)
			{
				// return borhan urls with serveSmil / serveManifest
				$partnerPath = myPartnerUtils::getUrlForPartner($manifestFileSync->getPartnerId(), $manifestFileSync->getPartnerId() * 100);
				$manifestObjectId = $manifestFileSync->getObjectId() . '_' . $manifestFileSync->getObjectSubType() . '_' . $manifestFileSync->getVersion();
				$extension = pathinfo($manifestFileSync->getFilePath(), PATHINFO_EXTENSION);
				$url = $partnerPath . '/serveManifest/objectId/' . $manifestObjectId . '.' . $extension;
				$url = kDeliveryUtils::formatGenericUrl($url, $this->getPattern(), $this->params);
			}
			else
			{
				$url = $this->getFileSyncUrl($manifestFileSync, false);
			}
			$manifestInfo = $this->getFlavorAssetInfo($url);
			
			return array($manifestInfo);
		} else {
			BorhanLog::log("No manifest file was found");
			return null;
		}
	}
	
}