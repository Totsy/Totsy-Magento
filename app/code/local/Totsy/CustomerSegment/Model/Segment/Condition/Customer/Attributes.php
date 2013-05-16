<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Totsy_CustomerSegment_Model_Segment_Condition_Customer_Attributes 
    extends Enterprise_CustomerSegment_Model_Segment_Condition_Customer_Attributes
{ 
     public function getDefaultOperatorInputByType()
     {
         if(null === $this->_defaultOperatorInputByType)
         {
             parent::getDefaultOperatorInputByType();
             $this->_defaultOperatorInputByType['days'] = array('within');
          }
         return $this->_defaultOperatorInputByType;
     }
     
      public function getDefaultOperatorOptions()
      {
        if (null === $this->_defaultOperatorOptions) {
            $this->_defaultOperatorOptions = parent::getDefaultOperatorOptions();
            $this->_defaultOperatorOptions['within'] = Mage::helper('rule')->__('within');
        }
        return $this->_defaultOperatorOptions;
     }
     
      public function getInputType()
    {
        if ($this->_isCurrentAttributeDefaultAddress()) {
            return 'select';
        }
        if (!is_object($this->getAttributeObject())) {
            return 'string';
        }
        $input = $this->getAttributeObject()->getFrontendInput();
        switch ($input) {
            case 'boolean':
                return 'select';
            case 'select':
            case 'multiselect':
            case 'date':
                return $input;
              case 'days':
                return $input;   
            default:
                return 'string';
        }
    }
    
      public function loadArray($arr)
    {
        if('created_days' == $arr['attribute']){
            $arr['operator'] = 'within' ;
        }
        $this->setType($arr['type']);
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $this->setOperator(isset($arr['operator']) ? $arr['operator'] : false);
        $this->setValue(isset($arr['value']) ? $arr['value'] : false);
        $this->setIsValueParsed(isset($arr['is_value_parsed']) ? $arr['is_value_parsed'] : false);
        
        return $this;
    }
    
     public function getConditionsSql($customer, $website)
    {
        $attribute = $this->getAttributeObject();
        $table = $attribute->getBackendTable();
        $addressTable = $this->getResource()->getTable('customer/address_entity');

        $select = $this->getResource()->createSelect();
        $select->from(array('main'=>$table), array(new Zend_Db_Expr(1)));

        $select->where($this->_createCustomerFilter($customer, 'main.entity_id'));
        Mage::getResourceHelper('enterprise_customersegment')->setOneRowLimit($select);

        if (!in_array($attribute->getAttributeCode(), array('default_billing', 'default_shipping')) ) {
            $value    = $this->getValue();
            if('created_days' == $attribute->getAttributeCode()){
                  $operator = '>=';
            }else{
                 $operator = $this->getOperator();
            }
            if ($attribute->isStatic()) {
                $field = "main.{$attribute->getAttributeCode()}";
            } else {
                $select->where('main.attribute_id = ?', $attribute->getId());
                $field = 'main.value';
            }
            $field = $select->getAdapter()->quoteColumnAs($field, null);
           
            if ($attribute->getFrontendInput() == 'date') {
                $value    = $this->getDateValue();
                $operator = $this->getDateOperator();
            }
            $condition = $this->getResource()->createConditionSql($field, $operator, $value);
            if('customer_entity' == $table)
            {
                 if("`main`.`created_days`" == $field)
                 {
                   $field = "`root`.`created_at`";
                   $value = "date_add(now(),INTERVAL -" . $value . " day)";
	           $condition = $field.' '.$operator.' '. $value;
                 }else{
                   $condition = str_replace("`main`","`root`",$condition);
                 }
                return $condition;
            }
            $select->where($condition);
        } else {
            $joinFunction = 'joinLeft';
            if ($this->getValue() == 'is_exists') {
                $joinFunction = 'joinInner';
            } else {
                $select->where('address.entity_id IS NULL');
            }
            $select->$joinFunction(array('address'=>$addressTable), 'address.entity_id = main.value', array());
            $select->where('main.attribute_id = ?', $attribute->getId());
        }
        return $select;
    }
    
}

?>
