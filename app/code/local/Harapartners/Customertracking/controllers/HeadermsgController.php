<?php
class Harapartners_Customertracking_HeadermsgController extends Mage_Core_Controller_Front_Action
{
    public function ajaxAction(){
        $this->loadLayout()->renderLayout();
    }
}