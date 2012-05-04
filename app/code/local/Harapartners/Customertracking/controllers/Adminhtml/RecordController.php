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

class Harapartners_Customertracking_Adminhtml_RecordController extends Mage_Adminhtml_Controller_Action{
    
    public function indexAction(){    
        $this->loadLayout()
            ->_setActiveMenu('marketing/customertracking')
            ->_addContent($this->getLayout()->createBlock('customertracking/adminhtml_record_index'))
            ->renderLayout();
    }   

    public function exportCsvAction(){
        $fileName   = 'customertracking record.csv';
        $content    = $this->getLayout()->createBlock('customertracking/adminhtml_record_index_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }
    public function exportXmlAction(){
        $fileName   = 'customertracking record.xml';
        $content    = $this->getLayout()->createBlock('customertracking/adminhtml_record_index_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
}   
