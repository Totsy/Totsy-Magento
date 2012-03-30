<?php
//include_once 'log.php';

function dbgprint($text)
{
    // Comment in/out line below to output debug info to stdout
    //print $text;
}

// Inspired by https://www6.software.ibm.com/developerworks/education/ws-soa-callsecurephp/ws-soa-callsecurephp-pdf.pdf

class SOAPTimestamp {
	private $Created;
	private $Expires;
    function __construct($c, $e) 
    {
        $this->Created = $c;
        $this->Expires = $e;
    }
}

class UsernameT1 
{
	private $Username; //Name must be identical to corresponding XML tag in SOAP header
	private $Password; // Name must be identical to corresponding XML tag in SOAP header
	private $Nonce;
	private $Created;
	function __construct($username, $password, $nonce, $created) 
	{
		$this->Username=$username;
		$this->Password=$password;
		$this->Nonce=$nonce;
		$this->Created=$created;
	}
}

class UserNameT2 
{
	private $Timestamp;
	private $UsernameToken; //Name must be identical to corresponding XML tag in SOAP header
	function __construct ($ts, $ut)
	{
		$this->Timestamp = $ts;
		$this->UsernameToken = $ut;
	}
}

class STSoapClient extends SoapClient 
{
	function emptyNodeList($nodes)
	{
		if (is_null($nodes) || count($nodes) == 0)
		{
			return true;
		}
		
		foreach ($nodes as $node) 
		{ 
			if ($node->textContent != "") return false; 
		}	
		
		return true;
	}
	
	function printNodeList($nodes)
	{
		print "NODES:\n";
		foreach ($nodes as $node) { print $node->textContent . " / "; }	
		print "-------\n";
	}
	
	function __doRequest($request, $location, $action, $version, $one_way=0) 
	{
		$dom = new DOMDocument('1.0');

		try 
		{
			//loads the SOAP request to the Document
			$dom->loadXML($request);
		} 
		catch (DOMException $e) 
		{
			die('XML parse error with code ' . $e->code);
		}

		//create a XPath object to query the request
		$path = new DOMXPath($dom);
		
		//echo $request;
		$nodesPostInvoice = $path->query('//ns1:postInvoice');
		$nodesCalcInvoice = $path->query('//ns1:calculateInvoice');
		
		if (!$this->emptyNodeList($nodesCalcInvoice))
		{
			$mustFixLineItems = true;
			$method = 'calculateInvoice';
		}
		else if (!$this->emptyNodeList($nodesPostInvoice))
		{
			$mustFixLineItems = true;
			$method = 'postInvoice';
		}
		else
		{
			$mustFixLineItems = false;
		}
				
		if ($mustFixLineItems)
		{
			//search for a node
			$nodesToFix = $path->query('//SOAP-ENV:Envelope/SOAP-ENV:Body/ns1:' . $method . '/Invoice/lineItems/SOAP-ENC:Struct');

			//check if nodes are ok
			$this->fixLineItems($path, $nodesToFix);
		} 

		//save the modified SOAP request
		$request = $dom->saveXML();        
        
		//doRequest
		return parent::__doRequest($request, $location, $action, $version);
	}

	function fixLineItems(DOMXPath $path, DOMNodeList $nodes) 
	{
		//iterate through the node list
		for ($i = 0; $i < $nodes->length; $i++) 
		{
			$node = $nodes->item($i);
			
			if (!isset($grandparentNode))
			{
				// All of these nodes have the same grandparent
				$parentNode = $node->parentNode;
				$grandparentNode = $node->parentNode->parentNode;
			}

			$newNodeName = $node->parentNode->nodeName;
			
			// Figure out who will hold the new items
			
			$newNode = new DOMElement($newNodeName);
			$grandparentNode->appendChild($newNode);
			
			$this->CopyNodes($node, $newNode);			
		}
		
		// Remove the old SOAP-ENC:Struct container
		$grandparentNode->removeChild($parentNode);

		//print "Saving...";
		//$grandparentNode->ownerDocument->saveHTMLFile("C:\dev-integration\PhpApi\Fix.xml");
		//print "DONE\n";
	}
	
	function CopyNodes($oldParent, $newParent)
	{
		foreach ($oldParent->childNodes as $oldChildNode)
		{
			if ($oldChildNode->nodeType != 1)
			{
				continue;
			}			
			
			$newChildNode = new DOMElement($oldChildNode->nodeName, $oldChildNode->nodeValue);
			
			$newParent->appendChild($newChildNode);

			if ($oldChildNode->hasChildNodes())
			{
				$this->CopyNodes($oldChildNode, $newChildNode);
			}			
		}
		
	}
	
	function __doRequest__OLD($request, $location, $action, $version) 
	{
		$arrayToFix = 'lineItems';
		
		$request = str_replace('</SOAP-ENC:Struct><SOAP-ENC:Struct>', '</' . $arrayToFix . '><' . $arrayToFix . '>', $request);
		$request = str_replace('<SOAP-ENC:Struct>', '', $request);
		$request = str_replace('</SOAP-ENC:Struct>', '', $request);

		// parent call
		return parent::__doRequest($request, $location, $action, $version );
	}
}

function createMethodSignature($theMethod, $paramAr) 
{
	if (null == $paramAr)
	return array($theMethod =>null);
	$used = null;
    dbgprint("[CMS] processing $theMethod\n");
	foreach ($paramAr as $name => $value)
	{
		if (is_array($value) || is_object($value)) 
		{
            dbgprint("[CMS] $name is an object or array\n");
			$used[$name] = createMixedValueSignature($value);
		} 
		else 
		{
            dbgprint("[CMS] $name = $value\n");
			$used[$name] = $value;
		}
	}
	return array($theMethod =>$used);
}

//---------------------------------------------------------------------
// inner routine: packing an inner complex parameter into a SOAP-valid representation
function createMixedValueSignature($MixedVals) 
{
	$mixedParamsList = null;
	if (is_object($MixedVals)) 
	{
        dbgprint("[CMVS] got an object\n");
		foreach ($MixedVals as $name => $value) 
		{		
			if (is_object($value) || is_array($value)) 
			{
                dbgprint("[CMVS] $name is an object or array\n");
				$mixedParamsList->$name = createMixedValueSignature($value);
			} 
			else 
			{
				if (isset($value))
				{
                    dbgprint("[CMVS] $name = $value\n");
					$mixedParamsList->$name = $value;
				}
			}
		}
		// an object needs to be passed as SoapVar
		return new SoapVar($mixedParamsList, SOAP_ENC_OBJECT , NULL, NULL);
	} 
	else 
	{ // an array
        dbgprint("[CMVS] got an array\n");
				
		foreach ($MixedVals as $name => $value) 
		{
			if (is_object($value) || is_array($value)) 
			{
                dbgprint("[CMVS] $name is an object or array\n");
				$mixedParamsList[$name] = createMixedValueSignature($value);
			} 
			else 
			{
				//die ("Array Element: " . $name . "=" . $value);
				if (isset($value))
				{
                    dbgprint("[CMVS] $name = $value\n");
					$mixedParamsList[$name] = $value;
				}
			}
		}
		// an array is passed as is !! 
		return $mixedParamsList;
		// EJ: Or as SOAP_ENC_ARRAY?
		//print "DEBUG: Encoding array\n";
		//print_r($mixedParamsList);
		//return new SoapVar($mixedParamsList, SOAP_ENC_ARRAY);
	}
}

?>