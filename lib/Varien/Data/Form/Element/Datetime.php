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

class Varien_Data_Form_Element_Datetime extends Varien_Data_Form_Element_Date {


    public function __construct($attributes=array()) {
        parent::__construct($attributes);
        //Important format, also compatible with JS calendar
        $this->setFormat("yyyy-MM-dd HH:mm:ss");
        $this->setTime(true);
        if(class_exists(Mage)){
        	$this->setImage(Mage::getDesign()->getSkinUrl('images/grid-cal.gif'));
        }
    }

}