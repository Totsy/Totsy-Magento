<?php
/**
* @copyright Amasty.
*/
$this->startSetup();


$fieldsSql = 'SHOW COLUMNS FROM ' . $this->getTable('salesrule/rule');
$cols = $this->getConnection()->fetchCol($fieldsSql);

if (!in_array('promo_sku', $cols)){
    $this->run("ALTER TABLE `{$this->getTable('salesrule/rule')}` ADD `promo_sku` TEXT");
}

$this->endSetup();