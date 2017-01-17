<?php
/**
 * @package api
 * @subpackage filters.enum
 */
class BorhanResponseProfileOrderBy extends BorhanStringEnum
{
	const CREATED_AT_ASC = "+createdAt";
	const CREATED_AT_DESC = "-createdAt";
	const UPDATED_AT_ASC = "+updatedAt";
	const UPDATED_AT_DESC = "-updatedAt";
}
