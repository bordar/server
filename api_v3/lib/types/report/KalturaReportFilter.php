<?php
/**
 * @package api
 * @subpackage objects
 */
class BorhanReportFilter extends BorhanObject
{
	/**
	 * The dimension whose values should be filtered
	 * @var string
	 */
	public $dimension;

	/**
	 * The (comma separated) values to include in the filter
	 * @var string
	 */
	public $values;

}