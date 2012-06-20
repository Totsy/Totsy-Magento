<?php

class Harapartners_Affiliate_Block_Adminhtml_Widget_Form_Element_StaticList
    extends Varien_Data_Form_Element_Label
{
    public function getElementHtml()
    {
        $html = '<ul><li>';
        $html .= str_replace(',', '</li><li>', $this->getValue());
        $html .= '</li></ul>';

        return $html;
    }
}
