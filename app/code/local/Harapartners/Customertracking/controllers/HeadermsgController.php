<?php
class Harapartners_Customertracking_HeadermsgController extends Mage_Core_Controller_Front_Action
{
    public function ajaxAction(){
        debug("Harapartners_Customertracking_HeadermsgController ajax");
        $this->loadLayout()->renderLayout();
    }
}
