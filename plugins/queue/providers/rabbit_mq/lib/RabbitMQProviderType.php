<?php
/**
 * @package plugins.rabbitMQ
 * @subpackage lib.enum
 */
class RabbitMQProviderType implements IBorhanPluginEnum, QueueProviderType
{
	const RABBITMQ = 'RabbitMQ';
	
	public static function getAdditionalValues()
	{
		return array(
			'RABBITMQ' => self::RABBITMQ,
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
