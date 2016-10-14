<?php
class Ak_Intime_Model_Resource_Warehouse
    extends Mage_Core_Model_Resource_Db_Abstract
{
    public function _construct()
    {
        $this->_init('intime/warehouse', 'id');
    }
}
