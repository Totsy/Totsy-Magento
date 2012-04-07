<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */

$username = Mage::getStoreConfig('speedtax/speedtax/account' );
$password = Mage::getStoreConfig('speedtax/speedtax/license' );
$company = Mage::getStoreConfig('speedtax/speedtax/company_code');

if( Mage::getStoreConfig( 'speedtax/speedtax/wsdl' ) ) {
	$wsdl = 'http://service.demo.myspeedtax.com/tx/services/STxTransactionService?wsdl';
} else {
	$wsdl = 'http://service.myspeedtax.com/tx/services/STxTransactionService?wsdl';
}