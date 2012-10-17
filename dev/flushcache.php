<?php
/**
 * @category    Totsy
 * @package     Totsy
 * @author      Tharsan Bhuvanendran <tbhuvanendran@totsy.com>
 * @copyright   Copyright (c) 2012 Totsy LLC
 */

// only execute from the CLI
if ('cli' != php_sapi_name()) {
    exit(0);
}

require_once __DIR__ . '/../app/Mage.php';
Mage::app();

$tags = array();
$opts = getopt('t:', array('tag:'));

if (isset($opts['t'])) {
    $tags = explode(',', $opts['t']);
} else if (isset($opts['tag'])) {
    $tags = explode(',', $opts['tag']);
} else {
    die("Usage: flushcache.php -t <tags> --tags=<tags>\n");
}

Mage::app()->getCache()->clean('all', $tags);
