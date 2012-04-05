<?php

class Harapartners_Paymentfactory_Model_Config extends Mage_Payment_Model_Config
{
    protected $_ccTypes = array();
    /**
     * Retrieve array of credit card types
     *
     * @return array
    */
    public function getCcTypes()
    {
        $pTypes = parent::getCcTypes();
        $this->_ccTypes = array();
        $added = false;
        foreach ($pTypes as $code => $name) {
             if ($code=='OT') {
                $added = true;
                $this->addExtraCcTypes();
            }
            $this->_ccTypes[$code] = $name;
        }
        if (!$added) {
            $this->addExtraCcTypes();
        }
        return $this->_ccTypes;
    }

    public function addExtraCcTypes()
    {
        $this->_ccTypes['JCB'] = Mage::helper('paymentfactory')->__('JCB');
        $this->_ccTypes['LASER'] = Mage::helper('paymentfactory')->__('Laser');
        $this->_ccTypes['UATP'] = Mage::helper('paymentfactory')->__('UATP');
        $this->_ccTypes['MCI'] = Mage::helper('paymentfactory')->__('Maestro (International)');
        $this->_ccTypes[Mage_Cybersource_Model_Soap::CC_CARDTYPE_SS] = Mage::helper('paymentfactory')->__('Maestro/Solo(UK Domestic)');

    }

}
