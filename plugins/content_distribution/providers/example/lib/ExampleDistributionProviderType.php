<?php
/**
 * @package plugins.exampleDistribution
 * @subpackage lib
 */
class ExampleDistributionProviderType implements IBorhanPluginEnum, DistributionProviderType
{
	const EXAMPLE = 'EXAMPLE';
	
	public static function getAdditionalValues()
	{
		return array(
			'EXAMPLE' => self::EXAMPLE,
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
