<?php 
class Harapartners_Affiliate_FeedsController extends Mage_Core_Controller_Front_Action {
    
    const COUNTER_LIMIT = 2000;
        
    public function preDispatch() {
        header ("Content-Type:text/xml");
        parent::preDispatch ();
    }
    
    public function indexAction() {
        $request = $this->getRequest();
        $from = $request->getParam('from'); // format 20120401
        $to = $request->getParam('to');
        $type = $request->getParam('type');
        $token = $request->getParam('token');
        $affiliateCode = $request->getParam('affiliate_code');
        if(!$affiliateCode){
            $affiliateCode = 'keyade';
        }
        if(!!$request->getParam('period')){
            $period=3600*24*$request->getParam('period');
        } 
        if($token!='7cf7e9d58a213b2ebb401517d342475e'){
            echo "Invalid token";
            return;
        }
        if(!!$from && !!$to){
            $simpleXml = $this->_generateSimpleXml();        
            switch ($type) {
                case 'signups':
                $xml = $this->_createSignupsXml($simpleXml,$from,$to,$affiliateCode);
                break;
                
                case 'signupsByReferral':
                //Place holders !!!
                $xml = $this->_createSignupsByReferralXml($simpleXml,$from,$to,$affiliateCode);
                break;
                
                case 'sales':
                $xml = $this->_createSalesXml($simpleXml,$from,$to,$affiliateCode,$period);
                break;
                
                case 'referringSales':
                //Place holders !!!
                $xml = $this->_createReferringSalesXml($simpleXml,$from,$to,$affiliateCode,$period);
                break;
                                
                default:    
                    $xml = $simpleXml->addChild('error','Unknown Type');
                break;
            } 
           
            echo $xml->asXML ();
        }
    }
    
    protected function _generateSimpleXml(){
        $xmlStr = <<<XML
<?xml version="1.0" encoding="utf-8" standalone="yes" ?>
<!DOCTYPE report PUBLIC "report" "https://dtool.keyade.com/dtd/conversions_v5.dtd">
<report></report>
XML;
        $xml = new SimpleXMLElement ($xmlStr);

        return $xml;
    }
    
    protected function _createSignupsXml($simpleXml,$from,$to,$affiliateCode){
        $recordCollection = $this->_prepareCollection($affiliateCode)
                                ->addFieldToFilter('created_at', array("from"=>$from, "to" =>$to, 'date'=> true ))
                                ->load();                                                    
        foreach ($recordCollection as $record) { 
            $this->_signupsEntry($simpleXml,$record);  
        }
        return $simpleXml;
    }
    
    protected function _createSignupsByReferralXml($simpleXml,$from,$to,$affiliateCode){
        $recordCollection = $this->_prepareCollection($affiliateCode,1)
                                ->addFieldToFilter('created_at', array("from"=>$from, "to" =>$to, 'date'=> true))
                                ->load();    
        foreach ($recordCollection as $record) { 
            $this->_signupsEntry($simpleXml,$record);  
        }
        return $simpleXml;
    }
    
    protected function _createSalesXml($simpleXml,$from,$to,$affiliateCode,$period){
       
        $recordCollection = $this->_prepareCollection($affiliateCode)->
            addFieldToFilter('created_at', array( "to" => $to,"from"=>$from, 'date'=>true))->
            addFieldToFilter('customer_id', array( "notnull" => true))->
            load();  
        foreach ($recordCollection as $record) {
        // record may not have accurate customerId
            $clickId = $this->_extractClickId($record);
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                                                            ->addFieldToFilter('created_at', array( "to" => $to,"from"=>$from, 'date'=>true))
                                                            ->addFieldToFilter('customer_id',$record->getCustomerId())
                                                            ->addFieldToFilter('status','complete')
                                                            ->load();    

            foreach ($orderCollection as $order) {
                $this->_salesEntry($simpleXml,$record,$order,$clickId,$period);
            }   
        }            
        return $simpleXml;
    }
    
    protected function _createReferringSalesXml($simpleXml,$from,$to,$affiliateCode,$period){
        $recordCollection = $this->_prepareCollection($affiliateCode,1)->
            addFieldToFilter('created_at', array( "to" => $to,"from"=>$from, 'date'=>true))->
            addFieldToFilter('customer_id', array( "notnull" => true))->
            load();
        foreach ($recordCollection as $record) {
        // record may not have accurate customerId
            //$customer = Mage::getModel('customer/customer')->setWebsiteId(1)->loadByEmail($record->getCustomerEmail());
            $clickId = $this->_extractClickId($record);
            $orderCollection = Mage::getModel('sales/order')->getCollection()
                                                            ->addAttributeToFilter('created_at', array( "from" => $from,"to"=>$to, 'date'=>true ))
                                                            ->addFieldToFilter('customer_id',$record->getCustomerId())
                                                            ->addFieldToFilter('status','complete')
                                                            ->load();    

            foreach ($orderCollection as $order) {
                $this->_salesEntry($simpleXml,$record,$order,$clickId,$period);
            }            
        }            
        return $simpleXml;
    }    
    
    protected function _prepareCollection($affiliateCode,$level = 0){
        return Mage::getModel('customertracking/record')->getCollection()
            ->addFieldToFilter('affiliate_code', array('like' => '%'.$affiliateCode.'%'))
            ->addFieldToFilter('level', $level)
            ->addFieldToFilter('registration_param', array("like" => "%clickId%"));
    }
    
    protected function _signupsEntry(&$simpleXml,&$record){
        $entry = $simpleXml->addChild ('entry');
        $entry->addAttribute('clickId',$this->_extractClickId($record));
        $entry->addAttribute('eventMerchantId',$record->getCustomerId());
        $entry->addAttribute('count1',1);
        $entry->addAttribute('time',strtotime($record->getCreatedAt()));
        $entry->addAttribute('eventStatus','confirmed');
    }
    
    protected function _salesEntry(&$simpleXml,&$record,&$order,&$clickId,$period){
        $salesTime = strtotime($order->getCreatedAt());
        $registrationTime = strtotime($record->getCreatedAt());
        $lte = ($salesTime-$registrationTime<=$period)?false:true;
        
        if($period>0 && $lte===false){ 
            return;
        }
        
        $entry = $simpleXml->addChild('entry');
        $entry->addAttribute('clickId',$clickId);
        $entry->addAttribute('lifetimeId',$record->getCustomerId());
        $entry->addAttribute('eventMerchantId',$order->getIncrementId());
        $entry->addAttribute('value1',$order->getGrandTotal());
        $entry->addAttribute('time',$salesTime);
    }
    
    protected function _extractClickId(&$record){
        $data = json_decode($record->getRegistrationParam(),true);
        $data = array_change_key_case($data,CASE_LOWER);
        return $data['clickid'];
    }
}