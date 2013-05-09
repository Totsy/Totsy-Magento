<?php
/**
 * @category    Totsy
 * @package     Totsy_Palorus_VaultController
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */

require_once Mage::getBaseDir('code') . '/community/Litle/LitleSDK/LitleOnline.php';
require_once 'Litle/Palorus/controllers/VaultController.php';

class Totsy_Palorus_VaultController extends Litle_Palorus_VaultController
{
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->_getSession()->authenticate($this) || !Mage::helper('palorus')->isVaultEnabled()) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * List vaulted cards
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Add vault card
     */
    public function newAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * create vault card
     */
    public function createAction()
    {
        //Getting Datas
        $customer = $this->_getSession()->getCustomer();
        $payment = new Varien_Object($this->getRequest()->getParam('payment'));
        $billingAddress = new Varien_Object($this->getRequest()->getParam('billing'));

        $region = Mage::getModel('directory/region')->load($billingAddress->getRegionId());

        #Create Address that will be link with Payment Profile
        $addressId = $this->createAddressFromForm($customer);
        //Treating Datas
        $street = $billingAddress->getStreet();
        $expDate = $payment->getCcExpMonth() . substr($payment->getCcExpYear(), -2);
        if(strlen($expDate) < 4) {
            $expDate = '0' . $expDate;
        }
        if($payment->getCcType() == 'AE') {
            $creditCardType = 'AX';
        } else {
            $creditCardType = $payment->getCcType();
        }
        $amount = 100;
        //Authorization Datas
        $auth_info = array(
            'orderId' => $customer->getId(),
            'amount' => $amount,
            'orderSource'=>'ecommerce',
            'billToAddress'=>array(
                'name' => $billingAddress->getFirstname() . ' ' . $billingAddress->getLastname(),
                'addressLine1' => (is_array($street))
                    ? substr($street[0] . ' ' . $street[1],0,35)
                    : substr($street,0,35),
                'city' => $billingAddress->getCity(),
                'state' => $region->getCode(),
                'zip' => $billingAddress->getPostcode(),
                'country' => 'US'),
            'card'=>array(
                'number' =>$payment->getCcNumber(),
                'expDate' => $expDate,
                'cardValidationNum' => $payment->getCcCid(),
                'type' => $creditCardType)
        );

        $initialize = new LitleOnlineRequest();
        $authResponse = $initialize->authorizationRequest($auth_info);
        $transactionId =  XmlParser::getNode($authResponse,'litleTxnId');

        if(!$transactionId) {
            $this->_redirect ( '*/*/' );
        }
        $auth_reversalinfos = array(
            'litleTxnId' => $transactionId,
            'amount' => $amount
        );
        $initialize->authReversalRequest($auth_reversalinfos);
        //Create Vault Profile if option selected
        $vault = Mage::getModel('palorus/vault');
        $alreadyCreated = Mage::getModel('palorus/vault')->getCustomerToken($customer,Mage::getModel('creditcard/paymentlogic')->getUpdater($authResponse, 'tokenResponse', 'litleToken'));
        if(!$alreadyCreated) {
            $vault->setData('token', Mage::getModel('creditcard/paymentlogic')->getUpdater($authResponse, 'tokenResponse', 'litleToken'))
                ->setData('bin', Mage::getModel('creditcard/paymentlogic')->getUpdater($authResponse, 'tokenResponse', 'bin'))
                ->setData('customer_id', $customer->getId())
                ->setData('type', $payment->getCcType())
                ->setData('last4', substr($payment->getCcNumber(), -4))
                ->setData('expiration_month', $payment->getCcExpMonth())
                ->setData('expiration_year', $payment->getCcExpYear())
                ->setData('is_visible', '1')
                ->setData('address_id', $addressId)
                ->save();
        }
        $this->_redirect ( '*/*/' );
    }

    /**
     * @todo Display the edit form
     *
     */
//     public function editAction()
//     {
//         $this->loadLayout();
//         $this->_initLayoutMessages('customer/session');

//         $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
//         if ($navigationBlock) {
//             $navigationBlock->setActive('palorus/vault');
//         }

//         $this->renderLayout();
//     }

    /**
     * @todo Save the edit form
     *
     */
//     public function editPostAction()
//     {

//     }

    /**
     * Delete the card from our database
     */
    public function deleteAction()
    {
        $vaultId = $this->getRequest()->getParam('vault_id');
        if ($vaultId) {
            $vault = Mage::getModel('palorus/vault')->load($vaultId);
            if($vault->getVaultId()) {
                if ($vault->getCustomerId() != $this->_getSession()->getCustomer()->getId()) {
                    $this->_getSession()->addError($this->__('The card does not belong to this customer.'));
                    $this->getResponse()->setRedirect(Mage::getUrl('*/*/index'));
                    return;
                }

                try {
                    $vault->delete();
                    $this->_getSession()->addSuccess($this->__('The card has been deleted.'));
                } catch (Exception $e) {
                    $this->_getSession()->addException($e, $this->__('An error occurred while deleting the card.'));
                    Mage::logException($e);
                }
            } else {
                //Case It's a Cybersource profile
                try{
                    $profile = Mage::getModel ( 'paymentfactory/profile' )->load($vaultId,'subscription_id');
                    if($profile->getId()) {
                        $profile->setData('is_default',1)
                            ->save();
                        $this->_getSession()->addSuccess($this->__('The card has been deleted.'));
                    }
                }catch(Exception $e){
                    $this->_getSession()->addException($e, $this->__('An error occurred while deleting the card.'));
                    Mage::logException($e);
                }
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Retrieve customer session object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function createAddressFromForm($customer) {
        $address = Mage::getModel('customer/address');
        $addressForm = Mage::getModel('customer/form');
        $addressForm->setFormCode('customer_address_edit')
            ->setEntity($address);
        $addressData = $addressForm->extractData($this->getRequest(), 'billing', false);
        $addressErrors = $addressForm->validateData($addressData);
        if ($addressErrors === true) {
            $addressForm->compactData($addressData);
            $address->setCustomerId($customer->getId())
                ->setIsDefaultBilling(false)
                ->setIsDefaultShipping(false);
            $errors = $address->validate();
            if (is_array($errors)) {
                $errors = array_merge($errors, $addressErrors);
                return $errors;
            } else {
                $address->save();
                return $address->getId();
            }
        } else {
            return $addressErrors;
        }
    }
}