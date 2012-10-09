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

class Unirgy_RapidFlow_Model_Rule_Condition_Product extends Mage_CatalogRule_Model_Rule_Condition_Product
{
    public function __construct()
    {
        parent::__construct();
        $this->setType('urapidflow/rule_condition_product');
    }

    protected function _addSpecialAttributes(array &$attributes)
    {
        parent::_addSpecialAttributes($attributes);
        $attributes['type_id'] = Mage::helper('urapidflow')->__('Product Type (system)');
    }

    public function getJsFormObject()
    {
        return 'rule_conditions_fieldset';
    }

    public function loadOperatorOptions()
    {
        parent::loadOperatorOptions();
        $byInputType = $this->getOperatorByInputType();
        $byInputType['multiselect'] = array('==', '!=', '()', '!()');
        $this->setOperatorByInputType($byInputType);
        return $this;
    }

    public function loadAttributeOptions()
    {
        $productAttributes = Mage::getResourceSingleton('catalog/product')
            ->loadAllAttributes()
            ->getAttributesByCode();

        $attributes = array();
        foreach ($productAttributes as $attribute) {
#var_dump($attribute->debug());
            if ($attribute->getFrontendLabel()!='' && ($attribute->getIsVisible() || $attribute->getIsUsedForPromoRules())) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }

        $this->_addSpecialAttributes($attributes);

        asort($attributes);
        $this->setAttributeOption($attributes);

        return $this;
    }

    public function getInputType()
    {
        if ($this->getAttribute()==='type_id') {
            return 'multiselect';
        }
        return parent::getInputType();
    }

    public function getValueElementType()
    {
        if ($this->getAttribute()==='type_id') {
            return 'multiselect';
        }
        return parent::getValueElementType();
    }

    public function getValueSelectOptions()
    {
        if ($this->getAttribute()==='type_id') {
            $arr = Mage::getSingleton('catalog/product_type')->getOptionArray();
            $options = array();
            foreach ($arr as $k=>$v) {
                $options[] = array('value'=>$k, 'label'=>$v);
            }
            return $options;
        }
        return parent::getValueSelectOptions();
    }

    /**
     * @param Unirgy_RapidFlow_Model_Mysql4_Catalog_Product_Collection $collection
     * @return bool|mixed|string
     */
    public function asSqlWhere($collection)
    {
        $a = $where = $this->getAttribute();
        $o = $this->getOperator();
        $v = $this->getValue();
        if (is_array($v)) {
            $ve = addslashes(join(',', $v));
        } else {
            $ve = addslashes($v);
        }


        if ($a=='category_ids') {
            $res = Mage::getSingleton('core/resource');
            $read = $res->getConnection('catalog_read');
            $sql = $read->quoteInto("select product_id from `{$res->getTableName('catalog_category_product')}` where category_id in (?)", explode(',',$v));
            switch ($o) {
            case '==': case '()':
                $w = "e.entity_id in ({$sql})";
                break;

            case '!=': case '!()':
                $w = "e.entity_id not in ({$sql})";
                break;

            default:
                return false;
            }
        } else {
            $attr = Mage::getSingleton('eav/config')->getAttribute('catalog_product', $a);

            if ($attr->getId() && $attr->getBackendType()=='datetime' && !is_int($ve)) {
                $ve = strtotime($ve);
            }

            // whether attribute is multivalue
            $m = $attr->getId() && ($attr->getFrontendInput() == 'multiselect');

            switch ($o) {
            case '==': case '!=':
                $wt = '{{ta}}'.($o=='==' ? '=' : '<>')."'{$ve}'";
                break;

            case '>=': case '<=': case '>': case '<':
                $wt = "{{ta}}{$o}'{$ve}'";
                break;

            case '{}': case '!{}':
                $wt = "{{ta}} ".($o=='!{}' ? 'NOT ' : '')."LIKE '%{$ve}%'";
                break;

            case '()': case '!()':
                $va = preg_split('|\s*,\s*|', $ve);
                if (!$m) {
                    $wt = "{{ta}} ".($o=='!()' ? 'NOT ' : '')."IN ('".join("','", $va)."')";
                } else {
                    $w1 = array();
                    foreach ($va as $v1) {
                        $w1[] = "find_in_set('".addslashes($v1)."', {{ta}})";
                    }
                    $wt = '('.join(') OR (', $w1).')';
                }
                break;

            default:
                return false;
            }

            if ($attr->getId() && $attr->getBackendType()!='static') {
                $collection->addAttributeToJoin($a);
                $sql = $collection->getSelect();
                $attrTable = $collection->getAttributeTableAlias($a);
                $dt = strpos($sql, "`{$attrTable}_default`")!==false;
                $dw = str_replace('{{ta}}', "{$attrTable}_default.value", $wt);
                $st = strpos($sql, "`{$attrTable}`")!==false;
                $sw = str_replace('{{ta}}', "{$attrTable}.value", $wt);
                if ($dt && $st) {
                    $w = "ifnull({$sw}, {$dw})";
                } elseif ($dt && !$st) {
                    $w = $dw;
                } else {
                    $w = $sw;
                }
            } else {
                $w = str_replace('{{ta}}', $a, $wt);
            }
        }

        return $w;
    }
}