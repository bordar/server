<?php
/**
 * @package plugins.drm
 * @subpackage Admin
 */
class DrmProfileDeleteAction extends BorhanApplicationPlugin
{
	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	public function getRequiredPermissions()
	{
		return array(Borhan_Client_Enum_PermissionName::SYSTEM_ADMIN_DRM_PROFILE_MODIFY);
	}
	
	public function doAction(Zend_Controller_Action $action)
	{
		$action->getHelper('layout')->disableLayout();
		$drmProfileId = $this->_getParam('drmProfileId');
		
		$client = Infra_ClientHelper::getClient();
		$drmPluginClient= Borhan_Client_Drm_Plugin::get($client);
		
		try
		{
			$updatedDrmProfile = $drmPluginClient->drmProfile->delete($drmProfileId);
			echo $action->getHelper('json')->sendJson('ok', false);
		}
		catch(Exception $e)
		{
			BorhanLog::err($e->getMessage() . "\n" . $e->getTraceAsString());
			echo $action->getHelper('json')->sendJson($e->getMessage(), false);
		}
	}
}

