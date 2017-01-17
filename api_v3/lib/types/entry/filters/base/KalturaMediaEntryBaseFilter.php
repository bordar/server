<?php
/**
 * @package api
 * @subpackage filters.base
 * @abstract
 */
abstract class BorhanMediaEntryBaseFilter extends BorhanPlayableEntryFilter
{
	static private $map_between_objects = array
	(
		"mediaTypeEqual" => "_eq_media_type",
		"mediaTypeIn" => "_in_media_type",
		"sourceTypeEqual" => "_eq_source_type",
		"sourceTypeNotEqual" => "_not_source_type",
		"sourceTypeIn" => "_in_source_type",
		"sourceTypeNotIn" => "_notin_source_type",
		"mediaDateGreaterThanOrEqual" => "_gte_media_date",
		"mediaDateLessThanOrEqual" => "_lte_media_date",
		"flavorParamsIdsMatchOr" => "_matchor_flavor_params_ids",
		"flavorParamsIdsMatchAnd" => "_matchand_flavor_params_ids",
	);

	static private $order_by_map = array
	(
		"+mediaType" => "+media_type",
		"-mediaType" => "-media_type",
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), self::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), self::$order_by_map);
	}

	/**
	 * @var BorhanMediaType
	 */
	public $mediaTypeEqual;

	/**
	 * @var string
	 */
	public $mediaTypeIn;

	/**
	 * @var BorhanSourceType
	 */
	public $sourceTypeEqual;

	/**
	 * @var BorhanSourceType
	 */
	public $sourceTypeNotEqual;

	/**
	 * @dynamicType BorhanSourceType
	 * @var string
	 */
	public $sourceTypeIn;

	/**
	 * @dynamicType BorhanSourceType
	 * @var string
	 */
	public $sourceTypeNotIn;

	/**
	 * @var time
	 */
	public $mediaDateGreaterThanOrEqual;

	/**
	 * @var time
	 */
	public $mediaDateLessThanOrEqual;

	/**
	 * @var string
	 */
	public $flavorParamsIdsMatchOr;

	/**
	 * @var string
	 */
	public $flavorParamsIdsMatchAnd;
}
