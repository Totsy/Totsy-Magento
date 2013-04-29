<?php

class Totsy_Sailthru_Model_Feedconfig extends Mage_Core_Model_Abstract {

    protected function _construct(){
        $this->_init('sailthru/feedconfig');
    }

    public function importData($data){
        //Type casting
        if(is_array($data)){
            $data = new Varien_Object($data);
        }
        if(!($data instanceof Varien_Object)){
            throw new Exception('Invalid type for data importing, Array or Varien_Object expected.');
        }

        //Forcefully overwrite existing data, certain data may need to be removed before this step
        $this->addData($data->getData());

        return $this;
    }

    protected function _beforeSave(){
        parent::_beforeSave();

        $this->validate(); //Errors will be thrown as exceptions
        if (!$this->hasHash()){
        	$this->setHash(sha1($this->toJson()));
    	}
        return $this;
    }

	public function validate(){
        
        //Validate type
        if(!$this->hasType()){
            throw new Exception('Type is required!');
        }
        if( strlen($this->getData('type'))>1){
            throw new Exception('Type can not be longer than 1 character. Error value: ' . $this->getData('type'));
        }
        if(!preg_match("/^[0-1]+$/", $this->getData('type'))){
            throw new Exception('Type must be boolean. Error value: ' . $this->getData('type'));
        }

        //Validate order
        if(!$this->hasOrder()){
            throw new Exception('Order is required!');
        }
        if( strlen($this->getData('order'))>1){
            throw new Exception('Is Order can not be longer than 1 character. Error value: ' . $this->getData('order'));
        }
        if(!preg_match("/^[0-5]+$/", $this->getData('order'))){
            throw new Exception('Type must be boolean. Error value: ' . $this->getData('order'));
        }

        //Validate start_at_day
		if (!$this->hasData('start_at_day') && $this->getData('type')==0){
            throw new Exception('Start Date is required!');
        }
        if($this->hasData('start_at_day')){
            try{
                $date = new DateTime($this->getData('start_at_day'));
            } catch (Exception $e){
                throw new Exception('Start date is invalid. '.$e->getMessage().'. Error value: ' . $this->getData('start_at_day')); 
            }
        }

        //Validate start_at_time
        if($this->hasData('start_at_time') && $this->getData('start_at_time')=='-1'){
            $this->unsetData('start_at_time');
        }
        if($this->hasData('start_at_time')){

            if ( !preg_match('/[\d\:apmAPM]+/', $this->getData('start_at_time'))){
                throw new Exception('Time contains not allowed characters. Error value: ' . $this->getData('start_at_time'));   
            }

            $time = trim($this->getData('start_at_time'));
            if (strtolower($time) == 'am'){
            	$time = '09:00';
            }
            if (strtolower($time) == 'pm'){
            	$time = '20:00';
            }
            $time = explode(':', $time);

            if ( count($time)!=2 ){
                throw new Exception('Time field allowed to contain hours and minutes only. Error value: ' . $this->getData('start_at_time'));   
            }

            if ( intval($time[0])<0 || intval($time[0])>24 ){
            	throw new Exception('Space does not exist for this time ;). Seriously, time is out of range. Error value: ' . $this->getData('start_at_time'));   	
            }
			if ( intval($time[1])<0 || intval($time[1])>60 ){
            	throw new Exception('This time does not have space ;). Seriously, time is out of range. Error value: ' . $this->getData('start_at_time'));   	
            }
        }

        //Validate include
        if (!$this->hasData('include') && $this->getData('type')==1){
            throw new Exception('Include is required for products feed!');
        }
        $this->_validateList('include');
        
        //Validate exclude
        $this->_validateList('exclude');
        
        //Validate filter
        $this->_validateList('filter','\d\n\=\_\,');

        return $this;
    }

    protected function _validateList($key,$allowed='\d\n\,'){
        $at = array();

        if(!$this->hasData($key)){
            return;
        }
        
        if (preg_match('/[^'.$allowed.']/', $this->getData($key))){
            $title = str_replace('_', ' ', $this->getData($key));
            $title = ucwords($title);
            throw new Exception(
                $title.
                ' can contain only new line, ',' and any numeric characters. '.
                'Error value: ' . $this->getData($key)
            );
        }

        if ( preg_match("/[\n]/", $this->getData($key))){
            $at = explode("\n",$at);
        }
        if (empty($at)){
            $at = $this->getData($key);
        }
        $at = explode(',', $at);
        if (empty($at) || !is_array($at)){
            return;
        }
        $ats = $at;
        $at = array();
        foreach ($ats as $t){
            $t = trim($t);
            if (empty($t) || !is_numeric($t)){
                continue;
            }
            $at[] = $t;
        }
        if (empty($at)){
            return;
        }
        $this->setData($key,implode(',', $at));
    }
 }