<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

if ($installer->getConnection()->tableColumnExists($this->getTable('novaposhta_quote_address'), 'street')) {
    $installer->getConnection()->dropColumn($this->getTable('novaposhta_quote_address'), 'street');
}

if ($installer->getConnection()->tableColumnExists($this->getTable('novaposhta_order_address'), 'street')) {
    $installer->getConnection()->dropColumn($this->getTable('novaposhta_order_address'), 'street');
}

if (!$installer->getConnection()->tableColumnExists($this->getTable('novaposhta_quote_address'), 'novaposhta_street')) {
    $installer->getConnection()->addColumn($this->getTable('novaposhta_quote_address'), 'novaposhta_street', array(
        'comment' => 'Street',
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'size'    => 255
    ));
}
if (!$installer->getConnection()->tableColumnExists($this->getTable('novaposhta_order_address'), 'novaposhta_street')) {
    $installer->getConnection()->addColumn($this->getTable('novaposhta_order_address'), 'novaposhta_street', array(
        'comment' => 'Street',
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'size' => 255
    ));
}

$installer->endSetup();
