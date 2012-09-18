<?php

class Unirgy_RapidFlow_Adminhtml_DocController extends Mage_Adminhtml_Controller_Action
{
    /*
    protected function _validateSecretKey()
    {
        return true;
    }

    public function indexAction()
    {
        $nl = "\n";
        echo "<pre>";
        $rowTypes = Mage::getSingleton('urapidflow/config')->getNode('row_types');
        echo "==== Allowed actions ====\n";
        echo "^ Row Type ^ Description ^ Create ^ Update ^ Delete ^ Rename ^\n";
        foreach ($rowTypes->children() as $k1=>$rowType) {
            if (in_array($k1, array('CC', 'CP'))) {
                continue;
            }
            $rename = in_array($k1, array('EA', 'EAS', 'EAO', 'CPCO', 'CPCOS', 'CPBO', 'CPSA')) ? 'X' : '';
            echo "^ $k1 | X | X | X | $rename | {$rowType->title} |\n";
        }
        foreach ($rowTypes->children() as $k1=>$rowType) {
            if (in_array($k1, array('CC', 'CP'))) {
                continue;
            }
            echo "==== $k1: {$rowType->title} ====$nl";
            if (!empty($rowType->columns)) {
                echo "=== Columns ===$nl^ # ^ Column ^ Key? ^ Required? ^ Comments ^$nl";
                foreach ($rowType->columns->children() as $k2=>$col) {
                    $key = !empty($col->key) ? 'X' : '';
                    echo "| {$col->col} | $k2 | {$key} | {$key} | |$nl";
                }
            }
        }
        echo "</pre>"; #print_r($rowTypes);
    }
    */
}