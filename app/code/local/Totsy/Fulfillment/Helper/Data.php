<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lhansen
 * Date: 4/10/13
 * Time: 5:53 PM
 * To change this template use File | Settings | File Templates.
 */

class Totsy_Fulfillment_Helper_Data extends Mage_Core_Helper_Abstract {

    /**
     * @param $type <string> name of xml root
     * @param $record <array> xml data
     * @return xml
     */
    protected function createXMLDoc($type, $record) {
        $xml = new SimpleXMLElement("<?xml version='1.0' encoding='utf-8'?><$type></$type>");

        $initialKey = array_keys($record);
        $initialKey = $initialKey[0];

        $addChildRecursive = function($key, $value, &$parent) use (&$object, &$addChildRecursive){
            if(is_array($value)) {
                $child = $parent;
                if($parent->getName() != $key && !isset($value[0])){
                    $child = $parent->addChild($key);
                }
                foreach($value as $sub_key => $sub_value){
                    if(is_numeric($sub_key)){
                        $sub_key = $key;
                        $addChildRecursive($sub_key, $sub_value, $parent);
                    } else{
                        $addChildRecursive($sub_key, $sub_value, $child);
                    }
                }
            } else {
                $parent->addChild($key, $object->sanitizeString($value));
            }
        };

        foreach($record[$initialKey] as $data) {
            $parent = $xml->addChild($initialKey);
            $object = $this;
            foreach($data as $key => $value) {
                if($key == '_attribute') {
                    foreach($value as $sub_key => $sub_value) {
                        $parent->addAttribute($sub_key, $sub_value);
                    }
                    continue;
                }
                $addChildRecursive($key, $value, $parent);
            }
        }
        return $xml;
    }

    public function sanitizeString($string) {
        $value = preg_replace('/&/','and',$string);
        $value = preg_replace('/[^a-zA-Z0-9\s-_]/',"",$string);
        return $value;
    }
}
