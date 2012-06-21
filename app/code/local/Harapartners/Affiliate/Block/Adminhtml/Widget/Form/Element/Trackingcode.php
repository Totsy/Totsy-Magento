<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Affiliate_Block_Adminhtml_Widget_Form_Element_Trackingcode extends Varien_Data_Form_Element_Label {   

    public function __construct($attributes=array()) {
        parent::__construct($attributes);
        $this->setType('attachment');
        $this->setExtType('trackingcode');
    } 

    public function getElementHtml(){
        $resultHtml = '';
        try{
            $resultHtml .= '<div id="tracking_code_container">';
            $i = 0;
            $result = json_decode($this->getValue(), true);
            foreach ($result as $pageName => $trackingCode){
                $resultHtml .= $this->getRowHtml($pageName, $trackingCode, $i++);
            }
            $resultHtml .= '</div>';
            $resultHtml .= <<<TRACKING_CODE_HTML
<div><button class="scalable add" style="" onclick="addTrackingCode()" type="button"><span>Add Tracking Code</span></button></div>
<script type="text/javascript">
var codeCounter = $i;
var addTrackingCode = function (){
    jQuery("#tracking_code_container").append('{$this->getRowHtml()}');
    console.log(jQuery("#tracking_code_container").children(':last').children('textarea'));
    jQuery("#tracking_code_container").children(':last')
        .children('textarea').attr('name', 'trackingcode_' + codeCounter).end()
        .children('select').attr('name', 'trackingevent_' + codeCounter++);
}
</script>
TRACKING_CODE_HTML;

            return $resultHtml;
        }catch(Exception $e){
            return "";
        }

    }
    
    //Note need to escape single quotation mark
    public function getRowHtml($currentCode = '', $currentValue = '', $index)
    {
        $rowHtml = '<div class="tracking_code_row" style="margin-top: 10px">';
        $rowHtml .= $this->getSelectDropdown($currentCode, $index);
        $rowHtml .= $this->getTextareaInput($currentValue, $index);
        $rowHtml .= '</div>';
        return addcslashes($rowHtml, "'");
    }
    
    public function getSelectDropdown($currentPageName = '', $id = 0){
        $trackingPageCodeArray = Mage::helper('affiliate')->getFormTrackingPageCodeArray();
        $selectDropdownHtml = '<select style="vertical-align: top;" name="trackingevent_' . $id . '">';
        $selectDropdownHtml .= '<option value=""></option>';
        foreach ($trackingPageCodeArray as $pageName => $pageLabel){
            $selected = '';
            if($pageName == $currentPageName ){
                $selected = ' selected="selected"';
            }
            $selectDropdownHtml .= '<option value="'.$pageName.'"'.$selected.'>'.$pageLabel.'</option>';
        }
        $selectDropdownHtml .= '</select>';
        return $selectDropdownHtml;
    }
    
    public function getTextareaInput($trackingCode = '', $id = 0){
        return '<textarea style="width: 500px; height: 80px; margin-left: 10px" name="trackingcode_' . $id . '">' . $trackingCode . '</textarea>';
    }
    
}
