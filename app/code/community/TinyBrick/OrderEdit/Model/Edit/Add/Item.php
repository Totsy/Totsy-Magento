<?php
/**
 * TinyBrick Commercial Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the TinyBrick Commercial Extension License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://store.delorumcommerce.com/license/commercial-extension
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@tinybrick.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this package to newer
 * versions in the future. 
 *
 * @category   TinyBrick
 * @package    TinyBrick_OrderEdit
 * @copyright  Copyright (c) 2010 TinyBrick Inc. LLC
 * @license    http://store.delorumcommerce.com/license/commercial-extension
 */
class TinyBrick_OrderEdit_Model_Edit_Add_Item 
{
	public function updateBox($sku, $color, $size)
	{
		$product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
		$options = array();
		return $this->getOptions($product, $color, $size);
	}
	
	public function getOptions($product, $color = "", $size = "") 
	{
		$collection = Mage::getModel('catalog/product')->getCollection()
			->addAttributeToFilter('type_id', 'simple')
			->addAttributeToFilter('sku', array('like'=>$product->getSku().'%'))
			->addAttributeToSelect('size')
			->addAttributeToSelect('color');
		$collection->getSelect()
			->join(array('si' => 'cataloginventory_stock_item'), 'e.entity_id = si.product_id AND si.qty > 0')
			->columns('qty', 'si');
		
		$array = array();
		foreach($collection as $simple) {
			$array[] = array(
				'simple_sku'    => $simple->getSku(),
				'color_id' 		=> intval($simple->getData('color')), 
				'color_value' 	=> $simple->getAttributeText('color'),
				'size_id' 		=> intval($simple->getData('size')),
				'size_value' 	=> $simple->getAttributeText('size'),
				'qty' 			=> $simple->getQty(),
			);
		}
		$arrReturn = array();
		
		$arrReturn['colors'] = $this->buildColor($array);

		if($color == "") {
			$color = $array[0]['color_id'];
		}
		$arrReturn['sizes'] = $this->buildSize($array, $color);
		
		if($size == "") {
			$size = $array[0]['size_id'];
		}
		$arrReturn['qtys'] = $this->buildQty($array, $color, $size);
		
		//create json to return
		return Zend_Json::encode($arrReturn);
	}
	
	public function buildColor($arr)
	{
		//color dropdown builder
		$colorArray = array();
		foreach($arr as $color) {
			$colorArray[$color['color_id']] = $color['color_value'];
		}

		$color = "<select name='items[color]' id='color-value'>";
		foreach($colorArray as $key => $option) {
			$color .= "<option value='" . $key . "'>" . $option . "</option>";
		}
		$color .= "</select>";
		return $color;
	}
	
	public function buildSize($arr, $productColor)
	{
		$size = "<select name='items[size]' id='size-value'>";
		foreach($arr as $option) {
			if($option['color_id'] == $productColor) {
				$size .= "<option value='" . $option['size_id'] . "-" . $option['simple_sku'] . "'>" . $option['size_value'] . "</option>";
			}
		}
		$size .= "</select>";
		return $size;
	}
	
	public function buildQty($arr, $productColor, $productSize)
	{
		$qty = "<select name='items[qty]' id='qty-value'>";
		foreach($arr as $option) {
			if($option['color_id'] == $productColor && $option['size_id']) {
				$maxqty = $option['qty'];
			}
		}
		$x = 1;
		while($x <= $maxqty) {
			$qty .= "<option value='" . $x . "'>" . $x++ . "</option>";
		}
		$qty .= "</select>";
		return $qty;
	}
}