<?php

class Totsy_Sailthru_Adminhtml_FeedconfigController extends Mage_Adminhtml_Controller_Action{
    
    public function indexAction(){    
        $this->loadLayout()
            ->_setActiveMenu('sailthru/feedconfig')
            ->_addContent($this->getLayout()->createBlock('sailthru/adminhtml_feedconfig_index'))
            ->renderLayout();
    }  
}

?>