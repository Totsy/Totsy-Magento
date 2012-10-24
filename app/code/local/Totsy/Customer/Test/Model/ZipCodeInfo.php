<?php
/**
 * @category    Totsy
 * @package     Totsy_Customer_Test_Model
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

class Totsy_Customer_Test_Model_ZipCodeInfo extends
    EcomDev_PHPUnit_Test_Case
{
    /**
     * Test a ZIP Code information lookup that returns a single result.
     *
     * @test
     * @loadFixture
     */
    public function testZipCodeLookupWithSingleResult()
    {
        $zip = '10001';
        $zipCodeInfo = Mage::getModel('totsycustomer/zipCodeInfo')->getCollection();
        $zipCodeInfo->addFieldToFilter('zip', $zip);

        $this->assertEquals(1, count($zipCodeInfo));

        $result = $zipCodeInfo->getFirstItem();
        $this->assertEquals('Robot', $result->getCity());
        $this->assertEquals('NY', $result->getState());
    }

    /**
     * Test a ZIP Code information lookup that returns multiple results.
     *
     * @test
     * @loadFixture
     */
    public function testZipCodeLookupWithMultipleResults()
    {
        $zip = '10002';
        $zipCodeInfo = Mage::getModel('totsycustomer/zipCodeInfo')->getCollection();
        $zipCodeInfo->addFieldToFilter('zip', $zip);

        $this->assertEquals(2, count($zipCodeInfo));

        $results = $zipCodeInfo->getItems();
        $this->assertEquals('Zebulon', $results[1]->getCity());
        $this->assertEquals('NY', $results[1]->getState());
        $this->assertEquals('Astrid', $results[2]->getCity());
        $this->assertEquals('NJ', $results[2]->getState());
    }
}
