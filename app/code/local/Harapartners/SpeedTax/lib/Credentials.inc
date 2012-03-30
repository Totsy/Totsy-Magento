<?php

$username = Mage::getStoreConfig('speedtax/speedtax/account' );
$password = Mage::getStoreConfig('speedtax/speedtax/license' );
$company = Mage::getStoreConfig('speedtax/speedtax/company_code');


/*$username = 'm.erne@harapartners.com';
$password = 'jk#@78GQK';
$company = 'STX-02272-00';*/
/*
Change for production: $wsdl = 'http://service.myspeedtax.com/tx/services/STxTransactionService?wsdl';
*/
//$wsdl = 'http://service.myspeedtax.com/tx/services/STxTransactionService?wsdl';
//$wsdl = 'http://service.demo.myspeedtax.com/tx/services/STxTransactionService?wsdl';
if( Mage::getStoreConfig( 'speedtax/speedtax/wsdl' ) ) {
	$wsdl = 'http://service.demo.myspeedtax.com/tx/services/STxTransactionService?wsdl';
} else {
	$wsdl = 'http://service.myspeedtax.com/tx/services/STxTransactionService?wsdl';
}
?>