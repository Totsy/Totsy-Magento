<?php

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Columns
    extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('urapidflow/columns.phtml');
    }

    public function getColumnsFields()
    {
        $groups = array();

        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_product');
        $attrs = $entityType->getAttributeCollection();
        $fields = array();
        $hidden = array();
        $removeFields = array('has_options', 'required_options', 'category_ids', 'minimal_price');
        if ($this->getProfile()->getProfileType()=='import') {
            $removeFields = array_merge($removeFields, array('created_at', 'updated_at'));
        }
        foreach ($attrs as $k=>$a) {
            $attr = $a->toArray();
            if ($attr['frontend_input']=='gallery' || in_array($attr['attribute_code'], $removeFields)) {
                continue;
            }
            if (empty($attr['frontend_label'])) {
                $attr['frontend_label'] = $attr['attribute_code'];
            }
            if (in_array($attr['frontend_input'], array('select', 'multiselect'))) {
                try {
                    if (!$a->getSource()) {
                        continue;
                    }
                    $opts = $a->getSource()->getAllOptions();
                    foreach ($opts as $o) {
                        if (is_array($o['value'])) {
                            foreach ($o['value'] as $o1) {
                                $attr['options'][$o['label']][$o1['value']] = $o1['label'];
                            }
                        } elseif (is_scalar($o['value'])) {
                            $attr['options'][$o['value']] = $o['label'];
                        }
                    }
                } catch (Exception $e) {
                    // can be all kinds of custom source models, just ignore
                }
            }
            if (!empty($attr['is_visible'])) {
                $fields[$attr['attribute_code']] = $attr;
            } else {
                unset($attr['is_required']);
                $hidden[$attr['attribute_code']] = $attr;
            }
        }
        $groups['attributes'] = array('label'=>$this->__('Product Attributes'), 'fields'=>$fields);
        $groups['hidden'] = array('label'=>$this->__('Hidden Attributes'), 'fields'=>$hidden);
        if ($this->getProfile()->getProfileType()=='export') {
            $groups['price'] = array('label'=>$this->__('Price'), 'fields'=>array(
                'price.final' => array(
                    'attribute_code' =>'price.final',
                    'frontend_input' => 'text',
                    'frontend_label' => $this->__('Final Price'),
                    'backend_type'   =>'decimal'
                ),
                'price.minimal' => array(
                    'attribute_code' =>'price.minimal',
                    'frontend_input' => 'text',
                    'frontend_label' => $this->__('Minimal Price'),
                    'backend_type'   =>'decimal'
                )
            ));
            if (Mage::helper('urapidflow')->hasMageFeature('indexer_1.4')) {
                $groups['price']['fields']['price.maximum'] = array(
                    'attribute_code' =>'price.maximum',
                    'frontend_input' => 'text',
                    'frontend_label' => $this->__('Maximum Price'),
                    'backend_type'   =>'decimal'
                );
            }
        }

        $attrs = Mage::getResourceModel('urapidflow/catalog_product')->fetchSystemAttributes();
        $gr = array(
            'product'=>$this->__('System Attributes'),
            'stock'=>$this->__('Inventory Stock'),
            'category'=>$this->__('Category'),
        );
        if ($this->getProfile()->getProfileType()=='import') {
            $removeFields = array_merge($removeFields, array('product.entity_id', 'price.final', 'price.minimal', 'price.maximum'));
        }
        foreach ($attrs as $f=>$a) {
            if (in_array($f, $removeFields)) continue;
            $fa = explode('.', $f, 2);
            if (empty($fa[1])) {
                if (strpos($f, '_type')!==false) {
                    if (empty($a['frontend_label'])) {
                        $a['frontend_label'] = $f;
                    }
                    $groups['hidden']['fields'][$f] = $a;
                }
                continue;
            }
            if (empty($groups[$fa[0]])) {
                $groups[$fa[0]] = array('label'=>$gr[$fa[0]], 'fields'=>array());
            }
            $a['attribute_code'] = $f;
            $groups[$fa[0]]['fields'][$f] = $a;
        }
        $groups['const'] = array('label'=>$this->__('Constant'), 'fields'=>array(
            'const.value' => array(
                'attribute_code' =>'const.value',
                'frontend_input' => 'textarea',
                'frontend_label' => $this->getProfile()->getProfileType()=='export' ? $this->__('Constant Value') : $this->__('Ignore Column'),
            ),
            'const.function' => array(
                'attribute_code' =>'const.function',
                'frontend_input' => 'text',
                'frontend_label' => $this->getProfile()->getProfileType()=='export' ? $this->__('Custom Function') : $this->__('Ignore Column'),
            ),
        ));

        $fields = array('attribute_code'=>1, 'backend_type'=>1, 'frontend_label'=>1, 'frontend_input'=>1, 'options'=>1, 'is_required'=>1);

        foreach ($groups as $i=>&$g) {
            uasort($g['fields'], array($this, 'sortFields'));
            foreach ($g['fields'] as $j=>&$a) {
                foreach ($a as $f=>$v) {
                    if (empty($fields[$f])) {
                        unset($a[$f]);
                    }
                }
                if (!empty($a['options'])) {
                    $options = $a['frontend_input']=='multiselect' ? array() : array(''=>'');
                    foreach ($a['options'] as $k=>$v) {
                        if ($k==='') {
                            continue;
                        }
                        if (is_array($v)) {
                            foreach ($v as $k1=>$v1) {
                                $options[$k][$k1.' '] = $v1;
                            }
                        } else {
                            $options[$k.' '] = $v;
                        }
                    }
                    $a['options'] = $options;
                }
            }
            unset($a);
        }
        unset($g);

        return $groups;
    }

    public function sortFields($a, $b)
    {
        return $a['frontend_label']<$b['frontend_label'] ? -1 : ($a['frontend_label']>$b['frontend_label'] ? 1 : 0);
    }

    public function getColumns()
    {
        return (array)$this->getProfile()->getColumns();
    }
}