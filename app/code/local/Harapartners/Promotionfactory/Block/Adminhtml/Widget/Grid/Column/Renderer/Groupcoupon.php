<?php
class Harapartners_Promotionfactory_Block_Adminhtml_Widget_Grid_Column_Renderer_Groupcoupon extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {

    public function _getValue(Varien_Object $row){
        $resultHtml = "N/A";
        $ruleId = $row->getData($this->getColumn()->getIndex());
        if(!!$ruleId){
            if(Mage::getModel('promotionfactory/groupcoupon')->ruleIdExist($ruleId)){
                $resultHtml = "Created";
            }
        }
        return $resultHtml;
    }   

}