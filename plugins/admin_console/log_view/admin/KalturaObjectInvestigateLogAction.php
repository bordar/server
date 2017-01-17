<?php
/**
 * @package plugins.logView
 * @subpackage admin
 */
class BorhanObjectInvestigateLogAction extends BorhanApplicationPlugin
{
	
	public function __construct()
	{
		$this->action = 'BorhanObjectInvestigateLogAction';
		$this->label = 'Logs Search';
		$this->rootLabel = 'Developer';
	}
	
	/**
	 * @return string - absolute file path of the phtml template
	 */
	public function getTemplatePath()
	{
		return realpath(dirname(__FILE__));
	}
	
	public function getRequiredPermissions()
	{
		return array();
	}
	
	public function doAction(Zend_Controller_Action $action)
	{
		$action->view->form = new Form_ObjectInvestigateLogForm();
	}
}

