<?php
/**
 * @package plugins.eventNotification
 * @subpackage model.enum
 */ 
class EventNotificationBatchType implements IBorhanPluginEnum, BatchJobType
{
	const EVENT_NOTIFICATION_HANDLER = 'EventNotificationHandler';
	
	/* (non-PHPdoc)
	 * @see IBorhanPluginEnum::getAdditionalValues()
	 */
	public static function getAdditionalValues()
	{
		return array(
			'EVENT_NOTIFICATION_HANDLER' => self::EVENT_NOTIFICATION_HANDLER,
		);
	}

	/* (non-PHPdoc)
	 * @see IBorhanPluginEnum::getAdditionalDescriptions()
	 */
	public static function getAdditionalDescriptions() 
	{
		return array();
	}

}
