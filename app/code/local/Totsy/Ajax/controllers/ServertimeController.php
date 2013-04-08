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
                $servertime = date('M j, Y H:i:s O', $servertime);
                break;
       }
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
        header("Expires: Fri, 1 Jan 2013 00:00:00 GMT"); // Date in the past
        header("Content-Type: text/plain; charset=utf-8"); // MIME type
        echo json_encode(array( 'time' => $servertime));
        exit();
    }
}


?>