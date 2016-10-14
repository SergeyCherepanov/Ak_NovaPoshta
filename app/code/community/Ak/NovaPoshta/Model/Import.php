<?php
class Ak_NovaPoshta_Model_Import
{
    /** @var  array */
    protected $_existingCities;

    /** @var  array */
    protected $_existingWarehouses;

    /**
     * @return Ak_NovaPoshta_Model_Api_Client
     */
    protected function _getApiClient()
    {
        return Mage::getSingleton('novaposhta/api_client');
    }

    /**
     * @throws Exception
     * @return Ak_NovaPoshta_Model_Import
     */
    public function run()
    {
        try {
            Mage::helper('novaposhta')->log('Start city import');
            $cities = $this->_getApiClient()->getCityWarehouses();
            $this->_importCities($cities);
            Mage::helper('novaposhta')->log('End city import');

            Mage::helper('novaposhta')->log('Start warehouse import');
            $this->_importWarehouses();
            Mage::helper('novaposhta')->log('End warehouse import');
        } catch (Exception $e) {
            Mage::logException($e);
            Mage::helper('novaposhta')->log("Exception: \n" . $e->getMessage());
            throw $e;
        }

        return $this;
    }

    /**
     * @param array $cities
     * @return bool
     * @throws Exception
     */
    protected function _importCities(array $cities)
    {
        if (empty($cities)) {
            Mage::helper('novaposhta')->log('No city with warehouses received');
            throw new Exception('No city with warehouses received');
        }

        $tableName  = Mage::getSingleton('core/resource')->getTableName('novaposhta_city');
        $connection = $this->_getConnection();

        $citiesToDelete = array_diff($this->_getExistingCities(), array_keys($cities));

        if (count($citiesToDelete) > 0) {
            $connection->delete($tableName, array('ref' => $citiesToDelete));
            Mage::helper('novaposhta')->log(sprintf("Warehouses deleted: %s", implode(',', $citiesToDelete)));
        }

        if (count($cities) > 0) {
            $tableName  = Mage::getSingleton('core/resource')->getTableName('novaposhta_city');
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
            /** @var Ak_NovaPoshta_Model_Resource_City_Collection $collection */
            $collection = Mage::getResourceModel('novaposhta/city_collection');
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
     * @param int|null $cityId
     * @return bool
     * @throws Exception
     */
    protected function _importWarehouses($cityId = null)
    {
        $cities = Mage::getResourceModel('novaposhta/city_collection');
        if ($cityId) {
            $cities->addFieldToFilter('id', $cityId);
        }
        $connection = $this->_getConnection();
        $tableName  = Mage::getSingleton('core/resource')->getTableName('novaposhta_warehouse');
        $exists = $this->_getExistingWarehouses($cityId);
        $newWarehouses = array();
        
        foreach ($cities as $cityInfo) {
            $warehouses = $this->_getApiClient()->getWarehouses($cityInfo);
            $newWarehouses = array_merge($newWarehouses, array_keys($warehouses));
            $connection->beginTransaction();
            try {
                foreach ($warehouses as $data) {
                    $connection->insertOnDuplicate($tableName, $data);
                }
                $connection->commit();
            } catch (Exception $e) {
                $connection->rollBack();
                throw $e;
            }
        }

        $warehousesToDelete = array_diff($exists, $newWarehouses);
        
        if ($warehousesToDelete) {
            $where = array(sprintf('ref IN (\'%s\')', implode('\',\'', $warehousesToDelete)));
            if ($cityId) {
                $where[] = sprintf('city_id = %d', $cityId);
            }
            $connection->delete($tableName, implode(' AND ', $where));
            Mage::helper('novaposhta')->log(sprintf("Warehouses deleted: %s", implode(',', $warehousesToDelete)));
        }

        return true;
    }

    /**
     * @param null $cityId
     * @return array
     */
    protected function _getExistingWarehouses($cityId = null)
    {
        if (!$this->_existingWarehouses) {
            /** @var Ak_NovaPoshta_Model_Resource_Warehouse_Collection $collection */
            $collection = Mage::getResourceModel('novaposhta/warehouse_collection');
            if ($cityId) {
                $collection->addFieldToFilter('city_id', $cityId);
            }
            $this->_existingWarehouses = $collection->getColumnValues('ref');
        }
        
        return $this->_existingWarehouses;
    }
}
