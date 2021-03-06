<?php
class Ak_NovaPoshta_Block_Checkout_Shipping_Destination
    extends Mage_Core_Block_Template
{
    /**
     * @return bool
     */
    public function canShowStreet()
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ('novaposhta_type_' . Ak_NovaPoshta_Model_Api_Client::DELIVERY_TYPE_WAREHOUSE_APARTMENT == $quote->getShippingAddress()->getShippingMethod() ||
            'novaposhta_type_' . Ak_NovaPoshta_Model_Api_Client::DELIVERY_TYPE_WAREHOUSE_APARTMENT == $this->getData('method')) {
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

        return $quote->getShippingAddress()->getData('novaposhta_street');
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
            $warehouse = Mage::getModel('novaposhta/warehouse')->load($warehouseId);
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
     * @return Ak_NovaPoshta_Model_Resource_City_Collection
     */
    public function getCities()
    {
        /** @var Ak_NovaPoshta_Model_Resource_City_Collection $collection */
        $collection = Mage::getResourceModel('novaposhta/city_collection');
        $collection->setOrder('name_ru', 'ASC');

        return $collection;
    }

    /**
     * @return Ak_NovaPoshta_Model_Resource_Warehouse_Collection|bool
     */
    public function getWarehouses()
    {
        if ($cityId = $this->getCityId()) {
            /** @var Ak_NovaPoshta_Model_Resource_Warehouse_Collection $collection */
            $collection = Mage::getResourceModel('novaposhta/warehouse_collection');
            $collection->addFieldToFilter('city_id', $cityId);
            $collection->setOrder('address_ru');
            
            if (!count($collection)) {
                $placeHolder = Mage::getModel('novaposhta/warehouse');
                $placeHolder->setData(array(
                    'city_id' => $cityId,
                    'address_ru' => 'Уточнить по телефону'
                ));
                $collection->addItem($placeHolder);
            }

            return $collection;
        }

        return false;
    }
}
