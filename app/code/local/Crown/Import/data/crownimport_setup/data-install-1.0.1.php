<?php
/**
 * 
 * @category 	Crown
 * @package 	Crown_Import 
 * @since 		1.0.1
 */

/* @var $this Crown_Import_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();

/**
 * Product Import Settings
 */
$productProfileData = array(
	'title' 			=> 'Product Import',
	'profile_type' 		=> 'import',
	'profile_status' 	=> 'enabled',
	'data_type' 		=> 'product',
	'filename' 			=> 'non.csv',
	'store_id' 			=> '0',
	'options' 			=> array(
							'import' 	=> array(
											'dryrun' 				=> '0',
											'reindex_type' 			=> 'realtime',
											'create_options' 		=> '1',
											'image_files' 			=> '1',
											'image_files_remote' 	=> '1',
							),
							'log'		=> array(
											'min_level'				=> 'SUCCESS',
							),
	),
	
);

/**
 * Product Extra Import Settings
 */
$productExtraProfileData = array(
	'title' 			=> 'Product Extra Import',
	'profile_type' 		=> 'import',
	'profile_status' 	=> 'enabled',
	'data_type' 		=> 'product_extra',
	'filename' 			=> 'non.csv',
	'store_id' 			=> '0',
	'options' 			=> array(
							'log'		=> array(
											'min_level'				=> 'SUCCESS',
							),
	),
);


/**
 * Product Import Settings Save and Create
 */
$productProfile = Mage::getModel ( 'urapidflow/profile' );

if (! isset ( $productProfileData ['columns_post'] )) {
	$productProfileData ['columns_post'] = array ();
}
if (isset ( $productProfileData ['conditions'] )) {
	$productProfileData ['conditions_post'] = $productProfileData ['conditions'];
	unset ( $productProfileData ['conditions'] );
}
if (isset ( $productProfileData ['options'] ['reindex'] )) {
	$productProfileData ['options'] ['reindex'] = array_flip ( $productProfileData ['options'] ['reindex'] );
}
if (isset ( $productProfileData ['options'] ['refresh'] )) {
	$productProfileData ['options'] ['refresh'] = array_flip ( $productProfileData ['options'] ['refresh'] );
}
$productProfile->addData ( $productProfileData );
$productProfile = $productProfile->factory ();

if ($productProfile->getCreatedTime == NULL || $productProfile->getUpdateTime () == NULL) {
	$productProfile->setCreatedTime ( now () )->setUpdateTime ( now () );
} else {
	$productProfile->setUpdateTime ( now () );
}

$productProfile->save ();

/**
 * Product Extra Import Settings Save and Create
 */
$productExtraProfile = Mage::getModel ( 'urapidflow/profile' );

if (! isset ( $productExtraProfileData ['columns_post'] )) {
	$productExtraProfileData ['columns_post'] = array ();
}
if (isset ( $productExtraProfileData ['conditions'] )) {
	$productExtraProfileData ['conditions_post'] = $productExtraProfileData ['conditions'];
	unset ( $productExtraProfileData ['conditions'] );
}
if (isset ( $productExtraProfileData ['options'] ['reindex'] )) {
	$productExtraProfileData ['options'] ['reindex'] = array_flip ( $productExtraProfileData ['options'] ['reindex'] );
}
if (isset ( $productExtraProfileData ['options'] ['refresh'] )) {
	$productExtraProfileData ['options'] ['refresh'] = array_flip ( $productExtraProfileData ['options'] ['refresh'] );
}
$productExtraProfile->addData ( $productExtraProfileData );
$productExtraProfile = $productExtraProfile->factory ();

if ($productExtraProfile->getCreatedTime == NULL || $productExtraProfile->getUpdateTime () == NULL) {
	$productExtraProfile->setCreatedTime ( now () )->setUpdateTime ( now () );
} else {
	$productExtraProfile->setUpdateTime ( now () );
}

$productExtraProfile->save ();

/**
 * Set default uRapidFlowProfiles
 */
Mage::getConfig()->saveConfig('crownimport/urapidflow/profile_product_extra', $productExtraProfile->getId());
Mage::getConfig()->saveConfig('crownimport/urapidflow/profile', $productProfile->getId());
Mage::getConfig()->reinit();
Mage::app()->reinitStores();

$installer->endSetup();

