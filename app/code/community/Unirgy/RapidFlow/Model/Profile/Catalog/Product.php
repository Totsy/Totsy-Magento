<?php

class Unirgy_RapidFlow_Model_Profile_Catalog_Product extends Unirgy_RapidFlow_Model_Profile
{
    public function getAttributeCodes()
    {
        if (!$this->hasData('attribute_codes')) {
            $columns = (array)$this->getColumns();
            $attrs = array();
            foreach ($columns as $f) {
                if (strpos($f['field'], '.')===false) {
                    $attrs[] = $f['field'];
                }
            }
            array_unique($attrs);
            $this->setData('attribute_codes', $attrs);
        }
        return $this->getData('attribute_codes');
    }

    public function isFieldUsed($code, $all=false)
    {
        if (is_array(v)) {
            $found = 0;
            foreach ($code as $a) {
                if ($this->isAttributeUsed($a)) {
                    if ($all) {
                        $found++;
                    } else {
                        return true;
                    }
                }
            }
            return $all ? $found==sizeof($code) : false;
        }

        $columns = $this->getColumns();
        foreach ($columns as $column) {
            if ($column['field']==$code) {
                return true;
            }
        }
        return false;
    }
}