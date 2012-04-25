<?php
/**
 * @category    Totsy
 * @package     Totsy\Api\Adminhtml
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Api_Adminhtml_ClientController
    extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/totsyapi');
        $this->_addContent(
            $this->getLayout()->createBlock('totsyapi/adminhtml_client_index')
        );
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $model = Mage::getModel('totsyapi/client');
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            $model->load($id);

            if ($model->isObjectNew()) {
                Mage::getSingleton('adminhtml/session')
                    ->addError(Mage::helper('totsyapi')->__('No Record Found.'));
                $this->_redirect('*/*/');
            }

            Mage::getSingleton('adminhtml/session')
                ->setFormData($model->getData());
        }

        $this->loadLayout();
        $this->_addContent(
            $this->getLayout()->createBlock('totsyapi/adminhtml_client_edit')
        );
        $this->renderLayout();
    }

    public function saveAction()
    {
        $model = Mage::getModel('totsyapi/client');
        $id    = $this->getRequest()->getParam('id');
        $data  = $this->getRequest()->getPost();

        // inject a new randomly generated Authorization Token if one
        // wasn't specified
        if (empty($data['authorization'])) {
            $data['authorization'] = md5(uniqid());
        }

        $data['active'] = isset($data['active']);

        if ($id) {
            $model->load($id);
            $successMessage = 'Updated the Totsy API Client record.';
        } else {
            $successMessage = 'Created a new Totsy API Client record.';
        }

        $model->addData($data);
        $model->save();

        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('totsyapi')->__($successMessage)
        );
        $this->_redirect('*/*/');
    }
}
