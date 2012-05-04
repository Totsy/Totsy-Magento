<?php
class Harapartners_Promotionfactory_Adminhtml_EmailcouponController extends Mage_Adminhtml_Controller_Action {
    
    public function indexAction() {
        $this->loadLayout ()->_setActiveMenu ( 'promotionfactory/emailcoupon' )
                ->_addContent ( $this->getLayout ()->createBlock ( 'promotionfactory/adminhtml_emailcoupon_index' ) )
                ->renderLayout ();
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
            
            //$this->_addContent ( $this->getLayout ()->createBlock ( 'promotionfactory/adminhtml_emailcoupon_edit' ) );
            
            $this->renderLayout ();
        } else {
            Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'promotionfactory' )->__ ( 'Coupon does not exist' ) );
            $this->_redirect ( '*/*/' );
        }
    }
    
    public function exportAction(){
        $fileName = 'coupon_email_list.csv';
        $content = $this->getLayout()
                ->createBlock('promotionfactory/adminhtml_emailcoupon_export_grid')
                ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
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
                    
                    //delete the exsiting file 1st
                    $ruleId = (int)$data['id'];
                    // exsiting only
                    if (Mage::getModel('promotionfactory/emailcoupon')->ruleIdExist($ruleId)){
                        Mage::getModel('promotionfactory/emailcoupon')->deleteByRuleId($ruleId);
                    }
                    
                    if (($handle = fopen ( $fileName, "r" )) !== FALSE) {
                        while ( ($fileData = fgetcsv ( $handle, 10000, ',', '"' )) !== FALSE ) {
                            $row ++;
                            if ($row > 1) { //skip the header line 
                                $model = Mage::getModel ( 'promotionfactory/groupcoupon' );
                                $coupon = Mage::getModel('salesrule/rule')->load($data['id']);
                                $couponCode = $coupon->getCouponCode();
                                $model = Mage::getModel('promotionfactory/emailcoupon');    
                                $model->setData ( 'email', $fileData[0]);
                                $model->setData('name', $data[1]);
                                $model->setData('code',$couponCode);
                                $model->setData('rule_id', (int)$data['id']);
                                $model->save ();                            
                            }
                        }
                    }
                    
                //--delete the file
                //    unlink($path . $_FILES ['import'] ['name']);
                } catch ( Exception $e ) {
                
                }
            }

        }        
        $this->_redirect ( '*/*/' );
    }
    


}