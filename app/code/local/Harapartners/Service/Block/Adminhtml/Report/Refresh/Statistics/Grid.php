<?php

class Harapartners_Service_Block_Adminhtml_Report_Refresh_Statistics_Grid extends Mage_Adminhtml_Block_Report_Refresh_Statistics_Grid
{
    
    protected function _prepareMassaction()
    {
        $this->getMassactionBlock()->addItem('refresh_sevendays', array(
            'label'    => Mage::helper('reports')->__('Refresh Last 7 days Statistics'),
            'url'      => $this->getUrl('*/*/refreshSevendays'),
            'confirm'  => Mage::helper('reports')->__('Are you sure you want to refresh last 7 days statistics? '),
        ));

        $this->getMassactionBlock()->addItem('refresh_lastmonth', array(
            'label'    => Mage::helper('reports')->__('Refresh Statistics for the Last Month'),
            'url'      => $this->getUrl('*/*/refreshLastmonth'),
            'confirm'  => Mage::helper('reports')->__('Are you sure?')
        ));
		
        $this->getMassactionBlock()->addItem('refresh_fromdate', array(
            'label'    => Mage::helper('reports')->__('Refresh Statistics from March 26th'),
            'url'      => $this->getUrl('*/*/refreshfromdate'),
            'confirm'  => Mage::helper('reports')->__('Are you sure?')
        ));

        return parent::_prepareMassaction();;
    }
}
