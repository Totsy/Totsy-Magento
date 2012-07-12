<?php
class Harapartners_EmailFactory_Model_Sailthruqueue extends Mage_Core_Model_Abstract {

	public function __construct(){
		$this->_init('emailfactory/sailthruqueue');
	}
	
	/*
	 * experitng to see reqired params:
	 * class
	 * method
	 * params
	 */
	public function addToQueue ($data=array()){
		
		$this->setData(array(
			'call' => serialize($data['call']),
			'params' => serialize($data['params']),
			'created_at' => now(),
			'status' => 'pending',
			'additional_calls' => isset($data['additional_calls'])?serialize($data['additional_calls']):null		
		));
		$this->save();
	}
	
	public function processQueue(&$details){
		$this->_setStatus($details, 'running');
		$obj = Mage::getModel($details['call']['class']);
		$result = null;
		foreach ($details['call']['methods'] as $method){
			if (array_key_exists($method, $details['params'])){
				$result = call_user_func_array(array($result,$method), array_values($details['params'][$method]));
			} else {
				$result = $obj->{$method}();
			}	
		}
		$details['response'] = $result;

		if (is_array($result) && (count($result) >= 2 || !empty($result['purchase']))){
			if (!empty($details['additional_calls'])){
				$acs = unserialize($details['additional_calls']);
				foreach ($acs as $ac){
					// check params
					if (!empty($ac['params'])){
						foreach ($ac['params'] as $m=>$p){
							if (array_key_exists('#useExtVar#', $p)){
								$ac['params'][$m] =  $this->_getArrayByPath(
										${$p['#useExtVar#']['name']}, 
										$p['#useExtVar#']['elements']
								);
							}
						}
					}
					$this->_procesAdditionalData($ac);
				}
			}
			$this->_setStatus($details, 'done');
		} else {
			$this->_setStatus($details, 'pending');
		}
	}
	
	public function getQueueListIds () {
		return $this->getCollection()
				->addFieldToSelect('id')
				->addFieldToFilter('status','pending')
				->setCurPage(1)
		    	->setPageSize(500)
				->load()
				->toArray();
	}
	
	public function getQueueDetails($id){
		$details = $this->getCollection()
				->addFieldToFilter('id',$id)
				->load()
				->toArray();
		if (empty($details['totalRecords'])){
			return array();
		}

		$details = $details['items'][0];
		$details['call'] = unserialize($details['call']);
		$details['params'] = unserialize($details['params']);
		return $details;
	}
	
	private function _procesAdditionalData($details){
		$obj = Mage::getModel($details['call']['class']);
		$result = null;
		foreach ($details['call']['methods'] as $method){
			if (array_key_exists($method, $details['params'])){
				$result = call_user_func_array(array($result,$method), array_values($details['params'][$method]));
			} else {
				$result = $obj->{$method}();
			}
		}
		return $result;
	}
	
	private function _setStatus(&$details,$status){

		$updated = unserialize($details['stats']);
		$updated[] = array(
						'status' => $status,
						'datetime'=>now(),
						'machine'=>php_uname('n'),
						'response' => empty($details['response'])?'':$details['response']
					);
		$this->addData(array(
				'status' => $status,
				'stats' => serialize($updated),
				'updated_at' => now()
		));
		$this->setId($details['id'])->save();
	}
	
	private function _getArrayByPath($data, $path){

		$found = true;
		$path = explode("/", $path);
		for ($x=0; $x < count($path) ; $x++){
			$key = $path[$x];
			if (isset($data[$key])){
				$data = $data[$key];
			} else { 
				$found = false;
			}
		}
		if ($found === true){
			return $data;
		} else {
			return false;
		}
	}
}