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

class Harapartners_PromotionFactory_Adminhtml_VirtualproductcouponController extends Mage_Adminhtml_Controller_Action {
    
	protected $_session;
	
    public function indexAction() {
        $this->loadLayout ()
        	 ->_setActiveMenu( 'promotionfactory/virtualproductcoupon' )
        	 ->_addContent( $this->getLayout ()->createBlock ( 'promotionfactory/adminhtml_virtualproductcoupon_index' ) )
        	 ->renderLayout();
    }

    public function manageCouponByProductAction() {
		
		$productId = Mage::app()->getRequest()->getParam('product_id');
		$product = Mage::getModel('catalog/product')->load($productId);
		
		if(!$product->getId() || !$product->isVirtual()){
			Mage::getSingleton( "adminhtml/session" )->addError( 'Manage coupon is only valid for existing virtual product!' );
			$this->_redirect ( '*/*/*' );
			return;
			
		}
		
		Mage::register('manage_coupon_virtual_product', $product);
		
        $this->loadLayout()
        	 ->_setActiveMenu( 'catalog/product' )
        	 ->_addContent( $this->getLayout()->createBlock( 'promotionfactory/adminhtml_virtualproductcoupon_managecoupon' ) )
        	 ->renderLayout();
    }
    
//	public function newAction() {
//        $this->_forward ( 'edit' );
//    }
//    
//	public function editAction() {
//        $id = $this->getRequest ()->getParam ( 'id' );
//        $model = Mage::getModel ( 'promotionfactory/virtualproductcoupon' )->load ( $id );
//        
//        if ($model->getId () || $id == 0) {
//            $data = Mage::getSingleton ( 'adminhtml/session' )->getData ( "virtual_product_coupon_data", true );
//            if (! empty ( $data )) {
//                $model->setData ( $data );
//            }
//            
//            Mage::register ( 'virtual_product_coupon_data', $model );
//            
//            $this->loadLayout ()->_setActiveMenu ( 'promotionfactory/edit' );
//            
//            $this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
//            $this->renderLayout ();
//        } else {
//            Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotionfactory' )->__ ( 'Virtual Product Coupon does not exist' ) );
//            $this->_redirect ( '*/*/' );
//        }
//    }
    
	public function generateCouponAction(){
        $data = $this->getRequest()->getPost();
        $total = $data['total'];
        $productId = $data['product_id']; //revisit to make sure it is product id
        Mage::getModel('promotionfactory/couponcreator')->createVirtualProductCoupons($total, $productId);
        $this->getResponse()->setRedirect( $this->getUrl('*/*/manageCouponByProduct', array('product_id' => $productId) ) );
    }
    
	public function importCouponAction() {
        if ($data = $this->getRequest ()->getPost ()) {
        	if( ! isset( $data[ 'product_id' ] ) ) {
        		Mage::getSingleton( "adminhtml/session" )->addError( "Unable to proceed since product id is missing." );
        		$this->getResponse()->setRedirect( '*/*/manageCouponByProduct' );
        		return;
        	}
            if (isset ( $_FILES ['import'] ['name'] ) and (file_exists ( $_FILES ['import'] ['tmp_name'] ))) {
                try {
                    $uploader = new Varien_File_Uploader ( 'import' );
                    $uploader->setAllowRenameFiles ( false );
                    $uploader->setFilesDispersion ( false );                    
                    $path = Mage::getBaseDir ( 'var' ) . DS;                    
                    $uploader->save ( $path, $_FILES ['import'] ['name'] );
                    $fileName = $path . $_FILES ['import'] ['name'];
                    
                    if (($handle = fopen ( $fileName, "r" )) !== FALSE) {
                    	$row = 0;
                    	$errorCount = 0;
                        while ( ($fileData = fgetcsv ( $handle, 10000, ',', '"' )) !== FALSE ) {
                            $row ++;
                            if( $row == 1 ) {
                            	$header = array_flip( $fileData );
                            	continue;
                            }
                            $coupon = Mage::getModel ( 'promotionfactory/virtualproductcoupon' );
                            $coupon->setData ('code', $fileData[$header["code"]]);
                            $coupon->setData('status',isset($fileData[$header['status']]) ? $fileData[$header['status']] : Harapartners_Promotionfactory_Model_Virtualproductcoupon::COUPON_STATUS_AVAILABLE);
                            $coupon->setData('product_id', $data['product_id']);
                            try {
                            	$coupon->save();
                            } catch( Exception $e ) {
                            	Mage::getSingleton( "adminhtml/session" )->addError( $e->getMessage() );
                            	if( $errorCount++ > 5 ) {
                            		break;
                            	}                                                                                   
                            }
                        }
                    }                    
                	unlink($path . $_FILES ['import'] ['name']);
                } catch ( Exception $e ) {
                
                }
            }
        }
        $this->getResponse()->setRedirect( $this->getUrl('*/*/manageCouponByProduct', array('product_id' => $data['product_id']) ) );
    }
    
    public function massDeleteCouponsAction() {
    	$couponIds = (array)$this->getRequest()->getParam('coupon_ids');
  		$storeId = (int)$this->getRequest()->getParam('store', 0);
  		$productId = (int)$this->getRequest()->getParam('product_id', 0);
  		if(!empty($couponIds)){
  			try{
  				Mage::getModel('promotionfactory/virtualproductcoupon')->getCollection()
  								->addFieldToFilter('entity_id', array('in'=>$couponIds))
								->walk('delete');
  				$this->_getSession()->addSuccess($this->__('Coupons have been deleted successfully.'));
  			}catch (Mage_Core_Model_Exception $e) {
            	$this->_getSession()->addError($e->getMessage());
	        } catch (Mage_Core_Exception $e) {
	            $this->_getSession()->addError($e->getMessage());
	        } catch (Exception $e) {
	            $this->_getSession()->addException($e, $this->__('An error occurred while deleting these coupons.'));
	        }
  		}else{
  			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('promotionfactory')->__('Please select coupon(s) you want to delete.'));
  		}
  		$this->_redirect('*/*/manageCouponByProduct', array('store'=> $storeId, 'product_id'=>$productId));
    }
    
    protected function _getSession() {
    	if( ! $this->_session ) {
    		$this->_session = Mage::getSingleton( "adminhtml/session" );
    	}
    	return $this->_session;
    }
    
}