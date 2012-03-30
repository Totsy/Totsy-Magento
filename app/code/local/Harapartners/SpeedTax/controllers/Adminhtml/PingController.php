<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license [^]
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
 */
class Harapartners_SpeedTax_Adminhtml_PingController extends Mage_Adminhtml_Controller_Action {
	
	public function pingAction() {
		try {
			Mage::helper ( 'speedtax' )->loadSpeedTaxLibrary ();
			// call the operation
			$stx = new SpeedTax ();
			$response = $stx->Ping ();
			
			if($response->return =="pong"){
				Mage::getSingleton('core/session')->addNotice("Your SpeedTax account has been validated. You are now connected with SpeedTax");
			}
		
		} catch ( Exception $e ) {
			// in case of an error, process the fault
			/*if ($e instanceof WSFault) {
				Mage::getSingleton('core/session')->addNotice( "Soap Fault: %s\n". $e->Reason );
			} else {
				Mage::getSingleton('core/session')->addNotice( "Message = %s\n". $e->getMessage () );
			}*/
			Mage::getSingleton('core/session')->addNotice("Your SpeedTax account could not be validated. Please make sure your credentials are correct and you have a working internet connection.");
		}
		$this->_redirect("adminhtml/system_config/index");
	}
	
	/*protected function _sendResponse($fileName, $content, $contentType = 'application/octet-stream') {
		$response = $this->getResponse ();
		$response->setHeader ( 'HTTP/1.1 200 OK', '' );
		$response->setHeader ( 'Pragma', 'public', true );
		$response->setHeader ( 'Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true );
		$response->setHeader ( 'Content-Disposition', 'attachment; filename=' . $fileName );
		$response->setHeader ( 'Last-Modified', date ( 'r' ) );
		$response->setHeader ( 'Accept-Ranges', 'bytes' );
		$response->setHeader ( 'Content-Length', strlen ( $content ) );
		$response->setHeader ( 'Content-type', $contentType );
		$response->setBody ( $content );
		$response->sendResponse ();
		exit ();
	}*/

}