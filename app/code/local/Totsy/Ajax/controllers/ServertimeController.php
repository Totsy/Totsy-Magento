<?php

class Totsy_Ajax_ServertimeController extends Mage_Core_Controller_Front_Action
{
    public function servertimeAction()
    {
       $format = $this->getRequest()->getParam('format');
       switch($format) {
            case 'milliseconds':
                $servertime = Mage::getModel('core/date')->timestamp(time());
                break;
            case 'string':
                $servertime = Mage::getModel('core/date')->timestamp(time());
                $servertime = date('F d,Y H:i:s:u', $servertime);
                break;
       }
        echo json_encode(array( 'time' => $servertime));
        exit();
    }
}


?>