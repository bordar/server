<?php
/**
 * @package api
 * @subpackage enum
 */
class BorhanFlavorReadyBehaviorType extends BorhanEnum
{
	const NO_IMPACT = 0;
	const REQUIRED = 1;
	const OPTIONAL = 2;
	
	/**
	 * @deprecated use NO_IMPACT instead
	 */
	const INHERIT_FLAVOR_PARAMS = 0;
}
