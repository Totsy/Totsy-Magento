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

/* @var $plusRewrite Mage_Core_Model_Url_Rewrite */
$plusRewrite            = Mage::getModel('core/url_rewrite');

/* @var $vaultRewrite Mage_Core_Model_Url_Rewrite */
$vaultRewrite           = Mage::getModel('core/url_rewrite');

/* @var $subscriptionRewrite Mage_Core_Model_Url_Rewrite */
$subscriptionRewrite    = Mage::getModel('core/url_rewrite');

/* @var $discountRewrite Mage_Core_Model_Url_Rewrite */
$discountRewrite        = Mage::getModel('core/url_rewrite');

/* @var $entertainmentRewrite Mage_Core_Model_Url_Rewrite */
$entertainmentRewrite   = Mage::getModel('core/url_rewrite');

/* @var $parkRewrite Mage_Core_Model_Url_Rewrite */
$parkRewrite            = Mage::getModel('core/url_rewrite');

/* @var $movieRewrite Mage_Core_Model_Url_Rewrite */
$movieRewrite           = Mage::getModel('core/url_rewrite');

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
    ->setUrlKey('plus')
    ->setIsActive(1)
    ->setPath('1/2')
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $plusCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);
$plusCategoryPath = Mage::getModel('catalog/category')->load($plusCategory->getId())->getPath();

$plusRewrite
    ->setStoreId(1)
    ->setIdPath('category/' . $plusCategory->getId())
    ->setTargetPath('catalog/category/view/id/' . $plusCategory->getId())
    ->setRequestPath('plus.html')
    ->setCategoryId($plusCategory->getId())
    ->save();



// create Plus/Discount Vault category
$vaultCategory->setStoreId(0)
    ->setName('Discount Vault')
    ->setDisplayMode('PAGE')
    ->setAttributeSetId($vaultCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setUrlKey('discount-vault')
    ->setPath($plusCategoryPath)
    ->setPageLayout('Crown_Club_Category_Layout')
    ->setLandingPage($vaultBlock->getId())
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $vaultCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);
$vaultCategoryPath = Mage::getModel('catalog/category')->load($vaultCategory->getId())->getPath();

$vaultRewrite
    ->setStoreId(1)
    ->setIdPath('category/' . $vaultCategory->getId())
    ->setTargetPath('catalog/category/view/id/' . $vaultCategory->getId())
    ->setRequestPath('plus/discount-vault.html')
    ->setCategoryId($vaultCategory->getId())
    ->save();


// create Plus/Discount Vault/Subscriptions category
$subscriptionCategory->setStoreId(0)
    ->setName('Subscriptions')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($subscriptionCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setUrlKey('subscriptions')
    ->setPath($vaultCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $subscriptionCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);

$subscriptionRewrite
    ->setStoreId(1)
    ->setIdPath('category/' . $subscriptionCategory->getId())
    ->setTargetPath('catalog/category/view/id/' . $subscriptionCategory->getId())
    ->setRequestPath('plus/discount-vault/subscriptions.html')
    ->setCategoryId($subscriptionCategory->getId())
    ->save();


// create Plus/Discount Vault/Discounts category
$discountCategory->setStoreId(0)
    ->setName('Discounts')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($discountCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setUrlKey('discounts')
    ->setPath($vaultCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $discountCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);

$discountRewrite
    ->setStoreId(1)
    ->setIdPath('category/' . $discountCategory->getId())
    ->setTargetPath('catalog/category/view/id/' . $discountCategory->getId())
    ->setRequestPath('plus/discount-vault/discounts.html')
    ->setCategoryId($discountCategory->getId())
    ->save();


// create Plus/Entertainment Savings category
$entertainmentCategory->setStoreId(0)
    ->setName('Entertainment Savings')
    ->setDisplayMode('PAGE')
    ->setAttributeSetId($entertainmentCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setUrlKey('entertainment-savings')
    ->setPath($plusCategoryPath)
    ->setPageLayout('Crown_Club_Category_Layout')
    ->setLandingPage($entertainmentBlock->getId())
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $entertainmentCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);
$entertainmentCategoryPath = Mage::getModel('catalog/category')->load($entertainmentCategory->getId())->getPath();

$entertainmentRewrite
    ->setStoreId(1)
    ->setIdPath('category/' . $entertainmentCategory->getId())
    ->setTargetPath('catalog/category/view/id/' . $entertainmentCategory->getId())
    ->setRequestPath('plus/entertainment-savings.html')
    ->setCategoryId($entertainmentCategory->getId())
    ->save();


// create Plus/Entertainment Savings/Movie Tickets category
$movieCategory->setStoreId(0)
    ->setName('Movie Tickets')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($movieCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setUrlKey('movie-tickets')
    ->setPath($entertainmentCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $movieCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);

$movieRewrite
    ->setStoreId(1)
    ->setIdPath('category/' . $movieCategory->getId())
    ->setTargetPath('catalog/category/view/id/' . $movieCategory->getId())
    ->setRequestPath('plus/entertainment-savings/movie-tickets.html')
    ->setCategoryId($movieCategory->getId())
    ->save();


// create Plus/Entertainment Savings/Theme Parks category
$parkCategory->setStoreId(0)
    ->setName('Theme Parks')
    ->setDisplayMode('PRODUCTS')
    ->setAttributeSetId($parkCategory->getDefaultAttributeSetId())
    ->setIsActive(1)
    ->setUrlKey('theme-parks')
    ->setPath($entertainmentCategoryPath)
    ->setInitialSetupFlag(true)
    ->save();
$installer->setConfigData(Mage_Catalog_Helper_Category::XML_PATH_CATEGORY_ROOT_ID, $parkCategory->getId());
$installer->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, 'Default', 'Design', 6);

$parkRewrite
    ->setStoreId(1)
    ->setIdPath('category/' . $parkCategory->getId())
    ->setTargetPath('catalog/category/view/id/' . $parkCategory->getId())
    ->setRequestPath('plus/entertainment-savings/theme-parks.html')
    ->setCategoryId($parkCategory->getId())
    ->save();