<?php

include_once 'WsSecurityTypes.inc';

// *******************************************************************
// *                                                                 *
// *                *** CLASS DEFINITIONS ***                        *
// *                                                                 *
// *******************************************************************

// *******************************
// Customer & Exemption Management
// *******************************

// Customer

class createCustomer {

    /**
     * @var string
     */
    public $CompanyId;

    /**
     * @var (object)customer
     */
    public $Customer;

}

class customer {
    /**
     * @var string
     */
    public $customerReference;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $taxNumber;
}

class createCustomerResponse {

    /**
     * @var (object)manageCustomerResult
     */
    public $CreateCustomer;
    
}

class manageCustomerResult {

    /**
     * @var (object)customer
     */
    public $Customer;    
    
    /**
     * @var array[0, unbounded] of string
     */
    public $errors;

    /**
     * @var string
     *     NOTE: resultType should follow the following restrictions
     *     You can have one of the following value
     *     CREATED
     *     UPDATED
     *     FAILED_WITH_ERRORS
     */
    public $resultType;
    
}

// Exemption

class createExemption {

    /**
     * @var string
     */
    public $CompanyId;

    /**
     * @var (object)exemptionCertificate
     */
    public $ExemptionCertificate;

}

class exemptionCertificate {
    /**
     * @var string
     */
    public $customerReference;

    /**
     * @var dateTime
     */
    public $endDate;

    /**
     * @var array[0, unbounded] of string
     */
    public $exemptedStateCodes;

    /**
     * @var string
     */
    public $exemptionReference;
    
    /**
     * @var double
     */
    public $rateOfExemption;
    
    /**
     * @var string
     */
    public $reasonForExemption;
    
    /**
     * @var string
     */
    public $skuCode;
    
    /**
     * @var dateTime
     */
    public $startDate;
}

class createExemptionResponse {

    /**
     * @var (object)manageExemptionResult
     */
    public $CreateExemption;    
}

class manageExemptionResult {
    
    /**
     * @var array[0, unbounded] of string
     */
    public $errors;

    /**
     * @var (object)exemptionCertificate
     */
    public $ExemptionCertificate;    

    /**
     * @var string
     *     NOTE: resultType should follow the following restrictions
     *     You can have one of the following value
     *     CREATED
     *     UPDATED
     *     FAILED_WITH_ERRORS
     */
    public $resultType;    
}



// *******************************
//           Sales Tax
// *******************************

class queryInvoice {

    /**
     * @var string
     */
    public $CompanyId;

    /**
     * @var string
     */
    public $InvoiceNumber;

}

class queryInvoiceResponse {

    /**
     * @var (object)calculateInvoiceResult
     *    Or one of following derived class(es)
     *       postInvoiceResult
     */
    public $CalculateInvoiceResult;

}

class invoiceResult {

    /**
     * @var array[0, unbounded] of (object)calculationError
     */
    public $errors;

    /**
     * @var string
     *     NOTE: resultType should follow the following restrictions
     *     You can have one of the following value
     *     SUCCESS
     *     FAILED_INVOICE_NUMBER
     *     FAILED_WITH_ERRORS
     */
    public $resultType;

}

class calculationError {

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     *     NOTE: type should follow the following restrictions
     *     You can have one of the following value
     *     INVOICE_POSTED
     *     INVOICE_INVALID
     *     LOCATION_ID
     *     SHIP_TO_ADDRESS
     *     SHIP_FROM_ADDRESS
     *     LINE_ITEMS
     *     PRODUCT_CODE
     */
    public $type;

}

class calculateInvoiceResult extends invoiceResult {

    /**
     * @var array[0, unbounded] of (object)lineItemBundle
     */
    public $lineItemBundles;

    /**
     * @var (object)price
     */
    public $totalExemptSales;

    /**
     * @var (object)price
     */
    public $totalSales;

    /**
     * @var (object)price
     */
    public $totalTax;

}

class lineItemBundle {

    /**
     * @var (object)fullAddress
     */
    public $addressForTax;

    /**
     * @var array[0, unbounded] of (object)lineItem
     */
    public $lineItems;

    /**
     * @var boolean
     */
    public $recalculatedJurisdictions;

    /**
     * @var string
     *     NOTE: resultType should follow the following restrictions
     *     You can have one of the following value
     *     STATE
     *     FALLBACK
     *     FULL
     *     UNRESOLVED
     */
    public $resultType;

    /**
     * @var array[0, unbounded] of (object)invoiceTax
     */
    public $taxes;

}

class fullAddress {

    /**
     * @var string
     */
    public $address;

    /**
     * @var string
     */
    public $city;

    /**
     * @var string
     */
    public $state;

    /**
     * @var string
     */
    public $zip;

}

class lineItem {

    /**
     * @var string
     */
    public $customReference;

    /**
     * @var int
     */
    public $lineItemNumber;

    /**
     * @var (object)price
     */
    public $nonTaxableSalesAmount;

    /**
     * @var string
     */
    public $productCode;

    /**
     * @var double
     */
    public $quantity;

    /**
     * @var (object)price
     */
    public $salesAmount;

