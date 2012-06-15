<?php
/**
 * Adminhtml sales orders creation process controller
 *
 * @category   Totsy
 * @package    Totsy_Adminhtml
 * @author      Tom Royer <troyer@totsy.com>
 */
require_once 'Mage/Adminhtml/controllers/Sales/Order/CreateController.php';

class Totsy_Adminhtml_Sales_Order_CreateController extends Mage_Adminhtml_Sales_Order_CreateController
{
    public function saveAction()
    {
        echo 'C';
        error_log('Yes, I did it!')
        die()
        $customerId = $this->_getOrderCreateModel()->getQuote()->getCustomerId();
        $profile = Mage::getModel('paymentfactory/profile');
        try {
            $this->_processActionData('save');
            if ($paymentData = $this->getRequest()->getPost('payment')) {
                $profile->loadByCcNumberWithId($paymentData['cc_number'].$customerId.$paymentData[ 'cc_exp_year' ].$paymentData[ 'cc_exp_month' ]);
                if(!!$profile && !!$profile->getId()){
                    $cybersourceIdEncrypted = $profile->getEncryptedSubscriptionId();
                    if($cybersourceIdEncrypted) {
                        $paymentData['cybersource_subid'] = $cybersourceIdEncrypted;
                    }
                }
                $this->_getOrderCreateModel()->setPaymentData($paymentData);
                $this->_getOrderCreateModel()->getQuote()->getPayment()->addData($paymentData);
            }

            $order = $this->_getOrderCreateModel()
                ->setIsValidate(true)
                ->importPostData($this->getRequest()->getPost('order'))
                ->createOrder();

            $this->_getSession()->clear();
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been created.'));
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        } catch (Mage_Payment_Model_Info_Exception $e) {
            $this->_getOrderCreateModel()->saveQuote();
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e){
            $message = $e->getMessage();
            if( !empty($message) ) {
                $this->_getSession()->addError($message);
            }
            $this->_redirect('*/*/');
        }
        catch (Exception $e){
            $this->_getSession()->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
            $this->_redirect('*/*/');
        }
    }
}