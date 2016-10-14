<?php
/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$installer->getConnection()->truncateTable($this->getTable('novaposhta_city'));
$installer->getConnection()->truncateTable($this->getTable('novaposhta_warehouse'));

if (!$installer->getConnection()->tableColumnExists($this->getTable('novaposhta_city'), 'ref')) {
    $installer->getConnection()->addColumn($this->getTable('novaposhta_city'), 'ref', array(
        'comment' => 'NovaPoshta Ref',
        'type'    => Varien_Db_Ddl_Table::TYPE_TEXT,
        'size'    => Varien_Db_Ddl_Table::DEFAULT_TEXT_SIZE
    ));
}

if (!$installer->getConnection()->tableColumnExists($this->getTable('novaposhta_warehouse'), 'ref')) {
    $installer->getConnection()->addColumn($this->getTable('novaposhta_warehouse'), 'ref', array(
        'comment' => 'NovaPoshta Ref',
        'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
        'size' => Varien_Db_Ddl_Table::DEFAULT_TEXT_SIZE
    ));
}

$installer->getConnection()->changeColumn($this->getTable('novaposhta_city'), 'id', 'id', 'int(10) unsigned NOT NULL AUTO_INCREMENT');
$installer->getConnection()->changeColumn($this->getTable('novaposhta_warehouse'), 'id', 'id', 'int(10) unsigned NOT NULL AUTO_INCREMENT');

$installer->endSetup();
