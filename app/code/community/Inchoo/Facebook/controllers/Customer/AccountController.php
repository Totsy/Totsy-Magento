<?php
/**
 * Facebook Customer account controller
 *
 * @category    Inchoo
 * @package     Inchoo_Facebook
 * @author      Ivan Weiler  <ivan.weiler@gmail.com>
 * @copyright   Inchoo (http://inchoo.net)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Inchoo_Facebook_Customer_AccountController extends Mage_Core_Controller_Front_Action
{

    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('inchoo_facebook/config')->isEnabled()) {
            $this->norouteAction();
        }

        return $this;
    }

    public function postDispatch()
    {
        parent::postDispatch();
        Mage::app()->getCookie()->delete('fb-referer');
        return $this;
    }

    public function connectAction()
    {
        if(!$this->_getSession()->validate()) {
            $this->_getCustomerSession()->addError($this->__('Facebook connection failed.'));
            $this->_redirect('customer/account/login');
            return;
        }

        //login or connect

        $customer = Mage::getModel('customer/customer');
        $storeId = Mage::app()->getStore()->getId();

        $collection = $customer->getCollection()
                    ->addAttributeToFilter('facebook_uid', $this->_getSession()->getUid())
                    //Must set store id for multi store or store view HP: Yang
                    ->addAttributeToFilter('store_id', $storeId)
                    ->setPageSize(1);

        if($customer->getSharingConfig()->isWebsiteScope()) {
            $collection->addAttributeToFilter('website_id', Mage::app()->getWebsite()->getId());
        }

        if($this->_getCustomerSession()->isLoggedIn()) {
            $collection->addFieldToFilter('entity_id', array('neq' => $this->_getCustomerSession()->getCustomerId()));
        }

        $uidExist = (bool)$collection->count();

        if($this->_getCustomerSession()->isLoggedIn() && $uidExist) {
            $existingCustomer = $collection->getFirstItem();
            $existingCustomer->setFacebookUid('');
            $existingCustomer->getResource()->saveAttribute($existingCustomer, 'facebook_uid');
        }

        if($this->_getCustomerSession()->isLoggedIn()) {
            $currentCustomer = $this->_getCustomerSession()->getCustomer();
            $currentCustomer->setFacebookUid($this->_getSession()->getUid());
            $currentCustomer->getResource()->saveAttribute($currentCustomer, 'facebook_uid');

            $this->_getCustomerSession()->addSuccess(
                $this->__('Your Facebook account has been successfully connected. Now you can fast login using Facebook Connect anytime.')
            );
            $this->_loginPostRedirect();
            return;
        }

        if ($uidExist) {
            $uidCustomer = $collection->getFirstItem();
            $customer    = $customer->load($uidCustomer->getId());

            $urlObject = Mage::getModel('core/url')->setStore(
                $uidCustomer->getStoreId()
            );
            $urlLogin = $urlObject->getRouteUrl('customer/account/login');

            if($uidCustomer->getStoreId()
                && $uidCustomer->getStoreId() != $storeId
            ) {
                // Harapartners, Store switching by rediret
                // Must validate store id for multi store or store view HP: Yang
                // since FB redirects IE differently, it's wrong to use referer
                // like before
                $this->_getCustomerSession()->setBeforeAuthUrl($urlLogin);
                $this->_loginPostRedirect();
                return;
            }

            // ensure the user has not been deactivated
			/*START Hara Partners Edward for deactive a customer*/
            /*there must be a relative customer group on admin for deactivating user, it this case the Id of it is 4*/
            //used to be like if ($session->getCustomer()->isDeactivated()) {
            $deactiveGroupId = Harapartners_Service_Helper_Data::DEACTIVATED_USER_GROUP_ID;
            if ($session->getCustomer()->getGroupId() == $deactiveGroupId) {
            /*END Hara Partners Edward for deactive a customer*/
                $this->_getCustomerSession()->addError(
                    $this->__('Your account has been disabled. Please contact customer service for more information.')
                );

                $this->_getCustomerSession()->setBeforeAuthUrl($urlLogin);
                $this->_loginPostRedirect();
                return;
            }

            if ($uidCustomer->getConfirmation()) {
                $uidCustomer->setConfirmation(null);
                Mage::getResourceModel('customer/customer')->saveAttribute(
                    $uidCustomer,
                    'confirmation'
                );
            }

            $this->_getCustomerSession()->setCustomerAsLoggedIn($uidCustomer);

            //since FB redirects IE differently, it's wrong to use referer like before
            $this->_loginPostRedirect();
            return;
        }

        //let's go with an e-mail

        try {
            $standardInfo = $this->_getSession()->getClient()->call("/me");

        } catch(Mage_Core_Exception $e) {
            $this->_getCustomerSession()->addError(
                $this->__('Facebook connection failed.') .
                ' ' .
                $this->__('Service temporarily unavailable.')
            );
            $this->_redirect('customer/account/login');
            return;
        }

        if(!isset($standardInfo['email'])) {
            $this->_getCustomerSession()->addError(
                $this->__('Facebook connection failed.') .
                ' ' .
                $this->__('Email address is required.')
            );
            $this->_redirect('customer/account/login');
            return;
        }

        $customer
            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
            ->loadByEmail($standardInfo['email']);

        if($customer->getId()) {
            $customer->setFacebookUid($this->_getSession()->getUid());
            Mage::getResourceModel('customer/customer')->saveAttribute($customer, 'facebook_uid');

            if($customer->getConfirmation()) {
                $customer->setConfirmation(null);
                Mage::getResourceModel('customer/customer')->saveAttribute($customer, 'confirmation');
            }

            $this->_getCustomerSession()->setCustomerAsLoggedIn($customer);
            $this->_getCustomerSession()->addSuccess(
                $this->__('Your Facebook account has been successfully connected. Now you can fast login using Facebook Connect anytime.')
            );
            $this->_loginPostRedirect();
            return;
        }

        //registration needed
        $randomPassword = $customer->generatePassword(8);
        $customer	->setId(null)
                    ->setSkipConfirmationIfEmail($standardInfo['email'])
                    ->setFirstname($standardInfo['first_name'])
                    ->setLastname($standardInfo['last_name'])
                    ->setEmail($standardInfo['email'])
                    ->setPassword($randomPassword)
                    ->setConfirmation($randomPassword)
                    ->setFacebookUid($this->_getSession()->getUid());

        //FB: Show my sex in my profile
        if(isset($standardInfo['gender']) && $gender=Mage::getResourceSingleton('customer/customer')->getAttribute('gender')) {
            $genderOptions = $gender->getSource()->getAllOptions();
            foreach($genderOptions as $option) {
                if($option['label']==ucfirst($standardInfo['gender'])) {
                     $customer->setGender($option['value']);
                     break;
                }
            }
        }

        //FB: Show my full birthday in my profile
        if(isset($standardInfo['birthday']) && count(explode('/',$standardInfo['birthday']))==3) {

            $dob = $standardInfo['birthday'];

            if(method_exists($this,'_filterDates')) {
                $filtered = $this->_filterDates(array('dob'=>$dob), array('dob'));
                $dob = current($filtered);
            }

            $customer->setDob($dob);
        }

        //$customer->setIsSubscribed(1);

        //registration will fail if tax required, also if dob, gender aren't allowed in profile
        $errors = array();
        $validationCustomer = $customer->validate();
        if (is_array($validationCustomer)) {
            $errors = array_merge($validationCustomer, $errors);
        }
        $validationResult = count($errors) == 0;

        if (true === $validationResult) {
            $customer->save();
            
            //Harapartners, Andu, for affiliate information setup START
			Mage::dispatchEvent('customer_register_success',
			                        array('account_controller' => $this, 'customer' => $customer)
			                    );
            //Harapartners, Andu,  END
            
			//Harapartners, Edward, for facebook speciall email   the template is  _trans_Facebook_Register
			//also set at admin->customer configuateion->Create New Account Options->Welcome Email
			//(not like Default Welcome Email, Welcome Email is dedicate for facebook register)
            $customer->sendNewAccountEmail('confirmed');
			//Harapartners, Edward
            
            //Harapartners, Yang, START
            //Harapartners, Yang, MUST dispatch 'customer_register_success' otherwise the cookies will not be set properly
            Mage::dispatchEvent('customer_register_success',
                    array('account_controller' => $this, 'customer' => $customer)
            );
            //Harapartners, Yang, END

            $this->_getCustomerSession()->setFacebookAccountFirstCreate(true);
            $this->_getCustomerSession()->addSuccess(
                $this->__('Thank you for registering with %s', Mage::app()->getStore()->getFrontendName()) .
                '. ' .
                $this->__('You will receive welcome email with registration info in a moment.')
            );
            
            $this->_getCustomerSession()->setCustomerAsLoggedIn($customer);
            $this->_loginPostRedirect();
            return;

        //else set form data and redirect to registration
        } else {
            $this->_getCustomerSession()->setCustomerFormData($customer->getData());
            $this->_getCustomerSession()->addError($this->__('Facebook profile can\'t provide all required info, please register and then connect with Facebook for fast login.'));
            if (is_array($errors)) {
                foreach ($errors as $errorMessage) {
                    $this->_getCustomerSession()->addError($errorMessage);
                }
            }

            $this->_redirect('customer/account/create');

        }

    }

    protected function _loginPostRedirect()
    {
        $session = $this->_getCustomerSession();
        $redirectUrl = "/";

        //Harapartners, yang: set referer to base
        //        if ($session->getBeforeAuthUrl() &&
        //        	!in_array($session->getBeforeAuthUrl(), array(Mage::helper('customer')->getLogoutUrl(), Mage::getBaseUrl()))) {
        //        	$redirectUrl = $session->getBeforeAuthUrl(true);
        //        } elseif(($referer = $this->getRequest()->getCookie('fb-referer'))) {
        //        	$referer = Mage::helper('core')->urlDecode($referer);
        //        	//@todo: check why is this added in Magento 1.7
        //        	//$referer = Mage::getModel('core/url')->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
        //        	if($this->_isUrlInternal($referer)) {
        //        		$redirectUrl = $referer;
        //        	}
        //        }

        $this->_redirectUrl($redirectUrl);
    }    

    private function _getCustomerSession()
    {
        return Mage::getSingleton('customer/session');
    }

    private function _getSession()
    {
        return Mage::getSingleton('inchoo_facebook/session');
    }

}
