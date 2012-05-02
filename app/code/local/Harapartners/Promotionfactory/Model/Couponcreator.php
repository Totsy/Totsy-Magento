<?php
class Harapartners_Promotionfactory_Model_Couponcreator extends Mage_Core_Model_Abstract {
	
	public function createCoupons($total, $id, $offset = 0){
		$coupon = Mage::getModel('salesrule/rule')->load($id);
		$code = $coupon->getCouponCode();
		for ($i = $offset; $i < $offset + $total; $i++) {
			$pseudoCode = strtoupper(base_convert(md5($code.$i), 16, 36));
			$model = Mage::getModel('promotionfactory/groupcoupon');
			$model->setData('pseudo_code', $pseudoCode);
			$model->setData('code', $code);
			$model->setData('rule_id', $id);
			$model->save();
		}		
		return;
	}
}