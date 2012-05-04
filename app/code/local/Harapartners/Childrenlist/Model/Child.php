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

class Harapartners_Childrenlist_Model_Child extends Mage_Core_Model_Abstract {
    const CHILD_GENDER_CODE_UNKNOWN = 0;
    const CHILD_GENDER_CODE_BOY = 1;
    const CHILD_GENDER_CODE_GIRL = 2;
    /*we can find the relationship by customer and child's gender*/
    /*
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_UNKONWN = 0;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_MOM_SON = 1;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_DAD_SON = 2;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_MOM_DAUGHTER = 3;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_DAD_DAUGHTER = 4;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_GRANDMA_GRANDSON = 5;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_GRANDPA_GRANDSON = 6;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_GRANDMA_GRANDDAUGHTER = 7;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_GRANDPA_GRANDDAUGHTER = 8;
    */
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_UNKNOWN = 0;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_PARENT_CHILD = 1;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_GRANDPARENT_GRANDCHILD = 2;
    const CHILD_CUSTOMER_RELATIONSHIP_CODE_OTHERS = 3;
    
    public static function getChildGenderLabels(){
        return array(self::CHILD_GENDER_CODE_UNKNOWN => '',
                self::CHILD_GENDER_CODE_BOY => 'Boy',
                self::CHILD_GENDER_CODE_GIRL => 'Girl' 
        );
    }
    
    public static function getChildRelationshipLabels(){
        return array(self::CHILD_CUSTOMER_RELATIONSHIP_CODE_UNKNOWN => '',
                self::CHILD_CUSTOMER_RELATIONSHIP_CODE_PARENT_CHILD => 'Parent/Child',
                self::CHILD_CUSTOMER_RELATIONSHIP_CODE_GRANDPARENT_GRANDCHILD => 'Grandparent/Grandchild',
                self::CHILD_CUSTOMER_RELATIONSHIP_CODE_OTHERS => 'Others'
        );
    }
    
    protected function _construct(){
        //Point to the correct table
        $this->_init('childrenlist/child');
    }
    
    protected function _beforeSave(){
        if(!$this->getId()){
            $this->setData('created_at', now());
        }
        $this->setData('updated_at', now());
        if(!$this->getStoreId()){
            $this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
        parent::_beforeSave();  
    }
    
    public function validateAndLoadData($data){
        $this->addData($data);
        if(!$this->getChildName()){
            throw new Exception('Please specify child name!');
        }
        if(!!$this->getAdditionalData() 
                && !json_decode($this->getAdditionalData())){
            throw new Exception('Additional data must be a valid JSON string!');    
        }
        return $this;
    }

}