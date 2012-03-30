<?php
class Harapartners_PromotionFactory_Adminhtml_GroupcouponController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction() {
		$this->loadLayout ()->_setActiveMenu ( 'promotionfactory/groupcoupon' )->_addContent ( $this->getLayout ()->createBlock ( 'promotionfactory/adminhtml_groupcoupon_index' ) )->renderLayout ();
	}
	
	public function newAction() {
		$this->_forward ( 'edit' );
	}
	
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'salesrule/rule' )->load ( $id );
		
		if ($model->getId () || $id == 0) {
			$data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
			if (! empty ( $data )) {
				$model->setData ( $data );
			}
			
			Mage::register ( 'emailcoupon_data', $model );
			
			$this->loadLayout ()->_setActiveMenu ( 'promotionfactory/edit' );
			
			$this->_addBreadcrumb ( Mage::helper ( 'promotionfactory' )->__ ( 'Manage Coupon With Email List' ), Mage::helper ( 'adminhtml' )->__ ( 'Manage Coupon With Email List' ) );
			$this->_addBreadcrumb ( Mage::helper ( 'promotionfactory' )->__ ( 'Coupon With Email List' ), Mage::helper ( 'adminhtml' )->__ ( 'Coupon With Email List' ) );
			
			$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
			$this->renderLayout ();
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotionfactory' )->__ ( 'Coupon does not exist' ) );
			$this->_redirect ( '*/*/' );
		}
	}

	
	public function saveAction() {
		
		if ($data = $this->getRequest ()->getPost ()) {
			// read the file and get the email address
			if (isset ( $_FILES ['import'] ['name'] ) and (file_exists ( $_FILES ['import'] ['tmp_name'] ))) {
				try {
					$uploader = new Varien_File_Uploader ( 'import' );
					$uploader->setAllowRenameFiles ( false );
					$uploader->setFilesDispersion ( false );					
					$path = Mage::getBaseDir ( 'var' ) . DS;					
					$uploader->save ( $path, $_FILES ['import'] ['name'] );
					
					$fileName = $path . $_FILES ['import'] ['name'];
					
					$ruleId = (int)$data['id'];
					// exsiting only
					if (Mage::getModel('promotionfactory/groupcoupon')->ruleIdExist($ruleId)){
						 Mage::getModel('promotionfactory/groupcoupon')->deleteByRuleId($ruleId);
					}
					
					if (($handle = fopen ( $fileName, "r" )) !== FALSE) {
						while ( ($fileData = fgetcsv ( $handle, 10000, ',', '"' )) !== FALSE ) {
							$row ++;
							if ($row > 1) { //skip the header line 
								$model = Mage::getModel ( 'promotionfactory/groupcoupon' );
								$coupon = Mage::getModel('salesrule/rule')->load($data['id']);
  								$code = $coupon->getCouponCode();	
									$model->setData ( 'pseudo_code', $fileData[0]);
									$model->setData('code',$code);
									$model->setData('rule_id',(int)$data['id']);
									$model->save ();																					
							}
						}
					}					
				//--delete the file
				unlink($path . $_FILES ['import'] ['name']);
				} catch ( Exception $e ) {
				
				}
			}
		}
		$this->_redirect ( '*/*/' );
	}
	
	public function activeAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'promotionfactory/emailcoupon' )->load ( $id );
		
		if ($model->getId ()) {
			$model->setIsActive ( 1 );
			try {
				$model->save ();
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotionfactory' )->__ ( 'Unable to active Coupon, please try again' ) );
			}
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotionfactory' )->__ ( 'Unknown Coupon, save failed' ) );
		}
		$this->_redirect ( '*/*/' );
	}
	
	public function deactiveAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'promotionfactory/buyxrule' )->load ( $id );
		
		if ($model->getId ()) {
			$model->setIsActive ( 0 );
			try {
				$model->save ();
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotionfactory' )->__ ( 'Unable to deactive Coupon, please try again' ) );
			}
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotionfactory' )->__ ( 'Unknown Coupon, deactivation failed' ) );
		}
		$this->_redirect ( '*/*/' );
	}
	
	public function deleteAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'promotionfactory/buyxrule' )->load ( $id );
		
		if ($model->getId ()) {
			try {
				$model->delete ();
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotion' )->__ ( 'Unable to delete Coupon, please try again' ) );
			}
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotion' )->__ ( 'Unknown Buy X Rule, deletion failed' ) );
		}
		$this->_redirect ( '*/*/' );
	}
	
	public function createAction(){
		//1.get total amount need to create
		$data = $this->getRequest()->getPost();
		$total = $data['total'];
		$id = $data['id'];
		$ruleId = (int)$data['id'];
		if (Mage::getModel('promotionfactory/groupcoupon')->ruleIdExist($ruleId)){
						 Mage::getModel('promotionfactory/groupcoupon')->deleteByRuleId($ruleId);
					}
		//2 .post the total amount to model file,createCouponCodes($total)
		Mage::getModel('promotionfactory/couponcreator')->createCoupons($total,$id);
		//2a. delete all current record.
		//2b create a new group of the codes 
		
		//3.refresh this page.
		$this->_redirect ( '*/*/' );
	}

}