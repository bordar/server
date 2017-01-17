<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanBatchHistoryDataArray extends BorhanTypedArray
{
	public static function fromDbArray($arr, BorhanDetachedResponseProfile $responseProfile = null)
	{
		$newArr = new BorhanBatchHistoryDataArray();
		if ($arr == null)
			return $newArr;

		foreach ($arr as $obj)
		{
    		$nObj = new BorhanBatchHistoryData();
			$nObj->fromObject($obj, $responseProfile);
			$newArr[] = $nObj;
		}
		
		return $newArr;
	}
		
	public function __construct()
	{
		parent::__construct("BorhanBatchHistoryData");	
	}
}