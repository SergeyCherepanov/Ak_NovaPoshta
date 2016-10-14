<?php
class Ak_Intime_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadQuoteAddressCollectionData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Resource_Quote_Address_Collection $addressCollection */
        $addressCollection = $observer->getData('quote_address_collection');

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('intime_quote_address'));
        $select->where('address_id IN (?)', $addressCollection->getAllIds());

        foreach ($connection->fetchAll($select) as $row) {
            $addressId = $row['address_id'];
            unset($row['address_id']);
            $address = $addressCollection->getItemById($addressId);
            foreach ($row as $key => $value) {
                $address->setData('intime_' . $key, $value);
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadQuoteAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Address $address */
        $address = $observer->getData('quote_address');
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('intime_quote_address'));
        $select->where('address_id = ?', $address->getId());

        if ($data = $connection->fetchRow($select)) {
            unset($data['address_id']);
            foreach ($data as $key => $value) {
                $address->setData('intime_' . $key, $value);
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveQuoteAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote_Address $address */
        $address        = $observer->getData('quote_address');

        /** @var Mage_Core_Model_Resource $resource */
        $resource       = Mage::getSingleton('core/resource');
        $connection     = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $warehouseId    = $address->getData('intime_warehouse_id');
        $warehouseLabel = '';
        $street         = '';

        if ($warehouseId) {
            /** @var Ak_Intime_Model_Warehouse $warhouse */
            $warehouse = Mage::getModel('intime/warehouse')->load($warehouseId);
            $warehouseLabel = implode(', ', array(
                $warehouse->getCity()->getData('name_ru'),
                $warehouse->getData('address_ru'),
                $warehouse->getData('phone')
            ));
            $street = $address->getData('intime_intime_street');
            if ($street) {
                $warehouseLabel .= ', Адресс Клиента: ' . $street;
            }
        }

        $data = array(
            'address_id'      => $address->getId(),
            'warehouse_id'    => $warehouseId,
            'warehouse_label' => $warehouseLabel,
            'intime_street' => $street,
        );

        $tableName = $resource->getTableName('intime_quote_address');

        if ($data['warehouse_id'] || $data['warehouse_label']) {
            $connection->insertOnDuplicate($tableName, $data);
        } else {
            $connection->delete($tableName, sprintf('address_id = %d', $data['address_id']));
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadOrderAddressCollectionData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Resource_Order_Address_Collection $addressCollection */
        $addressCollection = $observer->getData('order_address_collection');

        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('intime_order_address'));
        $select->where('address_id IN (?)', $addressCollection->getAllIds());

        foreach ($connection->fetchAll($select) as $row) {
            $addressId = $row['address_id'];
            unset($row['address_id']);
            $address = $addressCollection->getItemById($addressId);
            foreach ($row as $key => $value) {
                $address->setData('intime_' . $key, $value);
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function loadOrderAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = $observer->getData('address');
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $select = $connection->select();
        $select->from($resource->getTableName('intime_order_address'));
        $select->where('address_id = ?', $address->getId());

        if ($data = $connection->fetchRow($select)) {
            unset($data['address_id']);
            foreach ($data as $key => $value) {
                $address->setData('intime_' . $key, $value);
            }
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveOrderAddressData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Address $address */
        $address = $observer->getData('address');
        if (!$this->checkShippingMethod($address->getOrder()->getShippingMethod())) {
            return;
        }
        /** @var Mage_Core_Model_Resource $resource */
        $resource       = Mage::getSingleton('core/resource');
        $connection     = $resource->getConnection(Mage_Core_Model_Resource::DEFAULT_WRITE_RESOURCE);
        $warehouseId    = $address->getData('intime_warehouse_id');
        $warehouseLabel = '';
        $street         = '';

        if ($warehouseId) {
            /** @var Ak_Intime_Model_Warehouse $warhouse */
            $warehouse = Mage::getModel('intime/warehouse')->load($warehouseId);
            $warehouseLabel = implode(', ', array(
                $warehouse->getCity()->getData('name_ru'),
                $warehouse->getData('address_ru'),
                $warehouse->getData('phone')
            ));
            $street = $address->getData('intime_intime_street');
            if ($street) {
                $warehouseLabel .= ', Адресс Клиента: ' . $street;
            }
        }

        $data = array(
            'address_id'      => $address->getId(),
            'warehouse_id'    => $warehouseId,
            'warehouse_label' => $warehouseLabel,
            'intime_street' => $street,
        );

        $tableName = $resource->getTableName('intime_order_address');

        if ($data['warehouse_id'] || $data['warehouse_label']) {
            $connection->insertOnDuplicate($tableName, $data);
        } else {
            $connection->delete($tableName, sprintf('address_id = %d', $data['address_id']));
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function saveOrderData(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('order');

        if ($this->checkShippingMethod($order->getShippingMethod())) {
            if ($warehouseLabel = $order->getShippingAddress()->getData('intime_warehouse_label')) {
                $shippingDescription = $order->getData('shipping_description');
                $order->setData('shipping_description', $shippingDescription . PHP_EOL . " ({$warehouseLabel}) ");
            }
        }

        return $this;
    }

    /**
     * @param $method
     * @return bool
     */
    protected function checkShippingMethod($method)
    {
        return (bool) preg_match('/^intime_type_\w+$/i', $method);
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function saveShippingMethodBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_Core_Controller_Varien_Action $controller */
        $controller = $observer->getData('controller_action');
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        if ($this->checkShippingMethod($controller->getRequest()->getParam('shipping_method'))) {
            $id = $controller->getRequest()->getParam('intime_warehouse', null);
            if (!is_null($id)) {
                $warehouse = Mage::getModel('intime/warehouse')->load($id);
                if ($warehouse->getId()) {
                    $quote->getShippingAddress()->setData('intime_warehouse_id', $warehouse->getId());
                    $quote->getShippingAddress()->setData('intime_intime_street',
                        $controller->getRequest()->getParam('intime_street', null)
                    );
                }
            } else {
                $quote->getShippingAddress()->unsetData('intime_warehouse_id');
            }
        } else {
            if (!preg_match('/^intime_type_\w+$/i', $quote->getShippingAddress()->getShippingMethod())) {
                $quote->getShippingAddress()->unsetData('intime_warehouse_id');
            }
        }
    }
}

