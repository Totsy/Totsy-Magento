<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lhansen
 * Date: 4/23/13
 * Time: 5:46 PM
 * To change this template use File | Settings | File Templates.
 */

class Totsy_Fulfillment_Block_Adminhtml_System_Config_Services extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected function _prepareToRender()
    {
        $this->addColumn('name', array(
            'label' => Mage::helper('fulfillment')->__('Name'),
            'style' => 'width:200px',
        ));
        $this->addColumn('api_url', array(
            'label' => Mage::helper('fulfillment')->__('Api Url'),
            'style' => 'width:200px',
        ));
        $this->addColumn('api_key', array(
            'label' => Mage::helper('fulfillment')->__('Api Key'),
            'style' => 'width:200px',
        ));

        $this->_addButtonLabel = Mage::helper('fulfillment')->__('Add Provider');
    }

    protected function _toHtml()
    {
        // Make sure id is set before template is rendered or else we can't know the id.
        if ( ! $this->getHtmlId()) {
            $this->setHtmlId('_' . uniqid());
        }
        $html = parent::_toHtml();

        // Scripts in the template must be evaluated so that select values can be set.
        $html .= "
        <script type='text/javascript'>
        arrayRow{$this->getHtmlId()}._add = arrayRow{$this->getHtmlId()}.add;
        arrayRow{$this->getHtmlId()}.add = function(templateData, insertAfterId) {
          this._add(templateData, insertAfterId);
          this.template.evaluate(templateData).evalScripts();
        }
        </script>
        ";
        return $html;
    }
}