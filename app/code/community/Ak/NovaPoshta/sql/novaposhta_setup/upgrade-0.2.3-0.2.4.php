<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->truncateTable($this->getTable('novaposhta_warehouse'));
$installer->getConnection()->truncateTable($this->getTable('novaposhta_city'));

$installer->getConnection()->changeColumn($this->getTable('novaposhta_city'), 'ref', 'ref', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255
));
$installer->getConnection()->changeColumn($this->getTable('novaposhta_warehouse'), 'ref', 'ref', array(
    'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 255
));

$installer->getConnection()->addIndex($this->getTable('novaposhta_warehouse'),
    $installer->getConnection()->getIndexName($this->getTable('novaposhta_warehouse'), array('ref'), 'unique'),
    array('ref'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->getConnection()->addIndex($this->getTable('novaposhta_city'),
    $installer->getConnection()->getIndexName($this->getTable('novaposhta_city'), array('ref'), 'unique'),
    array('ref'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
