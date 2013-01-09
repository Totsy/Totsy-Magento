<?php
$installer = $this;

/* @var $plusCategory Mage_Catalog_Model_Category */
$plusCategory           = Mage::getModel('catalog/category');

/* @var $vaultCategory Mage_Catalog_Model_Category */
$vaultCategory          = Mage::getModel('catalog/category');

/* @var $subscriptionCategory Mage_Catalog_Model_Category */
$subscriptionCategory   = Mage::getModel('catalog/category');

/* @var $discountCategory Mage_Catalog_Model_Category */
$discountCategory       = Mage::getModel('catalog/category');

/* @var $entertainmentCategory Mage_Catalog_Model_Category */
$entertainmentCategory  = Mage::getModel('catalog/category');

/* @var $movieCategory Mage_Catalog_Model_Category */
$movieCategory          = Mage::getModel('catalog/category');

/* @var $parkCategory Mage_Catalog_Model_Category */
$parkCategory           = Mage::getModel('catalog/category');

/* @var $vaultPage Mage_Cms_Model_Block */
$vaultBlock              = Mage::getModel('cms/block');

/* @var $entertainmentPage Mage_Cms_Model_Block */
$entertainmentBlock      = Mage::getModel('cms/block');

$vaultContent = "<p><h1>Discount Vault:<br/>Up to 50% off on Totsy's<br/>partner websites</h1></p>";

$entertainmentContent = "<p><h1>Entertainment Savings:<br/>Find discounted tickets<br/>near you</h1></p>";

$vaultBlock->setStores(array(0))
    ->setTitle('Discount Vault')
    ->setIdentifier('discount_vault')
    ->setIsActive(1)
    ->setContent($vaultContent)
    ->save();

$entertainmentBlock->setStores(array(0))
    ->setTitle('Entertainment Savings')
    ->setIdentifier('entertainment_savings')
    ->setIsActive(1)
    ->setContent($entertainmentContent)
    ->save();

// create main Plus category
$plusCategory->setStoreId(0)
    ->setName('Plus')
    ->setDisplayMode('PAGE')
    ->setAttributeSetId($plusCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath('1/2')
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $plusCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);
$plusCategoryPath = Mage::getModel('catalog/category')->load($plusCategory->getId())->getPath();

// create Plus/Discount Vault category
$vaultCategory->setStoreId(0)
    ->setName('Discount Vault')
    ->setDisplayMode('PAGE')
    ->setAttributeSetId($vaultCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath($plusCategoryPath)
    ->setPageLayout('Crown_Club_Category_Layout')
    ->setLandingPage($vaultBlock->getId())
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $vaultCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);
$vaultCategoryPath = Mage::getModel('catalog/category')->load($vaultCategory->getId())->getPath();

// create Plus/Discount Vault/Subscriptions category
$subscriptionCategory->setStoreId(0)
    ->setName('Subscriptions')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($subscriptionCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath($vaultCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $subscriptionCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);

// create Plus/Discount Vault/Discounts category
$discountCategory->setStoreId(0)
    ->setName('Discounts')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($discountCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath($vaultCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $discountCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);

// create Plus/Entertainment Savings category
$entertainmentCategory->setStoreId(0)
    ->setName('Entertainment Savings')
    ->setDisplayMode('PAGE')
    ->setAttributeSetId($entertainmentCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath($plusCategoryPath)
    ->setPageLayout('Crown_Club_Category_Layout')
    ->setLandingPage($entertainmentBlock->getId())
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $entertainmentCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);
$entertainmentCategoryPath = Mage::getModel('catalog/category')->load($entertainmentCategory->getId())->getPath();

// create Plus/Entertainment Savings/Movie Tickets category
$movieCategory->setStoreId(0)
    ->setName('Movie Tickets')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($movieCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath($entertainmentCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $movieCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);

// create Plus/Entertainment Savings/Theme Parks category
$parkCategory->setStoreId(0)
    ->setName('Theme Parks')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($parkCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setPath($entertainmentCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $parkCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);