<?php
/**
 * @package plugins.adCuePoint
 * @subpackage lib.enum
 */
class AdCuePointMetadataObjectType implements IBorhanPluginEnum, MetadataObjectType
{
	const AD_CUE_POINT = 'AdCuePoint';
	
	public static function getAdditionalValues()
	{
		return array(
			'AD_CUE_POINT' => self::AD_CUE_POINT,
		);
	}
	
	/**
	 * @return array
	 */
	public static function getAdditionalDescriptions()
	{
		return array();
	}
}
