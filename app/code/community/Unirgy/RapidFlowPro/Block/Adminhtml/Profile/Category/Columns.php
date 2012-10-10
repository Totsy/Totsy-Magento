<?php

class Unirgy_RapidFlowPro_Block_Adminhtml_Profile_Category_Columns
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

        $entityType = Mage::getSingleton('eav/config')->getEntityType('catalog_category');
        $attrs = $entityType->getAttributeCollection();
        $fields = array();
        $hidden = array();
        $removeFields = array('children', 'children_count', 'all_children', 'path_in_store', 'path', 'parent_id', 'level');
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
                #unset($attr['is_required']);
                $hidden[$attr['attribute_code']] = $attr;
            }
        }
        $hidden['url_path']['is_required'] = 1;
        $groups['attributes'] = array('label'=>$this->__('Category Attributes'), 'fields'=>$fields);
        $groups['hidden'] = array('label'=>$this->__('Hidden Attributes'), 'fields'=>$hidden);

        $attrs = Mage::getResourceModel('urapidflowpro/category')->fetchSystemAttributes();
        $gr = array('category'=>'System Attributes');
        if ($this->getProfile()->getProfileType()=='import') {
            $removeFields = array_merge($removeFields, array('category.entity_id'));
        }
        foreach ($attrs as $f=>$a) {
            if (in_array($f, $removeFields)) continue;
            $fa = explode('.', $f, 2);
            if (empty($fa[1])) {
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