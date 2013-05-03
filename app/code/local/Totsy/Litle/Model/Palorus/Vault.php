<?php
/**
 * @category    Totsy
 * @package     Totsy_Litle_Model_Palorus_Vault
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */
 
class Totsy_Litle_Model_Palorus_Vault extends Litle_Palorus_Model_Vault
{
    /**
     * Create or update a token from a payment object
     *
     * @param Varien_Object $payment
     * @param string $vault
     * @param string $bin
     * @return Litle_Palorus_Model_Vault
     */
    public function setTokenFromPayment(Varien_Object $payment, $token, $bin)
    {
        if (!$payment->getCcNumber() || !$token) {
            return false;
        }

        $vault = $this->getCustomerToken($payment->getOrder()->getCustomer(), $token);
        if (!$vault) {
            $vault = Mage::getModel('palorus/vault');
        }

        $order = $payment->getOrder();
        Mage::helper('core')->copyFieldset('palorus_vault_order', 'to_vault', $order, $vault);
        Mage::helper('core')->copyFieldset('palorus_vault_payment', 'to_vault', $payment, $vault);

        $last4 = substr($payment->getCcNumber(), -4);
        $ccType = $payment->getCcType();

        $vault->setLast4(substr($payment->getCcNumber(), -4))
            ->setLitleCcType($payment->getCcType())
            ->setToken($token)
            ->setBin($bin);

        $vault->save();

        $payment->setLitleVaultId($vault->getId())
                ->save();

        return $vault;
    }
}