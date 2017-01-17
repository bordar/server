<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanUrlTokenizerCht extends BorhanUrlTokenizer {

	public function toObject($dbObject = null, $skip = array())
	{
		if (is_null($dbObject))
			$dbObject = new kChtHttpUrlTokenizer();
			
		parent::toObject($dbObject, $skip);
	
		return $dbObject;
	}
}
