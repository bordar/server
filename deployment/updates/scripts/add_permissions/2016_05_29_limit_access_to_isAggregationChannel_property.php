<?php
/**
 * @package deployment
 * @subpackage kajam.roles_and_permissions
 */
$script = realpath(dirname(__FILE__) . '/../../../../') . '/alpha/scripts/utils/permissions/addPermissionsAndItems.php';
$config = realpath(dirname(__FILE__)) . '/../../../permissions/object.BorhanCategory.ini';
passthru("php $script $config");