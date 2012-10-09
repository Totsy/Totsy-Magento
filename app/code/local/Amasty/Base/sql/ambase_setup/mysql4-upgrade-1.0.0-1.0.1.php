<?php
$this->startSetup();

Mage::getModel('core/config_data')
        ->setScope('default')
        ->setPath('ambase/feed/installed')
        ->setValue(time())
        ->save(); 

$feedData = array();
$feedData[] = array(
    'severity'      => 4,
    'date_added'    => gmdate('Y-m-d H:i:s', time()),
    'title'         => 'Amasty\'s extension has been installed. Check the Admin > Configuration > Amasty section.',
    'description'   => 'You can see versions of the installed extensions right in the admin, as well as configure notifications about major updates.',
    'url'           => 'http://amasty.com/news/updates-and-notifications-configuration-9.html'
//    'url'           => Mage::getModel('adminhtml/url')->getUrl('adminhtml/system_config/edit', array('section'=>'ambase')),
);

Mage::getModel('adminnotification/inbox')->parse($feedData);

$this->endSetup();