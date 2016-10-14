<?php
class Ak_NovaPoshta_Model_Api_Client
{
    protected $_client;

    const DELIVERY_TYPE_APARTMENT_APARTMENT = "DoorsDoors";
    const DELIVERY_TYPE_APARTMENT_WAREHOUSE = "DoorsWarehouse";
    const DELIVERY_TYPE_WAREHOUSE_APARTMENT = "WarehouseDoors";
    const DELIVERY_TYPE_WAREHOUSE_WAREHOUSE = "WarehouseWarehouse";

    const LOAD_TYPE_STANDARD   = 1;
    const LOAD_TYPE_SECURITIES = 4;

    /**
     * @return string
     * @throws Exception
     */
    protected function _getApiKey()
    {
        $key = Mage::helper('novaposhta')->getStoreConfig('api_key');
        if (!trim($key)) {
            Mage::helper('novaposhta')->log('No API key configured');
            throw new Exception('No API key configured');
        }

        return $key;
    }

    /**
     * @return NovaPoshta_Api2
     */
    protected function _getClient()
    {
        if (!$this->_client) {
            $this->_client = new NovaPoshta_Api2(
                $this->_getApiKey(),
                'ru', // Язык возвращаемых данных: ru (default) | ua | en
                FALSE, // При ошибке в запросе выбрасывать Exception: FALSE (default) | TRUE
                'curl' // Используемый механизм запроса: curl (defalut) | file_get_content
            );
        }

        return $this->_client;
    }

    /**
     * @return array
     */
    public function getCityWarehouses()
    {
        $response = $this->_getClient()->getCities();
        $result = array();
        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $key => $city) {
                $this->_cityMarshaling($city, $key);
                $result[$city['ref']] = $city;
            }
        }

        return $result;
    }

    /**
     * @param array $cityData
     * @param       $key
     */
    public function _cityMarshaling(array &$cityData, $key)
    {
        $data = array();
        $data['name_ru'] = $cityData['DescriptionRu'];
        $data['name_ua'] = $cityData['Description'];
        $data['ref']     = $cityData['Ref'];
        $data['id']      = $cityData['CityID'];

        $cityData = $data;
    }

    /**
     * @param Varien_Object $cityInfo
     * @return array
     */
    public function getWarehouses(Varien_Object $cityInfo)
    {
        $warehouses = array();
        $response = $this->_getClient()->getWarehouses($cityInfo['ref']);
        if (isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $key => $value) {
                $this->_warehouseMarshaling($value, $key);
                $value['city_id']          = $cityInfo['id'];
                $warehouses[$value['ref']] = $value;
            }
        }

        return $warehouses;
    }

    /**
     * @param array $warehouseData
     * @param $key
     */
    public function _warehouseMarshaling(array &$warehouseData, $key)
    {
        $data = array();
        $data['ref']      = $warehouseData['Ref'];
        $data['address_ru'] = $warehouseData['DescriptionRu'];
        $data['address_ua'] = $warehouseData['Description'];
        $data['phone']     = $warehouseData['Phone'];
        $data['longitude'] = $warehouseData['Longitude'];
        $data['latitude'] = $warehouseData['Latitude'];
        $data['max_weight_allowed'] = $warehouseData['TotalMaxWeightAllowed'];
        $data['number_in_city'] = $warehouseData['Number'];

        $warehouseData = $data;
    }

    /**
     * @param Zend_Date                $deliveryDate
     * @param Ak_NovaPoshta_Model_City $senderCity
     * @param Ak_NovaPoshta_Model_City $recipientCity
     * @param float                    $packageWeight
     * @param float                    $publicPrice
     * @param string                   $deliveryType
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getShippingCost(
        Zend_Date $deliveryDate, // Not implemented
        Ak_NovaPoshta_Model_City $senderCity,
        Ak_NovaPoshta_Model_City $recipientCity,
        $packageWeight,
        $publicPrice,
        $deliveryType = self::DELIVERY_TYPE_WAREHOUSE_WAREHOUSE)
    {
        $response = $this->_getClient()->getDocumentPrice(
            $senderCity->getData('ref'),
            $recipientCity->getData('ref'),
            $deliveryType,
            $packageWeight,
            $publicPrice
        );

        if (!isset($response['success']) || $response['success'] != true) {
            Mage::throwException('Novaposhta Api Error: ' . (empty($response['errors']) ? 'unknown' : implode('; ', (array) $response['errors'])));
        }

        return (float) $response['data'][0]["Cost"];
    }
}
