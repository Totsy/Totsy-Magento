<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Adminhtml_ProfileController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction() {
        $this->loadLayout();
        $this->_setActiveMenu('system/urapidflow');
        $this->_addBreadcrumb(Mage::helper('urapidflow')->__('RapidFlow Profile Manager'), Mage::helper('urapidflow')->__('RapidFlow Profile Manager'));
        $this->_addContent($this->getLayout()->createBlock('urapidflow/adminhtml_profile'));

        return $this;
    }

    protected function _validateSecretKey()
    {
        return true;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/urapidflow');
    }

    protected function _getProfile($idField='id')
    {
        $profile = Mage::getModel('urapidflow/profile');
        $id = $this->getRequest()->getParam($idField);

        if ($id) {
            $profile->load($id);
        }
        if (!$profile->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('urapidflow')->__('Invalid Profile ID'));
        }
        $profile = $profile->factory();

        return $profile;
    }

    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }

    public function editAction() {
        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('urapidflow/profile')->load($id)->factory();

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('profile_data', $model);

/*            try {
                $model->run();
                exit;
            } catch (Exception $e) {
                echo $e->getMessage();
            }*/

            $this->loadLayout();
            $this->_setActiveMenu('system/urapidflow');

            $this->_addBreadcrumb(Mage::helper('urapidflow')->__('SingleFeed Profile Manager'), Mage::helper('adminhtml')->__('SingleFeed Profile Manager'));
            $this->_addBreadcrumb(Mage::helper('urapidflow')->__('New Profile'), Mage::helper('adminhtml')->__('New Profile'));

            $this->getLayout()->getBlock('head')
                ->setCanLoadExtJs(true)
                ->setCanLoadRulesJs(true);

            $this->_addContent($this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit'))
                ->_addLeft($this->getLayout()->createBlock('urapidflow/adminhtml_profile_edit_tabs'));

            $this->renderLayout();
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('urapidflow')->__('Profile does not exist'));
            $this->_redirect('*/*/');
        }
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function ajaxStartAction()
    {
        try {
            $profile = $this->_getProfile();
            switch ($profile->getRunStatus()) {
            case 'pending':
                $profile->start()->save()->run();
                $result = array('success'=>true);
                break;
            case 'running':
                $result = array('warning'=>Mage::helper('urapidflow')->__('The profile is already running'));
                break;
            default:
                $result = array('error'=>Mage::helper('urapidflow')->__('Invalid profile run status'));
            }
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage());
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Zend_Json::encode($result));
    }

    public function ajaxStatusAction()
    {
        $profile = $this->_getProfile();

        $result = array(
            'run_status' => $profile->getRunStatus(),
            'html' => $this->getLayout()->createBlock('urapidflow/adminhtml_profile_status')->setProfile($profile)->toHtml()
        );

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setBody(Zend_Json::encode($result));
    }

    public function saveAction() {
        if ($data = $this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('urapidflow/profile');

                if (($id = $this->getRequest()->getParam('id'))) {
                    $model->load($id);
                }
                if (!isset($data['columns_post'])) {
                    $data['columns_post'] = array();
                }
                if (isset($data['conditions'])) {
                    $data['conditions_post'] = $data['conditions'];
                    unset($data['conditions']);
                }
                if (isset($data['options']['reindex'])) {
                    $data['options']['reindex'] = array_flip($data['options']['reindex']);
                }
                if (isset($data['options']['refresh'])) {
                    $data['options']['refresh'] = array_flip($data['options']['refresh']);
                }
                $model->addData($data);
                $model = $model->factory();

                if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
                    $model->setCreatedTime(now())
                        ->setUpdateTime(now());
                } else {
                    $model->setUpdateTime(now());
                }

                $model->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Profile was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if (($invokeStatus = $this->getRequest()->getParam('start'))) {
                    $model->pending($invokeStatus)->save();
                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('urapidflow')->__('Profile started successfully'));
                }

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
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Unable to find profile to save'));
        $this->_redirect('*/*/');
    }

    public function uploadAction()
    {
        $result = array();
        try {
            $uploader = new Varien_File_Uploader('file');
            $uploader->setAllowedExtensions(array('csv','txt','*'));
            $uploader->setAllowRenameFiles(false);
            $uploader->setFilesDispersion(false);

            $target = Mage::getConfig()->getVarDir('urapidflow/import');
            Mage::getConfig()->createDirIfNotExists($target);
            $result = $uploader->save($target);

            $result['cookie'] = array(
                'name'     => session_name(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain()
            );
        } catch (Exception $e) {
            $result = array('error'=>$e->getMessage(), 'errorcode'=>$e->getCode());
        }

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }

    public function downloadLogAction()
    {
        $profile = $this->_getProfile();

        $this->_pipeFile(
            $profile->getLogBaseDir().DS.$profile->getLogFilename(),
            $profile->getLogFilename(),
            'application/vnd.ms-excel'
        );
    }

    public function exportExcelReportAction()
    {
        $profile = $this->_getProfile();

        $profile->exportExcelReport();

        $this->_pipeFile(
            $profile->getExcelReportBaseDir().DS.$profile->getExcelReportFilename(),
            $profile->getExcelReportFilename(),
            'application/vnd.ms-excel'
        );
    }

    public function testAction()
    {
        try {
            $profile = $this->_getProfile();
            try { $profile->stop(); } catch (Exception $e) { };
            $profile->start()->save()->run();
        } catch (Exception $e) {
            var_dump($e);
        }
        exit;
    }

    public function deleteAction() {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('urapidflow/profile');

                $model->setId($this->getRequest()->getParam('id'))
                    ->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Profile was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }

    public function massDeleteAction()
    {
        $profileIds = $this->getRequest()->getParam('profiles');
        if(!is_array($profileIds)) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select profile(s)'));
        } else {
            try {
                foreach ($profileIds as $profileId) {
                    $profile = Mage::getModel('urapidflow/profile')->load($profileId);
                    $profile->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($profileIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function massStatusAction()
    {
        $profileIds = $this->getRequest()->getParam('profiles');
        if(!is_array($profileIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select profile(s)'));
        } else {
            try {
                foreach ($profileIds as $profileId) {
                    $profile = Mage::getSingleton('urapidflow/profile')
                        ->load($profileId)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($profileIds))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function exportCsvAction()
    {
        $fileName   = 'urapidflow_profiles.csv';
        $content    = $this->getLayout()->createBlock('urapidflow/adminhtml_profile_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'singefeed_profiles.xml';
        $content    = $this->getLayout()->createBlock('urapidflow/adminhtml_profile_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($filename, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$filename);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        exit;
    }

    protected function _pipeFile($filepath, $filename, $contentType='application/octet-stream')
    {
        if (!is_readable($filepath)) {
            header('HTTP/1.1 404 Not Found');
            echo "<h1>Not found</h1>";
            exit;
        }

        header('HTTP/1.1 200 OK');
        header('Pragma: public');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0', true);
        header('Content-Disposition: attachment; filename='.$filename);
        header('Last-Modified: '.date('r'));
        header('Accept-Ranges: bytes');
        header('Content-Length: '.filesize($filepath));
        header('Content-Type: ', $contentType);

        $from = fopen($filepath, 'rb');
        $to = fopen('php://output', 'wb');

        stream_copy_to_stream($from, $to);
        exit;
    }

    public function pauseAction()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                Mage::throwException("INVALID ID");
            }
            Mage::getModel('urapidflow/profile')->load($id)->factory()->pause()->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('urapidflow')->__('Profile paused successfully'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', array('_current'=>true));
    }

    public function resumeAction()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                Mage::throwException("INVALID ID");
            }
            Mage::getModel('urapidflow/profile')->load($id)->factory()->resume()->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('urapidflow')->__('Profile resumed successfully'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', array('_current'=>true));
    }

    public function stopAction()
    {
        try {
            $id = $this->getRequest()->getParam('id', false);
            if (!$id) {
                Mage::throwException("INVALID ID");
            }
            Mage::getModel('urapidflow/profile')->load($id)->factory()->stop()->save();
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('urapidflow')->__('Profile stopped successfully'));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
        $this->_redirect('*/*/edit', array('_current'=>true));
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $form = $this->getRequest()->getParam('form');

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('urapidflow/rule'))
            ->setPrefix($form);
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract
            || $model instanceof Mage_Rule_Model_Action_Abstract) {
            $model->setJsFormObject('rule_'.$form.'_fieldset');
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }

    public function chooserAction()
    {
        Mage::app()->getRequest()->setParam('form', '');
        switch ($this->getRequest()->getParam('attribute')) {
            case 'sku':
                $type = 'adminhtml/promo_widget_chooser_sku';
                break;

            case 'categories':
                $type = 'adminhtml/promo_widget_chooser_categories';
                break;
        }
        if (!empty($type)) {
            $block = $this->getLayout()->createBlock($type);
            if ($block) {
                $this->getResponse()->setBody($block->toHtml());
            }
        }
    }
}