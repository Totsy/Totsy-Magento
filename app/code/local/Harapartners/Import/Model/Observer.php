<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Import_Model_Observer
{
	
	public function processBatch($observer)
    {
    	$data = $observer->getEvent()->getData();
    	$file = Mage::getBaseDir('var').DS.'import'.DS.$data['import_filename'];
    	$attributes = $this->getSelectMultiselectAttributes();
    	$duplicateAttributes = array();
    	/*****************************
		 * Read CSV File START
		 *****************************/
    	$position = 0; 	//cell position
    	$row = 0;		//row counter
		if (($handle = fopen($file, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 4096, ',' , '"')) !== FALSE) {
				$num = count($data);
			    $row++; 	
				//$lines = count(file($importCsvFile));
				
				//$row = 1 is header of CSV file.  Will collect attribute names from this row
				if($row==1){
					foreach($data as $attributeCode){
						$attributeCode = trim($attributeCode);  //Clean whites spaces
						if(array_key_exists($attributeCode, $attributes)){
							//$key = array_search($attributeCode,array_keys($attributeNames));
							$attributeCodePosition[$attributeCode] = $position;
							$position = $position + 1;
						}else{
							//throw new Exception('Attribute Name: ' . $attributeCode. ' does not exists!');
							$position = $position + 1;
							continue;	
						}	
					}
					continue;   
				}
				if($attributeCodePosition){
					foreach ($attributeCodePosition as $attributeCode=>$dataCell){
						if(!isset($data[$dataCell]) || $data[$dataCell] === ''){
							continue;
						}
						$attributeOptionExists = false;
						if($attributes[$attributeCode]['frontend_input'] == 'multiselect'){
							$optionLabelArray = explode(',', trim($data[$dataCell]));
							foreach ($optionLabelArray as $optionLabel){
								$optionLabel = trim($optionLabel);
								foreach ($attributes[$attributeCode]['options'] as $attributeOption){
									$attributeOptionExists = false;
									if($attributeOption['label'] == $optionLabel){
										$attributeOptionExists = true;
										break;
									}
								}
							}
						
						}else{
							foreach ($attributes[$attributeCode]['options'] as $attributeOption){
								$optionLabel = trim($data[$dataCell]);
								if($attributeOption['label'] == $optionLabel){
									$attributeOptionExists = true;
									break;
								}
							}
						}
						if(!$attributeOptionExists && !in_array($optionLabel, $duplicateAttributes)){
							$this->addAttributeOption($attributeCode, $optionLabel);
							$duplicateAttributes[] = $optionLabel;
						}
					}
				}
			}
		}
		
		/*****************************
		 * Read CSV File End
		 *****************************/
    	$a = 1;
    }
    
    public function getSelectMultiselectAttributes(){
    	$attributes = Mage::getResourceModel('eav/entity_attribute_collection')->setEntityTypeFilter(4)->addSetInfo()->getData();
    	foreach($attributes as $attribute){
    		if(($attribute['frontend_input'] == 'multiselect' || $attribute['frontend_input'] == 'select') /*&& $attribute['is_user_defined'] == '1'*/){
    			$attributeLibrary[$attribute['attribute_code']] = array('options' => Mage::getModel('catalog/product')->getResource()->getAttribute($attribute['attribute_code'])->getFrontend()->getSelectOptions(),
    																	'frontend_input'=>$attribute['frontend_input']);	
    		}
		}
		return $attributeLibrary;
    }
    
		
    public function addAttributeOption($attributeCode, $optionLabel){
    		
    	$attributeModel = Mage::getModel('eav/entity_attribute');
		$entityAttributeModel = Mage::getModel('eav/entity_attribute');
		$resourceEavAttributeModel = Mage::getModel('catalog/resource_eav_attribute');
    
    	$attributeId = $entityAttributeModel->getIdByCode('catalog_product', $attributeCode);
    	//$attribute = $resourceEavAttributeModel->load($attributeId);
    	$attribute = $attributeModel->load($attributeId);
    	$value['option'] = array($optionLabel, $optionLabel);
    	$result = array('value' => $value);
    	$attribute->setData('option',$result);
    	$attribute->save();
    }
}