    /**
     * @var (object)address
     */
    public $shipFromAddress;

    /**
     * @var string
     */
    public $shipFromLocation;

    /**
     * @var (object)address
     */
    public $shipToAddress;

    /**
     * @var (object)price
     */
    public $taxAmount;

    /**
     * @var (object)price
     */
    public $taxableSalesAmount;

    /**
     * @var array[0, unbounded] of (object)invoiceTax
     */
    public $taxes;

    /**
     * @var (object)price
     */
    public $unitPrice;

}

class price {

    /**
     * @var int
     */
    public $cents;

    /**
     * @var double
     */
    public $decimalValue;

    /**
     * @var int
     */
    public $dollars;

    /**
     * @var boolean
     */
    public $negative;

}

// Harapartners: TODO: address class is declared more than once in the checkout step saveShipping, further investigation need...
// Do not evoke autoload for class_exists
//if(!class_exists('address', false)){
class address {
    
        /**
         * @var string
         */
        public $address1;
    
        /**
         * @var string    
         */
        public $address2;
    
        /**
         * @var array[0, unbounded] of string
         */
        public $jurisdictions;
    
        /**
         * @var dateTime
         */
        public $resolvedDate;
    
}
//}

class taxJurisdiction {

    /**
     * @var string
     */
    public $jurisdictionFips;

    /**
     * @var string
     */
    public $jurisdictionName;

}

class invoiceTax extends taxJurisdiction {

    /**
     * @var (object)price
     */
    public $totalTax;

}

class postInvoiceResult extends calculateInvoiceResult {

    /**
     * @var (object)postTransaction
     */
    public $transaction;

}

class postTransaction {

    /**
     * @var dateTime
     */
    public $date;

    /**
     * @var string
     */
    public $invoiceNumber;

    /**
     * @var string
     */
    public $transactionId;

}

class postInvoices {

    /**
     * @var string
     */
    public $CompanyId;

    /**
     * @var array[0, unbounded] of string
     */
    public $InvoiceNumbers;

}

class postInvoicesResponse {

    /**
     * @var (object)postBatchInvoicesResult
     */
    public $PostBatchInvoicesResult;

}

class postBatchInvoicesResult extends invoiceResult {

    /**
     * @var array[0, unbounded] of (object)postTransaction
     */
    public $transactions;

}

class resolveAddress {

    /**
     * @var (object)address
     */
    public $Address;

}

class resolveAddressResponse {

    /**
     * @var (object)resolveAddressResult
     */
    public $ResolveAddressResult;

}

class resolveAddressResult {

    /**
     * @var array[0, unbounded] of (object)taxJurisdiction
     *    Or one of following derived class(es)
     *       invoiceTax
     */
    public $jurisdictions;

    /**
     * @var (object)fullAddress
     */
    public $resolvedAddress;

    /**
     * @var string
     *     NOTE: resultType should follow the following restrictions
     *     You can have one of the following value
     *     STATE
     *     FALLBACK
     *     FULL
     *     UNRESOLVED
     */
    public $resultType;

}

class voidInvoice {

    /**
     * @var string
     */
    public $CompanyId;

    /**
     * @var string
     */
    public $InvoiceNumber;

}

class voidInvoiceResponse {

    /**
     * @var (object)invoiceResult
     *    Or one of following derived class(es)
     *       calculateInvoiceResult
     *       postInvoiceResult
     *       postBatchInvoicesResult
     */
    public $InvoiceResult;

}

class getVersion {

}

class getVersionResponse {

    /**
     * @var string
     */
    public $return;

}

class postInvoice {

    /**
     * @var string
     */
    public $CompanyId;

    /**
     * @var (object)invoice
     */
    public $Invoice;

}

class invoice {

    /**
     * @var string
     */
    public $customerIdentifier;

    /**
     * @var string
     */
    public $customerName;

    /**
     * @var boolean
     */
    public $exempt;

    /**
     * @var string
     */
    public $exemptionCertificate;

    /**
     * @var string
     */
    public $exemptionReason;

    /**
     * @var dateTime
     */
    public $invoiceDate;

    /**
     * @var string
     */
    public $invoiceNumber;

    /**
     * @var string
     *     NOTE: invoiceType should follow the following restrictions
     *     You can have one of the following value
     *     INVOICE
     *     CREDIT
     *     DEBIT
     *     RETURN
     *     SALESORDER
     */
    public $invoiceType;

    /**
     * @var array[0, unbounded] of (object)lineItem
     */
    public $lineItems;

}

final class INVOICE_TYPES {
     const INVOICE = "INVOICE";
     const CREDIT = "CREDIT";
     const DEBIT = "DEBIT";
     const RET = "RETURN";
     const SALESORDER = "SALESORDER";

    // ensures that this class acts like an enum
    // and that it cannot be instantiated
    private function __construct(){}
}

class postInvoiceResponse {

    /**
     * @var (object)postInvoiceResult
     */
    public $PostInvoiceResult;

}

class calculateInvoice {

    /**
     * @var string
     */
    public $CompanyId;

    /**
     * @var (object)invoice
     */
    public $Invoice;

}

class calculateInvoiceResponse {

