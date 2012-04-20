<?php

/**
 * Harapartners
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Harapartners License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.Harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@Harapartners.com so we can send you a copy immediately.
 *
 */

class Harapartners_Service_Model_Rewrite_CatalogInventory_Resource_Stock extends Mage_CatalogInventory_Model_Resource_Stock {
   
    public function correctItemsQty($stock, $productQtys, $operator = '-') {
        if (empty($productQtys)) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();
        $conditions = array();
        foreach ($productQtys as $productId => $qty) {
            $case = $adapter->quoteInto('?', $productId);
            $result = $adapter->quoteInto("qty{$operator}?", $qty);
            $conditions[$case] = $result;
        }

        $value = $adapter->getCaseSql('product_id', $conditions, 'qty');

        $where = array(
            'product_id IN (?)' => array_keys($productQtys),
            'stock_id = ?'      => $stock->getId()
        );

        $adapter->beginTransaction();
        $adapter->update($this->getTable('cataloginventory/stock_item'), array('qty' => $value), $where);
        
        //Harapartners, Jun, batch update without saving 'stock_item' object, must sync with status (cart reservation for Totsy)
        $adapter->update($this->getTable('cataloginventory/stock_status'), array('qty' => $value), $where);
        //Harapartners, Jun, batch update about 'in_stock', 'out_of_stock' and 'low_stock_date' also have similar problems
        
        $adapter->commit();

        return $this;
    }
    
}