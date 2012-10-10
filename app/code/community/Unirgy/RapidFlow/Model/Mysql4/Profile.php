<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Model_Mysql4_Profile extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('urapidflow/profile', 'profile_id');
    }

    public function sync(Mage_Core_Model_Abstract $object, $saveFields=null, $loadFields=null)
    {
        $conn = $this->_getWriteAdapter();
        $table = $this->getMainTable();

        $condition = $conn->quoteInto($this->getIdFieldName().'=?', $object->getId());

        if ($saveFields) {
            $saveData = array();
            foreach ($saveFields as $k) {
                $saveData[$k] = $object->getData($k);
            }
            $conn->update($table, $saveData, $condition);
        }

        if ($loadFields) {
            $loadData = $conn->fetchRow($conn->select()->from($table, $loadFields)->where($condition));
            foreach ($loadData as $k=>$v) {
                $object->setData($k, $v);
            }
        }

        return $this;
    }
}