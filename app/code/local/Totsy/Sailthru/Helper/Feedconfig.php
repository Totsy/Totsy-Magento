<?php

class Totsy_Sailthru_Helper_Feedconfig {
	
	public static function mapTypes(){
		return array(
			'Events',
			'Products'
		);
	}

	public static function mapOrders(){
		return array(
			'Event DESC',
			'Event ASC',
			'Product sales DESC',
			'Product sales ASC',
			'Event & Product sales DESC',
			'Event & Product sales ASC',
		);
	}

	public static function mapTimeSelect(){
		$times = array( 
			array('value'=>'-1', 'label' => 'Plese select ...'),
			array('value'=>'am', 'label' => 'AM'),
			array('value'=>'pm', 'label' => 'PM')
		);

		for( $i=0; $i<24; $i++ ){
			$times[] = array( 'value'=>$i.':00', 'label' => $i.':00');
			$times[] = array( 'value'=>$i.':30', 'label' => $i.':30');
		}
		return $times;
	}

	public static function mapTypesSelect(){
		return self::toLabels(array_merge(
			array('Please Select...'),
			self::mapTypes()
		));
	}

	public static function mapOrdersSelect(){
		return self::toLabels(array_merge(
			array('Please Select ...'),
			self::mapOrders()
		));
	}

	protected static function toLabels($array){
		$return  = array();
		foreach($array as $k=>$r){
			$return[] = array(
				'value'=>$k-1,
				'label' => $r
			);
		}
		return $return;
	}

}

?>