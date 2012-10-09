<?php

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$linkshare = Mage::getModel('linkshare/transactions');

$linkshare->sendUpdates();
