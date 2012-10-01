<?php
class Totsy_Catalog_Model_Product_Attribute_Source_Tax extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
    	if (!$this->_options) {
			$this->_options = array(
				array(
					'label' => 'Clothing',
					'value' => '1100500',
				),
				array(
					'label' => 'Clothing (Sales Tax Holiday)',
					'value' => '1100501',
				),
				array(
					'label' => 'Fur Clothing',
					'value' => '1100508',
				),
				array(
					'label' => 'Essentail clothing priced below a state specific threshold',
					'value' => '1100510',
				),
				array(
					'label' => 'Uniforms',
					'value' => '1100515',
				),
				array(
					'label' => 'Formal Clothing',
					'value' => '1100525',
				),
				array(
					'label' => 'Costumes',
					'value' => '1100530',
				),
				array(
					'label' => 'Clothing Accessories and equipment',
					'value' => '1101000',
				),
				array(
					'label' => 'Protective Equipment',
					'value' => '1101500',
				),
				array(
					'label' => 'Sport or Recreational Equipment',
					'value' => '1102000',
				),
			);
    	}
    	return $this->_options;
    }
}