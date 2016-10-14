<?php
class Ak_Intime_Model_System_Config_City
{
    /**
     * @param bool $isMultiselect
     * @return array
     */
    public function toOptionArray($isMultiselect = false)
    {
        /** @var Ak_Intime_Model_Resource_City_Collection $colllection */
        $colllection = Mage::getResourceModel('intime/city_collection');
        $options     = $colllection->toOptionArray();

        if (!$isMultiselect) {
            array_unshift($options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('--Please Select--')));
        }

        return $options;
    }
}
