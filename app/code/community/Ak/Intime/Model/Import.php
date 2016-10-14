<?php
class Ak_Intime_Model_Import
{
    /** @var  array */
    protected $_existingCities;

    /** @var  array */
    protected $_existingWarehouses;

    /**
     * @return Ak_Intime_Model_Api_Client
     */
    protected function _getApiClient()
    {
        return Mage::getSingleton('intime/api_client');
    }

    /**
     * @throws Exception
     * @return Ak_Intime_Model_Import
     */
    public function run()
    {
        try {

            $data = $this->_getApiClient()->getWarehouses();

            Mage::helper('intime')->log('Start city import');
            $this->_importCities($data);
            Mage::helper('intime')->log('End city import');

            Mage::helper('intime')->log('Start warehouse import');
            $this->_importWarehouses($data);
            Mage::helper('intime')->log('End warehouse import');
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('intime')->log("Exception: \n" . $e->getMessage());
            throw $e;
        }

        return $this;
    }

    /**
     * @param array $data
     * @return bool
     * @throws Exception
     */
    protected function _importCities(array $data)
    {
        if (empty($data)) {
            Mage::helper('intime')->log('No city with warehouses received');
            throw new Exception('No city with warehouses received');
        }
        $cities = array();
        foreach ($data as $info) {
            $cities[$info['city']] = array(
                'ref' => $info['city'],
                'name_ru' => $info['city'],
                'name_ua' => $info['city'],
            );
        }

        $tableName  = Mage::getSingleton('core/resource')->getTableName('intime_city');
        $connection = $this->_getConnection();

        $citiesToDelete = array_diff($this->_getExistingCities(), array_keys($cities));

        if (count($citiesToDelete) > 0) {
            $connection->delete($tableName, array('ref' => $citiesToDelete));
            Mage::helper('intime')->log(sprintf("Warehouses deleted: %s", implode(',', $citiesToDelete)));
        }

        if (count($cities) > 0) {
            $tableName  = Mage::getSingleton('core/resource')->getTableName('intime_city');
            $connection = $this->_getConnection();
            $connection->beginTransaction();
            try {
                foreach ($cities as $data) {
                    $connection->insertOnDuplicate($tableName, $data);
                }
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    protected function _getExistingCities()
    {
        if (!$this->_existingCities) {
            /** @var Ak_Intime_Model_Resource_City_Collection $collection */
            $collection = Mage::getResourceModel('intime/city_collection');
            $this->_existingCities = $collection->getColumnValues('ref');
        }
        return $this->_existingCities;
    }

    /**
     * @param array $existingCity
     * @param array $city
     *
     * @return bool
     */
    protected function _isCityChanged(array $existingCity, array $city)
    {
        foreach ($existingCity as $key => $value) {
            if (isset($city[$key]) && $city[$key] != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getConnection()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /**
     * @return bool
     * @throws Exception
     */
    protected function _importWarehouses($data)
    {
        /** @var Ak_Intime_Model_Resource_City_Collection $cities */
        $cities = Mage::getResourceModel('intime/city_collection');
        $connection = $this->_getConnection();
        $tableName  = Mage::getSingleton('core/resource')->getTableName('intime_warehouse');
        $exists = $this->_getExistingWarehouses();

        foreach ($data as $warehouseInfo) {
            $warehouseInfo['city_id'] = $cities->getItemByColumnValue('ref', $warehouseInfo['city'])->getId();
            unset($warehouseInfo['city']);
            $connection->beginTransaction();
            try {
                $connection->insertOnDuplicate($tableName, $warehouseInfo);
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        $warehousesToDelete = array_diff($exists, array_keys($data));
        if (count($warehousesToDelete) > 0) {
            $connection->delete($tableName, array('ref' => $warehousesToDelete));
            Mage::helper('intime')->log(sprintf("Warehouses deleted: %s", implode(',', $warehousesToDelete)));
        }

        return true;
    }

    /**
     * @return array
     */
    protected function _getExistingWarehouses()
    {
        if (!$this->_existingWarehouses) {
            /** @var Ak_Intime_Model_Resource_Warehouse_Collection $collection */
            $collection = Mage::getResourceModel('intime/warehouse_collection');
            $this->_existingWarehouses = $collection->getColumnValues('ref');
        }
        return $this->_existingWarehouses;
    }
}
