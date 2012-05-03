<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_SpeedTax_Adminhtml_Log_ErrorController extends Mage_Adminhtml_Controller_Action {
    
    public function indexAction() {
        $this->loadLayout ()->_setActiveMenu ( 'harapartners/log' )->_addContent ( $this->getLayout ()->createBlock ( 'speedtax/adminhtml_log_error_index' ) )->renderLayout ();
    }
    
    public function newAction() {
        $this->_forward ( 'edit' );
    }
    
    public function editAction() {
        $id = $this->getRequest ()->getParam ( 'id' );
        $model = Mage::getModel ( 'speedtax/log_error' )->load ( $id );
        
        if ($model->getId () || $id == 0) {
            $data = Mage::getSingleton ( 'adminhtml/session' )->getFormData ( true );
            if (! empty ( $data )) {
                $model->setData ( $data );
            }
            
            Mage::register ( 'log_model', $model );
            
            $this->loadLayout ()->_setActiveMenu ( 'speedtax/edit' );
            
            $this->_addBreadcrumb ( Mage::helper ( 'speedtax' )->__ ( 'Manage Buy X Rules' ), Mage::helper ( 'adminhtml' )->__ ( 'Manage Buy X Rules' ) );
            $this->_addBreadcrumb ( Mage::helper ( 'speedtax' )->__ ( 'Buy X Rule Configuration' ), Mage::helper ( 'adminhtml' )->__ ( 'Buy X Rule Configuration' ) );
            
            $this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
            
            $this->_addContent ( $this->getLayout ()->createBlock ( 'speedtax/adminhtml_upload_edit' ) );
            
            $this->renderLayout ();
        } else {
            Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'speedtax' )->__ ( 'Buy X Rule does not exist' ) );
            $this->_redirect ( '*/*/' );
        }
    }
    
    public function exportCsvAction()
    {
        $fileName   = 'speedtax_errorlog.csv';
        $content    = $this->getLayout()->createBlock('speedtax/adminhtml_log_error_index_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'speedtax_errorlog.xml';
        $content    = $this->getLayout()->createBlock('speedtax/adminhtml_log_error_index_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }


}