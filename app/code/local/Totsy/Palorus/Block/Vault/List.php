<?php
/**
 * @category    Totsy
 * @package     Totsy_Palorus_Block_Vault_List
 * @author      Tom Royer <troyer@totsy.com>
 * @copyright   Copyright (c) 2013 Totsy LLC
 */
 
class Totsy_Palorus_Block_Vault_List extends Litle_Palorus_Block_Vault_List
{

    /**
    *
    * @return string
    */
    public function getAddUrl()
    {
        return $this->getUrl('*/*/new');
    }

    public function getFullCcCardType( $shortCardType ) {
        switch ( $shortCardType ) {
            case 'AE':
                return 'American Express';
            case 'VI':
                return 'Visa';
            case 'MC':
                return 'MasterCard';
            case 'DI':
                return 'Discover';
            default:
                return $shortCardType;
        }
    }
}
