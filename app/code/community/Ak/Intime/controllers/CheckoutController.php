<?php
class Ak_Intime_CheckoutController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Render form for choose city and warehouse
     */
    public function formAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('root')->setData('method', $this->getRequest()->getParam('method'));
        if ($cityId = $this->getRequest()->getParam('city')) {
            $this->getLayout()->getBlock('root')->setData('city_id', $cityId);
        }

        $this->renderLayout();
    }

    /**
     * Calculate shipping cost for destination
     */
    public function calculateAction()
    {
        $helper         = Mage::helper('novaposhta');
        $warehouseId    = (int) $this->getRequest()->getParam('warehouse');
        $cost         = $helper->getShippingCost($warehouseId);
        $cost         = $helper->currency( (float) $cost, true, false);

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();
//        $quote->getShippingAddress()->setData('warehouse_id', $warehouseId);
//        $quote->collectTotals()->save();

        $this->getResponse()->setBody($helper->jsonEncode(array('cost' => $cost)));
    }
}
