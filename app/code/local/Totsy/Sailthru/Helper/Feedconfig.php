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

}

?>