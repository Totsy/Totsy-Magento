<?php

class Harapartners_SpeedTax_Adminhtml_TaxskuController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){    

		$data = Mage::getSingleton('adminhtml/session')->getSpeedtaxTaxskuFormData();
        
        if($data){
            Mage::unregister('speedtax_taxsku_form_data');
            Mage::register('speedtax_taxsku_form_data', $data);
        }
        
        $this->loadLayout()->_setActiveMenu('speedtax/taxsku');
        $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
        $this->_addContent($this->getLayout()->createBlock('speedtax/adminhtml_taxsku_edit'));
        $this->renderLayout();
    }

	public function generateAction(){
        $data = new Varien_Object($this->getRequest()->getPost());

        //save data in session in case of failure
        Mage::getSingleton('adminhtml/session')->setSpeedtaxTaxskuFormData($data);
        if(!$data){
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('speedtax')->__('Nothing to generate.'));
            $this->_redirect('*/*/');
            return;
        }

        try {
        	if (!$data->hasStartAt()){
        		throw new Exception("Field Start Date is required");
        	}
        	if (!$data->hasStartAt()){
        		throw new Exception("Field End Date is required");
        	}

            $start = $data->getStartAt();
            $end   = $data->getEndAt();
            
            $ex_events = $this->_getExs($data, 'ex_events');
        	$ex_products = $this->_getExs($data, 'ex_products');

            $this->getResponse()->setHeader('Content-Disposition','attachment; filename="mapTaxCategoryToSku.csv"');
			$this->getResponse()->setHeader('Content-Type','text/csv');

			Mage::getModel('speedtax/map')->generateMappingReport(
				compact('start','end','ex_events','ex_products')
			);

            //clear form data from session
            Mage::getSingleton('adminhtml/session')->setSpeedtaxTaxskuFormData(null); 

            return;

        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setSpeedtaxTaxskuFormData($data);
            $this->_redirect('*/*/');
            return;
        }

    }

    protected function _getExs($data,$key){
    	if (!is_object($data)
    		|| $data->has($key)
    	) {
    		return null;
    	}

    	$rex = $data->getData($key);
    	$rex = preg_replace("/[\r\n]+/",',',$rex);
    	$rex = explode(',', $rex);

    	$ex = array();
    	foreach($rex as $r){
    		$r = intval($r);
    		if (!empty($r)){
    			$ex[] = $r;
    		}
    	}
    	if (empty($ex)){
    		return null;
    	}
    	return $ex;
    }

}

?>