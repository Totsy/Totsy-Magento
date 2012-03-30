<?php

/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

class Harapartners_Unsubcentral_Model_Item extends Mage_Core_Model_Abstract
{
	const API_PENDING_STATUS = 0;
	const API_PRECESSED_STATUS = 1;
	const API_ERROR_STATUS = 2;
	
	const UNSUBSCRIBE_LIST = '130';  // opt out list should be 113 in live 
	const REGISTER_LIST = '142';  // should be 116 in live
	
	
	protected function _construct()
    {
    	//Point to the correct table
        $this->_init('unsubcentral/item');
    }

	public function loadByEmail($email)
    {
        $this->addData($this->getResource()->loadByEmail($email));
        return $this;
    }
}
