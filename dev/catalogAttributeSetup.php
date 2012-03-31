<?php 

// ========== Init setup ========== //
require_once( '../app/Mage.php' );
Mage::app();
define('DEFAULT_STORE_ID', Mage_Core_Model_App::DISTRO_STORE_ID);
define('ADMIN_STORE_ID', Mage_Core_Model_App::ADMIN_STORE_ID);

$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
define('CATEGORY_ENTITY_TYPE_ID', $setup->getEntityTypeId('catalog_category'));
define('CATEGORY_DEFAULT_ATTRIBUTE_SET_ID', $setup->getAttributeSetId(CATEGORY_ENTITY_TYPE_ID, 'Default'));
define('PRODUCT_ENTITY_TYPE_ID', $setup->getEntityTypeId('catalog_product'));
define('PRODUCT_DEFAULT_ATTRIBUTE_SET_ID', $setup->getAttributeSetId(PRODUCT_ENTITY_TYPE_ID, 'Default'));


// ========== Client specific logic ========== //

// Totsy, Enterprise 1.11.1.0
$categoryGeneralInfoGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'General Information');
$categoryDisplaySettingsGroupId = $setup->getAttributeGroupId(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, 'Display Settings');

// ---- Updating default values -----//
$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'name', '', '', 100); //quick trick to update sort order within attribute group

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'is_active', '', '', 110);

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'description', 'frontend_label', 'Blurb');
$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'description', 'is_required', 1);
$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'description', '', '', 120);

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'include_in_menu', 'is_required', 0);
$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'include_in_menu', 'default_value',  null);

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'image', 'frontend_label', 'Large Splash Image');
$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'image', '', '', 100); 
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, 'image');

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'thumbnail', 'frontend_label', 'Event Image');
$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'thumbnail', '', '', 120); 
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, 'thumbnail');

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'url_key', '', '', 140); 
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, 'url_key');

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'meta_title', '', '', 150); 
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, 'meta_title');

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'meta_keywords', '', '', 160); 
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, 'meta_keywords');

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'meta_description', '', '', 170); 
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, 'meta_description');

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'include_in_menu', '', '', 180); 
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, 'include_in_menu');

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'is_anchor', '', '', 190); 

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'display_mode', '', '', 200); 

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'landing_page', '', '', 210); 

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'available_sort_by', '', '', 220);

$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'default_sort_by', '', '', 230);
 
$setup->updateAttribute(CATEGORY_ENTITY_TYPE_ID, 'filter_price_range', '', '', 240); 

// ---- Adding new attributes ----- //

// ~~~~~ General Information ~~~~~ //
$attrCode = 'short_description';
$attrSetting = array(
	'label' => 'Short',
	'type' => 'text',
	'input' => 'text',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 130
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'is_virtual_event';
$attrSetting = array(
	'label' => 'Is Virtual Event',
	'type' => 'int',
	'input' => 'select',
	'source' => 'eav/entity_attribute_source_boolean',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 140
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'enable_preview';
$attrSetting = array(
	'label' => 'Enable Preview',
	'type' => 'int',
	'input' => 'select',
	'source' => 'eav/entity_attribute_source_boolean',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 150
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'is_clearance';
$attrSetting = array(
	'label' => 'Is Clearance',
	'type' => 'int',
	'input' => 'select',
	'source' => 'eav/entity_attribute_source_boolean',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 160
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'event_start_date';
$attrSetting = array(
	'label' => 'Event Start Date',
	'type' => 'datetime',
	'input' => 'datetime', //special input type, need Varien_Data_Form_Element_Datetime
	'backend' => 'eav/entity_attribute_backend_datetime',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 170,
	'note' => 'Based on store timezone, not forced into UTC.'
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'event_end_date';
$attrSetting = array(
	'label' => 'Event End Date',
	'type' => 'datetime',
	'input' => 'datetime', //special input type, need Varien_Data_Form_Element_Datetime
	'backend' => 'eav/entity_attribute_backend_datetime',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 180,
	'note' => 'Based on store timezone, not forced into UTC.'
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'departments';
$attrSetting = array(
	'label' => 'Departments',
	'type' => 'text',
	'input' => 'multiselect',
	'source' => 'catalog/category_attribute_source_departments',
	'backend' => 'eav/entity_attribute_backend_array',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 190
);

$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'ages';
$attrSetting = array(
	'label' => 'Ages',
	'type' => 'text',
	'input' => 'multiselect',
	'source' => 'catalog/category_attribute_source_ages',
	'backend' => 'eav/entity_attribute_backend_array',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 195
);


$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);

$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);
$attrCode = 'tags';
$attrSetting = array(
	'label' => 'Tags',
	'type' => 'text',
	'input' => 'multiselect',
	'source' => 'catalog/category_attribute_source_tags',
	'backend' => 'eav/entity_attribute_backend_array',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 200
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'shipping_message';
$attrSetting = array(
	'label' => 'Shipping Message',
	'type' => 'text',
	'input' => 'textarea',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 210
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

$attrCode = 'estimated_shipping_date';
$attrSetting = array(
	'label' => 'Estimated Shipping Date',
	'type' => 'datetime',
	'input' => 'date',
	'backend' => 'eav/entity_attribute_backend_datetime',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 220,
	'note' => 'TODO: This date will override the calcualted ship date for orders.'
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryGeneralInfoGroupId, $attrCode);

// ~~~~~ Display Settings ~~~~~ //
$attrCode = 'small_image';
$attrSetting = array(
	'label' => 'Small Splash Image',
	'type' => 'varchar',
	'input' => 'image',
	'backend' => 'catalog/category_attribute_backend_image',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 110
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, $attrCode);

$attrCode = 'logo';
$attrSetting = array(
	'label' => 'Logo',
	'type' => 'varchar',
	'input' => 'image',
	'backend' => 'catalog/category_attribute_backend_image',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 130
);
$setup->addAttribute(CATEGORY_ENTITY_TYPE_ID, $attrCode, $attrSetting);
$setup->addAttributeToGroup(CATEGORY_ENTITY_TYPE_ID, CATEGORY_DEFAULT_ATTRIBUTE_SET_ID, $categoryDisplaySettingsGroupId, $attrCode);

echo 'Setup complete!';
exit;




// ========== Some useful code ========== //
$attrCode = 'sample_type';
$attrSetting = array(
	'label' => 'Sample Type',
	'type' => 'int',
	'input' => 'select',
	'required'=> 0,
	'user_defined' => 1,
	'sort_order' => 100,
);
$optionArray = array(
	array(
		'order' => array( 0 => 0),
		'value' => array(
			array($adminStoreId => 'None', $defaultStoreId => 'None')
		)
	),
	array(
		'order' => array( 0 => 1),
		'value' => array(
			array($adminStoreId => 'Free', $defaultStoreId => 'Free')
		)
	),
	array(
		'order' => array( 0 => 2),
		'value' => array(
			array($adminStoreId => 'Coupon', $defaultStoreId => 'Coupon')
		)
	)
);
$setup->addAttribute($defaultcatalogProductEntityTypeId, $attrCode, $attrSetting);
$setup->addAttributeToSet($defaultcatalogProductEntityTypeId, $tempAttrSet, $tempAttrGroup, $attrCode, $attrSetting['sort_order']);

$attributeId = $setup->getAttributeId($defaultcatalogProductEntityTypeId, $attrCode);
foreach($optionArray as $option){
	$option['attribute_id'] = $attributeId;
	$setup->addAttributeOption($option);	
}