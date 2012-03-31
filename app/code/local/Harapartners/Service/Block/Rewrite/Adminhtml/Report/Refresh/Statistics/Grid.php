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
 
class Harapartners_Service_Block_Rewrite_Adminhtml_Report_Refresh_Statistics_Grid extends Mage_Adminhtml_Block_Report_Refresh_Statistics_Grid {
	
	CONST REPORT_EARLIEST_DATE = '2012-03-26';
	   
	protected function _prepareMassaction(){
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('code');
		
        $maxReportingHours = round((time() - strtotime(self::REPORT_EARLIEST_DATE))/3600) + 25;
        
        $this->getMassactionBlock()->addItem('refresh_fromdate', array(
            'label'    => Mage::helper('reports')->__('Refresh Statistics from ' . self::REPORT_EARLIEST_DATE),
            'url'      => $this->getUrl('*/*/refreshRange', array('hours' => $maxReportingHours)),
            'confirm'  => Mage::helper('reports')->__('This may take a long time. Are you sure?')
        ));
        
        $this->getMassactionBlock()->addItem('refresh_sevendays', array(
            'label'    => Mage::helper('reports')->__('Refresh Last 7 days Statistics'),
            'url'      => $this->getUrl('*/*/refreshRange', array('hours' => 7*24+1)),
            'confirm'  => Mage::helper('reports')->__('Are you sure you want to refresh the last 7 days\' statistics? '),
        ));
        
        $this->getMassactionBlock()->addItem('refresh_lastmonth', array(
            'label'    => Mage::helper('reports')->__('Refresh Statistics for the Last 30 days'),
            'url'      => $this->getUrl('*/*/refreshRange', array('hours' => 30*24+1)),
            'confirm'  => Mage::helper('reports')->__('Are you sure you want to refresh the last 30 days\' statistics?')
        ));

        $this->getMassactionBlock()->addItem('refresh_recent', array(
            'label'    => Mage::helper('reports')->__('Refresh Statistics for the Last Day'),
            'url'      => $this->getUrl('*/*/refreshRecent'),
            'confirm'  => Mage::helper('reports')->__('Are you sure?'),
            'selected' => true
        ));

        return $this;
    }
    	
}