<?php

/*
*   HOW TO CAALL THIS FROM TEMPLATE
*
*   $this->getLayout()->createBlock('promotions/block')
*
*       // for the home page just this
*       ->setBlockPageName('home')
*
*       // for the event page
*       ->setBlockPageName('events')
*       ->setBlockPageId($eventId)
*
*       // or for the product page
*       ->setBlockPageId($productId)
*       ->setBlockPageName('products')
*
*   ->toHtml();
*/

class Totsy_Promotions_Block_Block extends Mage_Core_Block_Template {
    
	public function __construct(){
		$this->setTemplate('promotions/banner/view.phtml');
	}

	public function getDataObject(){
		$id = null;

		if ($this->hasBlockPageId()){
			$id = $this->getBlockPageId();
		}

		$model = Mage::getModel('promotions/banner');
		$model->setPageName( $this->getBlockPageName() );
		$model->setPageId( $id );
		return $model;
	}
}