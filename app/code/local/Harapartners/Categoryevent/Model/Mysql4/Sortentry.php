<?php

class Harapartners_Categoryevent_Model_Mysql4_Sortentry
    extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {
        $this->_init('categoryevent/sortentry', 'id');
    }

    public function loadByDate($object, $date, $useRecent = true)
    {
        if (is_numeric($date)) {
            $date = date('Y-m-d', $date);
        }

        if ($read = $this->_getReadAdapter()) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable());
            if ($useRecent) {
                $select->where('DATE(date) <= ?', $date)
                    ->order('date DESC');
            } else {
                $select->where('DATE(date) = ?', $date);
            }

            $data = $read->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }

        $this->unserializeFields($object);
        $this->_afterLoad($object);

        return $this;
    }
}
