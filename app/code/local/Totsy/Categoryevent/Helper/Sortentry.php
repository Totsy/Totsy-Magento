<?php
/**
 * @category    Totsy
 * @package     Totsy_Categoryevent_Helper
 * @author      Slavik Koshelevskyy <troyer@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */
class Totsy_Categoryevent_Helper_Sortentry
{

    protected $_detartmentsAges = array();
    protected $_idMap = array();

    public function __construct () 
    {
        $this->_getAttributeOptions('departments');
        $this->_getAttributeOptions('ages');
    }

    public function getCategories(&$event,$type='')
    {
        foreach ($event[$type] as $category) {
            $option = $this->getOptionByValue($category);
            $event['categories'][$type.'s'][] = $option['default_value'];
        }
    }

    public function getOptionById($id)
    {
        if (!is_numeric($id)) {
            return false;
        }
        if (!array_key_exists($id, $this->_detartmentsAges)) {
            return false;
        }
        return $this->_detartmentsAges[$id];
    }

    public function getOptionByValue($value)
    {
        if (empty($value)) {
            return false;
        }
        if (!array_key_exists($value, $this->_idMap)) {
            return false;
        }
        return $this->_detartmentsAges[ $this->_idMap[$value] ];
    }

    protected function _getAttributeOptions($name)
    {
        $options = Mage::getResourceModel('eav/entity_attribute_option_collection')
            ->setStoreFilter(
                Mage::app()->getStore()->getId()
            )
            ->setAttributeFilter(
                $this->_getAttributeIdByName($name)
            )
            ->setPositionOrder('asc')
            ->load()
            ->getData();
            
        $this->_sortAttributeOptinsById($options);
    }

    protected function _getAttributeIdByName(&$name)
    {
        return Mage::getResourceModel('eav/entity_attribute_collection')
            ->setCodeFilter($name)
            ->setEntityTypeFilter(4)
            ->getFirstItem()
            ->getId();
    }

    protected function _sortAttributeOptinsById(array $options = array())
    {
        foreach ($options as $option) {
            $this->_detartmentsAges[$option['option_id']] = $option;
            $this->_idMap[$option['value']] = $option['option_id'];
        }
    }

}
