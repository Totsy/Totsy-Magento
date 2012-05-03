<?php
// *******************************************************************
// *                                                                 *
// *                    *** UTIL FUNCTION ***                        *
// *                                                                 *
// *******************************************************************

//
// All output is formatted for the console, not for a web page
//

function STLog($text)
{
    $myFile = "phplog.txt";
    $fh = fopen($myFile, 'w') or die("can't open file");
    fwrite($fh, $text);
    fclose($fh);
}

function DisplayInvoiceResult($result)
{

    foreach ($result as $name => $value)
    {    
        switch ($name)
        {
            case 'resultType':
                print "Result............: " . $value . "\n";
                break;
            case 'lineItemBundles':
                print "Line Item Bundle:\n";
                DisplayInvoiceBundle($value);
                break;
            case 'totalExemptSales':
                print "Total Exempt Sales: $" . number_format($value->decimalValue, 2) . "\n";
                break;
            case 'totalSales':
                print "Total Sales.......: $" . number_format($value->decimalValue, 2) . "\n";
                break;
            case 'totalTax':
                print "Total Tax.........: $" . number_format($value->decimalValue, 2) . "\n";
                break;
        }
    }
}

/*
This function might receive one single bundle or an array of bundles
*/
function DisplayInvoiceBundle($bundle)
{    
    if (is_array($bundle))
    {
        foreach ($bundle as $value)
        {        
            DisplayOneInvoiceBundle($value);
        }
    } 
    else 
    {
        DisplayOneInvoiceBundle($bundle);
    }
}

function DisplayOneInvoiceBundle($value)
{
        print "    Address for Tax:\n";
        DisplayFullAddress($value->addressForTax, "        ");
        print "    Line Item:\n";
        DisplayLineItem($value->lineItems);
        print "    Recalculated Jurisdictions: " . $value->recalculatedJurisdictions . "\n";
        print "    Result Type: " . $value->resultType . "\n";
        print "    Taxes: \n";
        
        if (isset($value->taxes))
        {
            DisplayTaxes($value->taxes);
        }
}

function DisplayLineItem($item)
{    
    // We might get one line item or an array of line items.  Use recursion to simplify.
    // This is a different path than the one used for line item bundles, which defines
    // a separate function    
    if (is_array($item))
    {
        foreach ($item as $value)
        {
            DisplayLineItem($value);
        }
    }
    else
    {
        foreach ($item as $name => $value)
        {    
            switch ($name)
            {
                case 'lineItemNumber':
                    print "        Line Item Number: " . $value . "\n";
                    break;
                case 'customReference':
                    print "        Custom Reference: " . $value . "\n";
                    break;
                case 'productCode':
                    print "        Product Code....: " . $value . "\n";
                    break;
                case 'taxAmount':
                    print "        Tax.............: $" . DisplayAmount($value) . "\n";
                    break;
                case 'salesAmount':
                    print "        Sales Amount....: $" . DisplayAmount($value) . "\n";
                    break;
                case 'nonTaxableSalesAmount':
                    print "        Non-Tax. Amt....: $" . DisplayAmount($value) . "\n";
                    break;
                case 'taxableSalesAmount':
                    print "        Taxable Amt.....: $" . DisplayAmount($value) . "\n";
                    break;
                case 'taxes':
                    print "        Line Item Tax Breakdown:\n";
                    DisplayTaxes($value);
                    break;
                default:
                    //print $name . "\n";
                    break;
            }
        }
    }
    print "        --------\n";
}

function DisplayTaxes($value)
{
    if (!isset($value))
    {
        return;
    }
    
    if (is_array($value))
    {
        foreach($value as $tax)
        {
            DisplayTaxes($tax);
            //print "        " . $tax->jurisdictionName . " (" . $tax->jurisdictionFips . "): $" . DisplayAmount($tax->totalTax) . "\n";
        }
    }
    else
    {
        $tax = $value; // There was only one tax, so $value is not an array but rather the specific tax object.
        print "        " . $tax->jurisdictionName . " (" . $tax->jurisdictionFips . "): $" . DisplayAmount($tax->totalTax) . "\n";
    }
    
}

function DisplayAmount($value)
{
    
    if (isset($value->decimalValue))
    {
        $amt = $value->decimalValue;
    }
    else
    {
        $amt = 0;
        
        if (isset($value->dollars))
        {
            $amt = $value->dollars;
        }
        if (isset($value->cents))
        {
            $amt = $amt + ($value->cents / 100);
        }
    }
    
    return number_format($amt, 2);
}

function DisplayFullAddress($fullAddress, $prefix)
{
    if (isset($prefix)) print $prefix;
    print $fullAddress->address . "\n";

    if (isset($prefix)) print $prefix;
    print $fullAddress->city . ", " . $fullAddress->state . "  " . $fullAddress->zip . "\n";
}

function DisplayJurisdictions($jurisdictions)
{
    foreach($jurisdictions as $j)
    {
        print $j->jurisdictionName . " (" . $j->jurisdictionFips . ")\n";
    }
}

function DisplayErrors($errors)
{
    if (is_array($errors))
    {
        foreach($errors as $e)
        {
            DisplayErrors($e);
        }
    }
    else
    {
        print $errors . "\n";
    }
}

function DisplayInvoice($invoice)
{
    foreach ($invoice as $name => $value)
    {    
        //print "Name" . $name . ": ";
        //print_r ($value);
            
        switch ($name)
        {
            case 'customerIdentifier':
                print 'customerIdentifier..: ' . $value . "\n";
                break;
            case 'customerName':
                print 'customerName........: ' . $value . "\n";
                break;
            case 'exempt':
                print 'exempt..............: ' . $value . "\n";
                break;
            case 'exemptionCertificate':
                print 'exemptionCertificate: ' . $value . "\n";
                break;
            case 'exemptionReason':
                print 'exemptionReason.....: ' . $value . "\n";
                break;
            case 'invoiceDate':
                print 'invoiceDate.........: ' . $value . "\n";
                break;
            case 'invoiceNumber':
                print 'invoiceNumber.......: ' . $value . "\n";
                break;
            case 'invoiceType':
                print 'invoiceType.........: ' . $value . "\n";
                break;
            //case 'lineItems':
            //    print "Line Items:\n";
            //    DisplayLineItem($value);
            //    break;
            default:
                //print $name . " = " . $value;
                break;
        }
    }
        
    print "Line Items:\n";        
    
    foreach ($invoice->lineItems as $name => $value)
    {
        DisplayLineItem($value);
    }
}