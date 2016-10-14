<?php
class Ak_Intime_Model_Resource_City
    extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('intime/city', 'id');
    }
}
