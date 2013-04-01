<?php

class Totsy_Page_Block_Html_Script_Footer extends Fooman_Speedster_Block_Page_Html_Head
{

protected function _construct() {
}

protected function _toHtml() {
    return $this->getCssJsHtml();
}
}