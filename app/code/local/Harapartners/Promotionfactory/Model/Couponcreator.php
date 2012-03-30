<?php
class Harapartners_Promotionfactory_Model_Couponcreator extends Mage_Core_Model_Abstract {
	
  public function createCoupons($total,$id){
  	
  	$coupon = Mage::getModel('salesrule/rule')->load($id);
  	$code = $coupon->getCouponCode();
  	for ($i = 1; $i <= $total; $i++) {
  		$pseudoCode = $code.$i;
  		 	$model = Mage::getModel('promotionfactory/groupcoupon');
    		$model->setData('pseudo_code',$pseudoCode);
    		$model->setData('code',$code);
    		$model->setData('rule_id',$id);
    		$model->save();
	}  	
  	return;
  }
    
}