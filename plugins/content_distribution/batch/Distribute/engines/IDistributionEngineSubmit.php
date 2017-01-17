<?php
/**
 * @package plugins.contentDistribution 
 * @subpackage Scheduler.Distribute
 */
interface IDistributionEngineSubmit extends IDistributionEngine
{
	/**
	 * sends media to external system.
	 * @param BorhanDistributionSubmitJobData $data
	 * @return bool true if finished, false if will be finished asynchronously
	 */
	public function submit(BorhanDistributionSubmitJobData $data);
}