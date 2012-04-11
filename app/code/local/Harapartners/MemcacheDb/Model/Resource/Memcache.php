<?php

class Harapartners_MemcacheDb_Model_Resource_Memcache {
	
	//This connection should be used as a singleton
	protected $_memcache = null;
	protected $_history = array(); //In case we want to reduce the number of reads, Be careful when reading from the history!
	protected $_allowReconnect = false; //only try to connect once!
	protected $_hasConnection = false;
	
    public function __construct(){
    	$this->_initMemcache();
    	//note the connection is automatically closed after script execution, so __destruct() is not necessary
    }
    
    protected function _initMemcache(){
    	//Only used for Frontend, for Backend actions, always use the default method for safety
    	if(Mage::app()->getStore()->isAdmin()){
    		return;
    	}
    	try{
    		if(!class_exists(Memcache)){
    			$this->_memcache = null;
    			return;
    		}
    		
    		$this->_memcache = new Memcache;
    		//By default use the first server available
			$this->_hasConnection = $this->_memcache->connect(
					(string)Mage::getConfig()->getNode('global/cache/memcached/servers/server/host'), 
					(string)Mage::getConfig()->getNode('global/cache/memcached/servers/server/port')
			);
			if(!$this->_hasConnection){
				$this->_memcache = null;
			}
    	}catch(Exception $e){
    		$this->_memcache = null;
    	}
    }
    
    public function hasConnection(){
    	return $this->_hasConnection;
    }
    
	public function getMemcache(){
    	if($this->_allowReconnect && !$this->_memcache){
    		$this->_initMemcache();
    	}
    	return $this->_memcache;
    }
    
    public function read($key, $flags = null, $readFromHistory = false){
    	if($readFromHistory 
    			&& isset($this->_history[$key])
    			&& !!$this->_history[$key]){
    		return $this->_history[$key];
    	}
    	if(!$this->getMemcache()){
    		return null;
    	}
    	return $this->_memcache->get($key, $flags);
    }
    
	public function write($key, $var, $flag = null, $expire = 0, $writeToHistory = true){
    	if(!$this->getMemcache()){
    		return false;
    	}
    	$isSuccess = $this->_memcache->set($key, $var, $flag, $expire);
    	if($isSuccess && $writeToHistory){
    		$this->_history[$key] = $var;
    	}
    	return $isSuccess;
    }
    
    public function delete($key){
    	if(!!$this->getMemcache()){
    		$this->_memcache->delete($key);
    	}
    	return true;
    }
    
}