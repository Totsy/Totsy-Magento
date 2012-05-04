<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
$installer = $this;
$installer->startSetup();

//Note please double check FOREIGN KEY must have exactly the same data type: Including 'unsigned'
//    In general, Magento entity_id: int(10) unsigned, store_id: smallint(5) unsigned
//'ON DELETE' and 'ON UPDATE' operation must be compatible with 'NULL/NOT NULL'
//    The lastest Magento logic will be: ON DELETE SET NULL ON UPDATE CASCADE, for maximum compatibility
//    So make sure to specify DEFAULT NULL
//Make sure FOREIGN KEY name should be unique


$installer->run("

DROP TABLE IF EXISTS {$this->getTable('speedtax_log/error')};
CREATE TABLE {$this->getTable('speedtax_log/error')} (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `event` varchar(255) NOT NULL default '',
  `message` text NOT NULL default '',
  `result_type` varchar(255) NOT NULL default '',
  `address_shipping_from` text NOT NULL default '',
  `address_shipping_to` text NOT NULL default '',
  `customer_name` varchar(255) NOT NULL default '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Speedtax Error Log';
    ");
    
$installer->run("
    DROP TABLE IF EXISTS {$this->getTable('speedtax_log/call')};
CREATE TABLE {$this->getTable('speedtax_log/call')} (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `event` varchar(255) NOT NULL default '',
  `result_type` varchar(255) NOT NULL default '',
  `invoice_num` varchar(255) NOT NULL default '',
  `gross` varchar(255) NOT NULL default '',
  `exempt` varchar(255) NOT NULL default '',
  `tax` varchar(255) NOT NULL default '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Speedtax Call Log';
    ");

$installer->endSetup();
/*
addNexusAttributeToProduct();

function addNexusAttributeToProduct() {
    $model = Mage::getModel( 'catalog/resource_eav_attribute' );
    $entityTypeId = Mage::getModel( 'eav/entity' )->setType( Mage_Catalog_Model_Product::ENTITY )->getTypeId();
    $data = array();
    $data[ 'entity_type_id' ] = $entityTypeId;
    $data[ 'attribute_code' ] = 'speedtax_origin12';
    $data[ 'is_global' ] = 1;
    $data[ 'frontend_input' ] = 'select';
    $data[ 'is_unique' ] = 1;
    $data[ 'is_configurable' ] = 0;
    $data[ 'is_filterable' ] = 0;
    $data[ 'is_filterable_in_search' ] = 0;
    $data[ 'source_model' ] = null;
    $data[ 'backend_model' ] = null;
    $data[ 'backend_type' ] = 'int';
    $data[ 'apply_to' ] = array();
    $data[ 'is_required' ] = 0;
    $data[ 'is_searchable' ] = 0;
    $data[ 'is_visible_in_advanced_search' ] = 0;
    $data[ 'is_comparable' ] = 0;
    $data[ 'is_used_for_promo_rules' ] = 0;
    $data[ 'is_html_allowed_on_front' ] = 1;
    $data[ 'is_visible_on_front' ] = 0;
    $data[ 'used_in_product_listing' ] = 0;
    $data[ 'used_for_sort_by' ] = 0;
    $data[ 'frontend_label' ] = array( 'Product Origin' );
    
    $adminStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;
    
    $attributeSets = Mage::getModel( 'catalog/resource_setup', 'core_setup' );
    $defaultAttributeSetId = $attributeSets->getDefaultAttributeSetId( $entityTypeId );
    
    $storeAddress = '';
    $configDataCollection = Mage::getModel( 'core/config_data' )->getCollection();
    $configDataCollection->getSelect()->where( 'scope_id = 0' );
    foreach( $configDataCollection as $configData ) {
        switch( $configData->getPath() ) {
            case 'shipping/origin/postcode':
                $zip = $configData->getValue();
                break;
            case 'shipping/origin/region_id':
                $regionId = $configData->getValue();
                $state = Mage::getModel ( 'directory/region' )->load ( $regionId )->getCode ();
                break;
            case 'shipping/origin/city':
                $city = $configData->getValue();
                break;
            case 'shipping/origin/street_line1':
                $street1 = $configData->getValue();
                break;
            case 'shipping/origin/street_line2':
                $street2 = $configData->getValue();
                break;
        }
    }

    $storeAddress = $street1 . ', ' . $street2 . ', ' . $city . ", " . $state . " " . $zip;
    
    $option = array( 'value' => array( array( $adminStoreId => $storeAddress ) ) );
    
    $model->setAttributeSetId( $defaultAttributeSetId );
    $model->setAttributeGroupId( $attributeSets->getAttributeGroupId( $entityTypeId, $defaultAttributeSetId, 'Prices' ) );
    $model->addData( $data );
    
    try {
        $model->save();
        $option[ 'attribute_id' ] = $model->getId();
        $attributeSets->addAttributeOption( $option );
    } catch( Exception $e ) {}
}*/