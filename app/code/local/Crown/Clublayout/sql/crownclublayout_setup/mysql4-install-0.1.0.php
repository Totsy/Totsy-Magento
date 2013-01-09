<?php
$installer = $this;

$installer->startSetup ();

Mage::init();

$category = Mage::getModel('catalog/category');
$category->setPath(Mage::app()->getStore()->getRootCategoryId()) // set parent to be root category
    ->setName('Plus')
    ->setUrlKey('plus')
    ->setIsActive(1)
    ->setIncludeInMenu(1)
    ->setInfinitescroll(1)
    ->setDisplayMode('PAGE')
    //->setLandingPage($idToCmsBlock)
    ->setPageLayout('default')
    ->setCustomUseParentSettings(0)
    ->setCustomLayoutUpdate('')
    ->save();
$this->endSetup();