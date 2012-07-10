<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Test_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Test_Model_Autoregistration
    extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Load an Autoregistration data model, fetched by the token.
     *
     * @test
     * @loadFixture
     */
    public function loadByToken()
    {
        $autoreg = Mage::getModel('totsycustomer/autoregistration')
            ->loadByToken('e3d34caed57b74235aa01e1dba626054570df08750e5955d6672c232601757d8');

        $this->assertEquals('tbhuvanendran+autoregtest1@totsy.com', $autoreg->getData('email'));
        $this->assertEquals(1, $autoreg->getData('store_id'));
    }

    /**
     * Create a new Autoregistration record.
     *
     * @test
     */
    public function createAutoregistration()
    {
        $autoreg = Mage::getModel('totsycustomer/autoregistration')
            ->setEmail('autoreg@unit-test.com')
            ->save();

        $this->assertEquals('autoreg@unit-test.com', $autoreg->getData('email'));
        $this->assertNotEmpty($autoreg->getData('token'));
        $this->assertNotEmpty($autoreg->getData('created_at'));
    }

    /**
     * Process an existing Autoregistration record by spawning a new Customer
     * record from it.
     *
     * @test
     * @loadFixture
     */
    public function processAutoregistration()
    {
        $autoreg = Mage::getModel('totsycustomer/autoregistration')
            ->loadByToken('e3d34caed57b74235aa01e1dba626054570df08750e5955d6672c232601757d8');

        $customer = $autoreg->createCustomer();

        $this->assertEquals('tbhuvanendran+autoregtest1@totsy.com', $customer->getData('email'));
        $this->assertEquals(1, $customer->getData('store_id'));
        $this->assertNotEmpty($customer->getData('entity_id'));
        $this->assertNotEmpty($customer->getData('password'));
    }
}
