<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->truncateTable($this->getTable('intime_warehouse'));
$installer->getConnection()->truncateTable($this->getTable('intime_city'));

$installer->getConnection()->changeColumn($this->getTable('intime_city'), 'ref', 'ref', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255
));
$installer->getConnection()->changeColumn($this->getTable('intime_warehouse'), 'ref', 'ref', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255
));

$installer->getConnection()->addIndex($this->getTable('intime_warehouse'),
    $installer->getConnection()->getIndexName($this->getTable('intime_warehouse'), array('ref'), 'unique'),
    array('ref'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex($this->getTable('intime_city'),
    $installer->getConnection()->getIndexName($this->getTable('intime_city'), array('ref'), 'unique'),
    array('ref'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
