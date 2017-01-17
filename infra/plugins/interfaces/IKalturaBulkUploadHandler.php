<?php
/**
 * Enable the plugin to handle bulk upload additional data
 * @package infra
 * @subpackage Plugins
 */
interface IBorhanBulkUploadHandler extends IBorhanBase
{
	/**
	 * @param string $entryId the new created entry
	 * @param array $data key => value pairs
	 */
	public static function handleBulkUploadData(BaseObject $object, array $data);	
}