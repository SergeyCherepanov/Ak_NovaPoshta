<?php
class Ak_Intime_Model_Api_Client
{
    const DELIVERY_TYPE_APARTMENT_APARTMENT = "DoorsDoors";
    const DELIVERY_TYPE_APARTMENT_WAREHOUSE = "DoorsWarehouse";
    const DELIVERY_TYPE_WAREHOUSE_APARTMENT = "WarehouseDoors";
    const DELIVERY_TYPE_WAREHOUSE_WAREHOUSE = "WarehouseWarehouse";

    const LOAD_TYPE_STANDARD   = 1;
    const LOAD_TYPE_SECURITIES = 4;

    /** @var  Zend_Soap_Client */
    protected $_client;

    /**
     * @return string
     * @throws Exception
     */
    protected function _getApiId()
    {
        $key = Mage::helper('intime')->getStoreConfig('api_id');
        if (!trim($key)) {
            Mage::helper('intime')->log('No API Id configured');
            throw new Exception('No API Id configured');
        }

        return $key;
    }


    /**
     * @return string
     * @throws Exception
     */
    protected function _getApiKey()
    {
        $key = Mage::helper('intime')->getStoreConfig('api_key');
        if (!trim($key)) {
            Mage::helper('intime')->log('No API key configured');
            throw new Exception('No API key configured');
        }

        return $key;
    }

    /**
     * @return Zend_Soap_Client
     */
    protected function _getClient()
    {
        if (!$this->_client) {
            $this->_client = new Zend_Soap_Client('https://ws.intime.ua/API/ws/API20/?wsdl');
        }

        return $this->_client;
    }

    /**
     * @return array
     */
    public function getWarehouses()
    {
        $response = $this->_getClient()->CatalogList(array(
            'CatalogListRequest' => array(
                'AuthData' => array(
                    'ID'  => $this->_getApiId(),
                    'KEY' => $this->_getApiKey()
                ),
                'CatalogNameEng' => 'Departments'
            )
        ));

        $result = array();

        foreach ($response->{'return'}->{'ListCatalog'}->{'Catalog'} as $itemInfo) {
            $info = $this->_warehouseMarshaling($itemInfo);
            $result[$info['ref']] = $info;
        }

        return $result;
    }

    /**
     * @param $warehouseData
     * @return array
     */
    public function _warehouseMarshaling($warehouseData)
    {
        $data = array();
        $data['ref']      = $warehouseData->{'Code'};

        foreach ($warehouseData->{'AppendField'} as $info) {
            switch ($info->{'AppendFieldName'}) {
                case 'Adress':
                    $data['address_ru'] = $info->{'AppendFieldValue'};
                    $data['address_ua'] = $info->{'AppendFieldValue'};
                    break;
                case 'Longitude':
                    $data['longitude'] = $info->{'AppendFieldValue'};
                    break;
                case 'Latitude':
                    $data['latitude'] = $info->{'AppendFieldValue'};
                    break;
                case 'City':
                    $data['city'] = $info->{'AppendFieldValue'};
                    break;
                case 'Tel':
                    $data['phone'] = $info->{'AppendFieldValue'};
                    break;
                case 'WeightLimit':
                    $data['max_weight_allowed'] = $info->{'AppendFieldValue'};
                    break;
                case 'WarehouseNumberInCity':
                    $data['number_in_city'] = $info->{'AppendFieldValue'};
                    break;
            }
        }

        return $data;
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
