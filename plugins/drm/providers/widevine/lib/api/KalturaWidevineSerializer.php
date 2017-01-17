<?php

class BorhanWidevineSerializer extends BorhanSerializer
{
	/* (non-PHPdoc)
	 * @see BorhanSerializer::serialize()
	 */
	public function serialize($object) 
	{
		if (is_object($object) && $object instanceof Exception)
    	{
    		$assetid = 0;
    		$requestParams = requestUtils::getRequestParams();
			if(array_key_exists(WidevineLicenseProxyUtils::ASSETID, $requestParams))
			{
				$assetid = $requestParams[WidevineLicenseProxyUtils::ASSETID];
			}
			
			$object = WidevineLicenseProxyUtils::createErrorResponse(BorhanWidevineErrorCodes::GENERAL_ERROR, $assetid);
    	}
		
		return $object;		
	}
	
	public function setHttpHeaders()
	{
		header("Content-Type: text/plain");
		header("Content-Transfer-Encoding: base64");		
	}
}