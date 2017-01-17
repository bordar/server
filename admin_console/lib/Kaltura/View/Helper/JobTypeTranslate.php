<?php
/**
 * @package Admin
 * @subpackage views
 */
class Borhan_View_Helper_JobTypeTranslate extends Zend_View_Helper_Abstract
{
	public function jobTypeTranslate($jobType, $jobSubType = null)
	{
		if($jobType == Borhan_Client_Enum_BatchJobType::CONVERT && $jobSubType)
			return $this->view->enumTranslate('Borhan_Client_Enum_ConversionEngineType', $jobSubType);
			
		return $this->view->enumTranslate('Borhan_Client_Enum_BatchJobType', $jobType);
	}
}