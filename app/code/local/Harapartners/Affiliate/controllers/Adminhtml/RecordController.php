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

class Harapartners_Affiliate_Adminhtml_RecordController extends Mage_Adminhtml_Controller_Action{
	
	public function indexAction(){	
		$this->loadLayout()
			->_setActiveMenu('harapartners/affiliate/record')
			->_addContent($this->getLayout()->createBlock('affiliate/adminhtml_record_index'))
			->renderLayout();
    }   
    
	public function newAction(){
		$this->_forward('edit');
    } 
    
    public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('affiliate/record')->load($id);
		
		if($id == 0){
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		}elseif(!!$model->getId()){
			$data = $model->getData();
		//prepare data
			if(isset($data['tracking_code']) && !!$data['tracking_code']){
				$trackingCode = json_decode($data['tracking_code'],true);
				if(isset($trackingCode['code']) && !!$trackingCode['code']){
					$data['invitation_code'] = $trackingCode['code'];
				}
				if(isset($trackingCode['pixels']) && !!$trackingCode['pixels']){
					foreach ($trackingCode['pixels'] as $index=>$pixels) {
						$data['pixels'.$index.'enable'] = $trackingCode['pixels'][$index]['enable'];
						$data['pixels'.$index.'page'] = $trackingCode['pixels'][$index]['page'];
						$data['pixels'.$index.'pixel'] = $trackingCode['pixels'][$index]['pixel'];
					}
					Mage::unregister('affiliatePixelsCount');
			       	Mage::register('affiliatePixelsCount', count($trackingCode['pixels']));					
				}				
			}
		//---end						
		}else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('no record exists'));
			$this->_redirect('*/*/');
			return;
		}
		
		Mage::getSingleton('adminhtml/session')->setAffiliateFormData($data);
		Mage::register('affiliate_form_data', $data);
		$this->loadLayout()->_setActiveMenu('harapartners/affiliate');
		$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
		$this->_addContent($this->getLayout()->createBlock('affiliate/adminhtml_record_edit'));
		$this->renderLayout();
		
    }
	public function saveAction(){
		$datetime = date('Y-m-d H:i:s');
		if ($data = $this->getRequest()->getPost()) {
			$id = $this->getRequest()->getParam('id');
			if($id>0){
				$model = Mage::getModel('affiliate/record')->load($id);
			}else{
				$model = Mage::getModel('affiliate/record');
			}	
			
			//split tracking code to code and tracking pixels					
			$trackingCode = json_decode($model->getTrackingCode(),true);
			if(!isset($trackingCode)){
				$trackingCode = array();
			}			
			if(isset($data['invitation_code']) && !!$data['invitation_code']){
				$trackingCode['code'] = $data['invitation_code'];
				unset($data['invitation_code']);
			}
			if(isset($trackingCode['pixels'])){
				$pixelCount = count($trackingCode['pixels']);
			}else{
				$trackingCode['pixels'] = array();
				$pixelCount=0;
			}
			$i=0;
			while ($i<$pixelCount){	
				if(!isset($trackingCode['pixels'][$i]) ){
					$trackingCode['pixels'][$i]=array();
				}					
				if(isset($data['pixels'.$i.'page']) && !!$data['pixels'.$i.'page']){
					$trackingCode['pixels'][$i]['page'] = $data['pixels'.$i.'page'];
					unset($data['pixels'.$i.'page']);
				}	
				if(isset($data['pixels'.$i.'pixel']) && !!$data['pixels'.$i.'pixel']){
					$trackingCode['pixels'][$i]['pixel'] = $data['pixels'.$i.'pixel'];
					unset($data['pixels'.$i.'pixel']);
					$trackingCode['pixels'][$i]['enable'] = $data['pixels'.$i.'enable'];
					unset($data['pixels'.$i.'enable']);
				}			
				$i++;
			}
			if($i==$pixelCount && isset($data['pixels'.$i.'pixel']) && !!$data['pixels'.$i.'pixel']){
				$trackingCode['pixels'][$i]['enable'] = $data['pixels'.$i.'enable'];
				unset($data['pixels'.$i.'enable']);
				$trackingCode['pixels'][$i]=array();
				$trackingCode['pixels'][$i]['page'] = $data['pixels'.$i.'page'];
				unset($data['pixels'.$i.'page']);
				$trackingCode['pixels'][$i]['pixel'] = $data['pixels'.$i.'pixel'];
				unset($data['pixels'.$i.'pixel']);
			}
			$data['tracking_code'] = json_encode($trackingCode);
			// end
			
			if($id>0){
				$data['affiliate_id'] = $id;
			}
			$model->setData($data);					
			try {
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('affiliate')->__('Record was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('Unable to find Record to save'));
        $this->_redirect('*/*/');
    }
    
    public function deleteAction(){
		$id = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('affiliate/record')->load($id);

		if ($model->getId()) {
			try{
				$model->delete();
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('Unable to Delete, please try again'));
			}
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('affiliate')->__('Unknown record, deletion failed'));
		}
		$this->_redirect('*/*/');
    }
    
    public function exportCsvAction(){
        $fileName   = 'affiliate record.csv';
        $content    = $this->getLayout()->createBlock('affiliate/adminhtml_record_index_grid')
            	->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
    public function exportXmlAction(){
        $fileName   = 'affiliate record.xml';
        $content    = $this->getLayout()->createBlock('affiliate/adminhtml_record_index_grid')
            	->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }
    
}   
