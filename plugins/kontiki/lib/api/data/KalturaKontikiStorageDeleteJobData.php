<?php
/**
 * @package plugins.kontiki
 * @subpackage api.object
 */
class BorhanKontikiStorageDeleteJobData extends BorhanStorageDeleteJobData
{
     /**
     * Unique Kontiki MOID for the content uploaded to Kontiki
     * @var string
     */
    public $contentMoid;

    /**
     * @var string
     */
    public $serviceToken;
    
    private static $map_between_objects = array
    (
        'serviceToken',
        'contentMoid',
    );
    
    /* (non-PHPdoc)
     * @see BorhanObject::getMapBetweenObjects()
     */
    public function getMapBetweenObjects ( )
    {
        return array_merge ( parent::getMapBetweenObjects() , self::$map_between_objects );
    }
    
    /* (non-PHPdoc)
     * @see BorhanObject::toObject()
     */
    public function toObject($dbData = null, $props_to_skip = array()) 
    {
        if(is_null($dbData))
            $dbData = new kKontikiStorageDeleteJobData();
            
        return parent::toObject($dbData);
    }
    
}
