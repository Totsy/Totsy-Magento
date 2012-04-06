<?php

$username = Mage::getStoreConfig('speedtax/speedtax/account' );
$password = Mage::getStoreConfig('speedtax/speedtax/license' );
$company = Mage::getStoreConfig('speedtax/speedtax/company_code');

if( Mage::getStoreConfig( 'speedtax/speedtax/wsdl' ) ) {
	$wsdl = 'http://service.demo.myspeedtax.com/tx/services/STxTransactionService?wsdl';
} else {
	$wsdl = 'http://service.myspeedtax.com/tx/services/STxTransactionService?wsdl';
}