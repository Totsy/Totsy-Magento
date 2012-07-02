<?php

require_once '../app/Mage.php';
Mage::app();

$obs = Mage::getModel('fulfillmentfactory/service_dotcom');
$obs->runDotcomFulfillOrder();

