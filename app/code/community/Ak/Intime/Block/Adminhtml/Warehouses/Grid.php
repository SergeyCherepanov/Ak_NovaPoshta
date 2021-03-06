<?php
class Ak_Intime_Block_Adminhtml_Warehouses_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('city_id');
        $this->setId('warehousesGrid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var $collection Intime_Model_Resource_Warehouse_Collection */
        $collection = Mage::getModel('intime/warehouse')
            ->getCollection();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header' => $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'id'
            )
        );

        $this->addColumn('address_ru',
            array(
                'header' => $this->__('Address (ru)'),
                'index' => 'address_ru'
            )
        );


        $options = Mage::getResourceModel('intime/city_collection')->toOptionHash();

        $this->addColumn('city_id',
            array(
                 'header'  => $this->__('City'),
                 'index'   => 'city_id',
                 'type'    => 'options',
                 'options' => $options,
            )
        );

        $this->addColumn('phone',
            array(
                 'header' => $this->__('Phone'),
                 'index' => 'phone'
            )
        );

        $this->addColumn('max_weight_allowed',
            array(
                 'header' => $this->__('Max weight'),
                 'index' => 'max_weight_allowed'
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return false;
    }

}
