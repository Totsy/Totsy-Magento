<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * WebShopApps
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    WebShopApps
 * @package     WebShopApps WsaLogger
 * @copyright   Copyright (c) 2011 Zowta Ltd (http://www.webshopapps.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
**/


class Webshopapps_Wsalogger_Adminhtml_Block_System_Config_Form_Fieldset_Modules_DisableLogging
    extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    
    protected $_dummyElement;
    protected $_fieldRenderer;
    protected $_values;

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->_getHeaderHtml($element);
        
       // $html.= $this->getUrl('*/system_wsalogger');

        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());

        sort($modules);

        foreach ($modules as $moduleName) {
            if ($moduleName==='Mage_Adminhtml' ||$moduleName==='Webshopapps_Wsacommon' || $moduleName==='Webshopapps_Wsalogger'
            	|| stripos($moduleName,'Mage_') !== false) {
                continue;
            }
            if (!Mage::getStoreConfig('wsalogmenu/wsalog/view_all_extns') && stripos($moduleName,'webshopapps_') === false) {
            	continue;
            }
            $html.= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }

    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new Varien_Object(array('show_in_default'=>1, 'show_in_website'=>1));
        }
        return $this->_dummyElement;
    }

    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_fieldRenderer;
    }

    protected function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = array(
                array('label'=>Mage::helper('adminhtml')->__('Logging Disabled'), 'value'=>0),
                array('label'=>Mage::helper('adminhtml')->__('Logging Enabled'), 'value'=>1),
            );
        }
        return $this->_values;
    }

    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'wsalogmenu/modules_disable_logger/'.$moduleName; //TODO: move as property of form
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = 0;
            $inherit = true;
        }

        $e = $this->_getDummyElement();

        $field = $fieldset->addField($moduleName, 'select',
            array(
                'name'          => 'groups[modules_disable_logger][fields]['.$moduleName.'][value]',
                'label'         => $moduleName,
                'value'         => $data,
                'values'        => $this->_getValues(),
                'inherit'       => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($e),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($e),
            ))->setRenderer($this->_getFieldRenderer());

        return $field->toHtml();
    }
}
