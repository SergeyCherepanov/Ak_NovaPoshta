<?php
class Ak_Intime_Block_Checkout_Shipping_Destination
    extends Mage_Core_Block_Template
{
    /**
     * @return bool
     */
    public function canShowStreet()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ('intime_type_' . Ak_Intime_Model_Api_Client::DELIVERY_TYPE_WAREHOUSE_APARTMENT == $quote->getShippingAddress()->getShippingMethod() ||
            'intime_type_' . Ak_Intime_Model_Api_Client::DELIVERY_TYPE_WAREHOUSE_APARTMENT == $this->getData('method')) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        return $quote->getShippingAddress()->getData('intime_street');
    }

    /**
     * @return Ak_NovaPoshta_Model_Warehouse|bool
     */
    public function getWarehouse()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $warehouseId = $quote->getShippingAddress()->getData('warehouse_id');
        if ($warehouseId) {
            $warehouse = Mage::getModel('intime/warehouse')->load($warehouseId);
            if ($warehouse->getId()) {
                return $warehouse;
            }
        }

        return false;
    }

    /**
     * @return bool|int
     */
    public function getCityId()
    {
        $cityId = (int) $this->getData('city_id');
        if (!$cityId) {
            $warehouse = $this->getWarehouse();
            if ($warehouse) {
                $cityId =  $warehouse->getCity()->getId();;
                $this->setData('city_id', $cityId);
            }
        }

        if ($cityId) {
            return $cityId;
        }

        return false;
    }

    /**
     * @return Ak_Intime_Model_Resource_City_Collection
     */
    public function getCities()
    {
        /** @var Ak_Intime_Model_Resource_City_Collection $collection */
        $collection = Mage::getResourceModel('intime/city_collection');
        $collection->setOrder('name_ru', 'ASC');

        return $collection;
    }

    /**
     * @return Ak_Intime_Model_Resource_Warehouse_Collection|bool
     */
    public function getWarehouses()
    {
        if ($cityId = $this->getCityId()) {
            /** @var Ak_Intime_Model_Resource_Warehouse_Collection $collection */
            $collection = Mage::getResourceModel('intime/warehouse_collection');
            $collection->addFieldToFilter('city_id', $cityId);
            $collection->setOrder('address_ru');

            return $collection;
        }

        return false;
    }
}
