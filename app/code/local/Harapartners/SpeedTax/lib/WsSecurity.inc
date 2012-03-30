<?php

// Inspired by https://www6.software.ibm.com/developerworks/education/ws-soa-callsecurephp/ws-soa-callsecurephp-pdf.pdf

//2009-04-07T16:04:49Z
$utcCreated = str_replace("+00:00", "Z", gmdate("c", time()));
$utcExpires = str_replace("+00:00", "Z", gmdate("c", (time() + 60*2)));

$nsWsse = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";  //WS-Security nsWsse
$nsWsu = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd";
$nsTypeText = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText";

$userT = new SoapVar($username, XSD_STRING, NULL, $nsWsse, NULL, $nsWsse);
$passwT = new SoapVar($password, XSD_STRING, NULL, $nsWsse, NULL, $nsWsse);
$nonceT = new SoapVar('wh66ztc4ZaCn2SFMpKDY/w==', XSD_STRING, NULL, $nsWsse, NULL, $nsWsse);

$createdT = new SoapVar($utcCreated, XSD_STRING, NULL, $nsWsu, NULL, $nsWsu);
$expiresT = new SoapVar($utcExpires, XSD_STRING, NULL, $nsWsu, NULL, $nsWsu);
$ts = new SOAPTimestamp($createdT, $expiresT);
$timestampT = new SoapVar($ts, SOAP_ENC_OBJECT, NULL, $nsWsu, NULL, $nsWsu);

$tmp = new UsernameT1($userT, $passwT, $nonceT, $createdT);
$uuT = new SoapVar($tmp, SOAP_ENC_OBJECT, NULL, $nsWsse, 'UsernameToken', $nsWsse);

$tmp = new UsernameT2($timestampT, $uuT);
$userToken = new SoapVar($tmp, SOAP_ENC_OBJECT, NULL, $nsWsse, 'UsernameToken', $nsWsse);

$secHeaderValue=new SoapVar($userToken, SOAP_ENC_OBJECT, NULL, $nsWsse, 'Security', $nsWsse);
$secHeader = new SoapHeader($nsWsse, 'Security', $secHeaderValue, true);


?>