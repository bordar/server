<?php
/**
 * Handles ad cue point ingestion from XML bulk upload
 * @package plugins.adCuePoint
 * @subpackage batch
 */
class AdCuePointBulkUploadXmlHandler extends CuePointBulkUploadXmlHandler
{
	/**
	 * @var AdCuePointBulkUploadXmlHandler
	 */
	protected static $instance;
	
	/**
	 * @return AdCuePointBulkUploadXmlHandler
	 */
	public static function get()
	{
		if(!self::$instance)
			self::$instance = new AdCuePointBulkUploadXmlHandler();
			
		return self::$instance;
	}
	
	/* (non-PHPdoc)
	 * @see AdCuePointBulkUploadXmlHandler::getNewInstance()
	 */
	protected function getNewInstance()
	{
		return new BorhanAdCuePoint();
	}
	
	/* (non-PHPdoc)
	 * @see AdCuePointBulkUploadXmlHandler::parseCuePoint()
	 */
	protected function parseCuePoint(SimpleXMLElement $scene)
	{
		if($scene->getName() != 'scene-ad-cue-point')
			return null;
			
		$cuePoint = parent::parseCuePoint($scene);
		if(!($cuePoint instanceof BorhanAdCuePoint))
			return null;
		
		if(isset($scene->sceneEndTime))
			$cuePoint->endTime = kXml::timeToInteger($scene->sceneEndTime);
		if(isset($scene->sceneTitle))
			$cuePoint->title = "$scene->sceneTitle";
		if(isset($scene->sourceUrl))
			$cuePoint->sourceUrl = "$scene->sourceUrl";

		$cuePoint->adType = "$scene->adType";
		$cuePoint->protocolType = "$scene->protocolType";
			
		return $cuePoint;
	}
	
	/**
	 * Removes all non updatble fields from the cuepoint
	 * @param BorhanCuePoint $entry
	 */
	protected function removeNonUpdatbleFields(BorhanCuePoint $cuePoint)
	{
		$retCuePoint = clone $cuePoint;
		$retCuePoint->protocolType = null;
		return $retCuePoint;
	}
}
