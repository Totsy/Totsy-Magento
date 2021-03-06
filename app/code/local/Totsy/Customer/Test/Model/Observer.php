<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Test_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Test_Model_Observer extends
    EcomDev_PHPUnit_Test_Case
{
    /**
     * Detect an invalid token. The user should be redirected to the login page.
     *
     * @test
     */
    public function invalid()
    {
        $observer = Mage::getSingleton('totsycustomer/observer');

        $request  = Mage::app()->getFrontController()->getRequest();
        $request->setRequestUri('/event');
        $request->setPathInfo('/event');
        $request->setPathInfo(null);
        $request->setQuery('auto_access_token', 'randominvalidtoken');

        $observer->autoAuthorization();

        $response = Mage::app()->getFrontController()->getResponse();
        $responseHeaders = $response->getHeaders();

        // find and verify the 'Location' header
        foreach ($responseHeaders as $header) {
            if ('Location' == $header['name']) {
                $this->assertEquals(
                    'customer/account/login',
                    $header['value']
                );
            }
        }

        $this->assertEquals(302, $response->getHttpResponseCode());
    }

    /**
     * Automagically register as a new user.
     *
     * @test
     * @loadFixture
     */
    public function register()
    {
        $mock = $this->getModelMock(
            'customer/customer',
            array('save')
        );
        $mock->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'customer/customer', $mock);

        $mock = $this->getModelMock(
            'customer/session',
            array('setCustomerAsLoggedIn')
        );
        $mock->expects($this->once())
            ->method('setCustomerAsLoggedIn')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'customer/session', $mock);

        $mock = $this->getModelMock(
            'core/email_template_mailer',
            array('send')
        );
        $mock->expects($this->once())
            ->method('send')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'core/email_template_mailer', $mock);

        $observer = Mage::getSingleton('totsycustomer/observer');

        // generate an encrypted e-mail address for an non-existent user
        $token = Mage::getSingleton('core/encryption')->encrypt('autologin@test.net');

        // setup the HTTP Request
        $request  = Mage::app()->getFrontController()->getRequest();
        $request->setRequestUri('/event');
        $request->setPathInfo('/event');
        $request->setPathInfo(null);
        $request->setQuery('auto_access_token', $token);

        $observer->autoAuthorization();
    }

    /**
     * Automagically login as a user.
     *
     * @test
     * @loadFixture
     */
    public function login()
    {
        $observer = Mage::getSingleton('totsycustomer/observer');

        // generate an encrypted e-mail address for an existing user
        $token = Mage::getSingleton('core/encryption')->encrypt('autologin+3@test.net');

        $request  = Mage::app()->getFrontController()->getRequest();
        $request->setRequestUri('/event');
        $request->setPathInfo('/event');
        $request->setPathInfo(null);
        $request->setQuery('auto_access_token', $token);

        // ensure that a user with this e-mail address exists
        $customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail('autologin+3@test.net');
        $this->assertNotNull($customer->getId());

        $observer->autoAuthorization();

        $response = Mage::app()->getFrontController()->getResponse();
        $this->assertEquals(302, $response->getHttpResponseCode());
        $this->assertTrue(Mage::getSingleton('customer/session')->isLoggedIn());
    }

    /**
     * Register for a specific store.
     *
     * @test
     * @loadFixture
     */
    public function registerAltStore()
    {
        $mock = $this->getModelMock(
            'customer/customer',
            array('save')
        );
        $mock->expects($this->once())
            ->method('save')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'customer/customer', $mock);

        $mock = $this->getModelMock(
            'customer/session',
            array('setCustomerAsLoggedIn')
        );
        $mock->expects($this->once())
            ->method('setCustomerAsLoggedIn')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'customer/session', $mock);

        $mock = $this->getModelMock(
            'core/email_template_mailer',
            array('send')
        );
        $mock->expects($this->once())
            ->method('send')
            ->will($this->returnSelf());

        $this->replaceByMock('model', 'core/email_template_mailer', $mock);

        $observer = Mage::getSingleton('totsycustomer/observer');

        // generate an encrypted e-mail address for an non-existent user
        $token = Mage::getSingleton('core/encryption')->encrypt('autologin@test.net');

        // setup the HTTP Request
        $request  = Mage::app()->getFrontController()->getRequest();
        $request->setRequestUri('/event');
        $request->setPathInfo('/event');
        $request->setPathInfo(null);
        $request->setQuery('auto_access_token', $token);
        $request->setQuery('auto_access_store', 2);

        $observer->autoAuthorization();
    }
}
