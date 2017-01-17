<?php
// This file generated by Propel  convert-conf target
// from XML runtime conf file C:\opt\borhan\app\alpha\config\runtime-conf.xml
return array (
  'datasources' => 
  array (
    'borhan' => 
    array (
      'adapter' => 'mysql',
      'connection' => 
      array (
        'phptype' => 'mysql',
        'database' => 'borhan',
        'hostspec' => 'localhost',
        'username' => 'root',
        'password' => 'root',
      ),
    ),
    'default' => 'borhan',
  ),
  'log' => 
  array (
    'ident' => 'borhan',
    'level' => '7',
  ),
  'generator_version' => '1.4.2',
  'classmap' => 
  array (
    'DistributionProfileTableMap' => 'lib/model/map/DistributionProfileTableMap.php',
    'DistributionProfilePeer' => 'lib/model/DistributionProfilePeer.php',
    'DistributionProfile' => 'lib/model/DistributionProfile.php',
    'EntryDistributionTableMap' => 'lib/model/map/EntryDistributionTableMap.php',
    'EntryDistributionPeer' => 'lib/model/EntryDistributionPeer.php',
    'EntryDistribution' => 'lib/model/EntryDistribution.php',
    'GenericDistributionProviderTableMap' => 'lib/model/map/GenericDistributionProviderTableMap.php',
    'GenericDistributionProviderPeer' => 'lib/model/GenericDistributionProviderPeer.php',
    'GenericDistributionProvider' => 'lib/model/GenericDistributionProvider.php',
    'GenericDistributionProviderActionTableMap' => 'lib/model/map/GenericDistributionProviderActionTableMap.php',
    'GenericDistributionProviderActionPeer' => 'lib/model/GenericDistributionProviderActionPeer.php',
    'GenericDistributionProviderAction' => 'lib/model/GenericDistributionProviderAction.php',
  ),
);