    /**
     * @var (object)calculateInvoiceResult
     *    Or one of following derived class(es)
     *       postInvoiceResult
     */
    public $CalculateInvoiceResult;

}

class ping {

}

class pingResponse {

    /**
     * @var string
     */
    public $return;

}

// *******************************************************************
// *                                                                 *
// *                    *** API FUNCTION ***                         *
// *                                                                 *
// *******************************************************************

class SpeedTax
{
    function Version()
    {
        return "1.4.a";
    }
    
    function CheckResult($result)
    {
        if (isset($result) && isset($result->faultstring) && strlen($result->faultstring) > 0)
        {
            throw new Exception("Error calling SpeedTax: " . $result->faultstring);
        }
    }

    function Ping()
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $theMethodName = 'ping';

        $client = new SoapClient($wsdl, array("trace" => 0, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, array("" => ""), null, $secHeader );

        $this->CheckResult($result);

        return $result;
    }

    function ResolveAddress($Address)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('Address'=> $Address);

        $theMethodName = 'resolveAddress';
        $theMethodSignature = CreateMethodSignature('resolveAddress', $argumentsList);

        $client = new SoapClient($wsdl, array("trace" => 0, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
            
        $this->CheckResult($result);

        return $result;
    }

    function CalculateInvoice($Invoice)
    {
        return $this->CalculateOrPostInvoice($Invoice, false);
    }

    function PostInvoice($Invoice)
    {
        return $this->CalculateOrPostInvoice($Invoice, true);
    }

    function PostInvoices($InvoiceNumbers)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('CompanyId'=> $company, 'InvoiceNumbers'=> $InvoiceNumbers);

        $theMethodName = 'postInvoices';
        
        $theMethodSignature = CreateMethodSignature($theMethodName, $argumentsList);
        
        // Need special client due to <SOAP-ENC:Struct> bug in PHP/SOAP when encoding arrays.
        $client = new STSoapClient($wsdl, array("trace" => 1, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
        
        $this->CheckResult($result);
        
        return $result;
    }
    
    function CalculateOrPostInvoice($Invoice, $post)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('CompanyId'=> $company, 'Invoice'=> $Invoice);

        if ($post)
        {
            $theMethodName = 'postInvoice';
        }
        else
        {
            $theMethodName = 'calculateInvoice';
        }
        
        $theMethodSignature = CreateMethodSignature($theMethodName, $argumentsList);

        // Need special client due to <SOAP-ENC:Struct> bug in PHP/SOAP when encoding arrays.
        $client = new STSoapClient($wsdl, array("trace" => 1, "exceptions" => 0));
    
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
        
        $this->CheckResult($result);
        
        return $result;
    }
    
    function VoidInvoice($InvoiceNumber)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('CompanyId'=> $company, 'InvoiceNumber'=> $InvoiceNumber);

        $theMethodName = 'voidInvoice';
        
        $theMethodSignature = CreateMethodSignature($theMethodName, $argumentsList);
        
        // Need special client due to <SOAP-ENC:Struct> bug in PHP/SOAP when encoding arrays.
        $client = new STSoapClient($wsdl, array("trace" => 1, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
        
        $this->CheckResult($result);
        
        return $result;
    }
    
    function CreateCustomer($Customer)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('CompanyId'=> $company, 'Customer'=> $Customer);

        $theMethodName = 'createCustomer';
        $theMethodSignature = CreateMethodSignature('createCustomer', $argumentsList);
        
        //dbgprint("XML Request:\n");
        //print_r($theMethodSignature);
        
        $client = new SoapClient($wsdl, array("trace" => 0, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
            
        $this->CheckResult($result);

        return $result;
    }    
    
    function EditCustomer($Customer)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('CompanyId'=> $company, 'Customer'=> $Customer);

        $theMethodName = 'editCustomer';
        $theMethodSignature = CreateMethodSignature('editCustomer', $argumentsList);

        $client = new SoapClient($wsdl, array("trace" => 0, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
            
        $this->CheckResult($result);

        return $result;
    }    
    
    function CreateExemption($ExemptionCertificate)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('CompanyId'=> $company, 'Exemption'=> $ExemptionCertificate);

        $theMethodName = 'createExemption';
        $theMethodSignature = CreateMethodSignature('createExemption', $argumentsList);

        //dbgprint("XML Request:\n");
        //print_r($theMethodSignature);

        $client = new SoapClient($wsdl, array("trace" => 0, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
            
        $this->CheckResult($result);

        return $result;
    }    
    
    function EditExemption($ExemptionCertificate)
    {
        include "Credentials.inc";
        include 'WsSecurity.inc';
        
        $argumentsList = array ('CompanyId'=> $company, 'Exemption'=> $ExemptionCertificate);

        $theMethodName = 'editExemption';
        $theMethodSignature = CreateMethodSignature('editExemption', $argumentsList);

        $client = new SoapClient($wsdl, array("trace" => 0, "exceptions" => 0));
        
        $result = $client->__soapCall($theMethodName, $theMethodSignature, null, $secHeader );
            
        $this->CheckResult($result);

        return $result;
    }    
}