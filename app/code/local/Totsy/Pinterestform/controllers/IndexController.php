<?php
class Totsy_Pinterestform_IndexController extends Mage_Core_Controller_Front_Action{

	public function indexAction() {
        $this->loadLayout()->renderLayout();
	}
	
	public function indexPostAction() {
		$to      = 'jwidro@totsy.com';
		$subject = 'pinterest fever';
		$totsyemail = $this->getRequest()->getParam('totsyemail');
		$region = $this->getRequest()->getParam('region');
		$pinterestuser = $this->getRequest()->getParam('pinterestuser');
		
		$message ="
		email - $totsyemail
		
		region - $region
		
		pinterest - $pinterestuser
		";
		
		$headers = 'From: ' . $totsyemail . "\r\n" .
		    'X-Mailer: PHP/' . phpversion();
		
		mail($to, $subject, $message, $headers);
		
		$this->_redirect('pinterestform/index/success');
	}
	
	public function successAction() {
        $this->loadLayout()->renderLayout();
	}
}