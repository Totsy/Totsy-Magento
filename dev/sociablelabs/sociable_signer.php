<?php

/**********************
* RondavuData signing example.
*
* Requires PHP's Multibyte strings and OpenSSL modules.
*
* by Nathan Friedly <nathan@sociablelabs.com> and Ramil Nobleza <ramil@sociablelabs.com>
*
* Last Modified on 4/29/2011
***********************/


/**
* Method rondavu_sign
* Signs RondavuData with key and returns a JSON string.
*
* Example:
* <script>
*   var RondavuData = < ?php echo rondavu_sign($UnsignedRondavuData, "path/to/sign_private_key"); ? >;
* </script>
* < script> 
*   // JS snippet supplied by Sociable Labs here
* </script>
*
* @param {string|object|array} $rondavuData Complete, unsigned RondavuData. Accepts a JSON string, a PHP object, or a PHP array
* @param {string} $sign_private_key_path the path to the sign_private_key file that Sociable Labs provides. This file should not be publicly accessible, but PHP's user account must have read permission on it.
* @param {string} signed RondavuData, ready to echo into your page.
*/

function rondavu_sign($RondavuData, $sign_private_key_path) {
    
    // if we were given a string, parse it into an object
    if(is_string($RondavuData)){
        $RondavuData = json_decode($RondavuData);
    }
    
    // ensure we're working with an array for at least the top level
    if(is_object($RondavuData)){
           $RondavuData = get_object_vars($RondavuData);
    }
    
    // read in the private key
    $priv_key = file_get_contents($sign_private_key_path);
    
    // let openssl parse the key and return an identifier
    $priv_key_id = openssl_get_privatekey($priv_key);
    if($priv_key_id === false){
           throw new Exception('There was an error parsing the supplied key for signing RondavuData.');
    }
    
    // add a spot to store the signatures
    $RondavuData['signature'] = array();
    
    // Loop through all signable the fields, encoding and signing them if they are present in the data
    foreach(array('user','page','primary_mo','mos') as $field){
    
           // skip fields that don't currently exist or don't have any data
           if(!isset($RondavuData[$field]) || !$RondavuData[$field]){
                 continue;
           }
    
           $encoded_field = $field . "_base64";
    
           // create the JSON string that we're going to sign
           $json = rondavu_json_encode($RondavuData[$field]);
    
           // ensure that the data is UTF-8
           
           /*
           if(!mb_check_encoding($json, 'UTF-8')){
                   $enc =  mb_detect_encoding($json, 'UTF-8, UTF-7, ASCII, ISO-8859-1, EUC-JP, SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP');
                   if(!enc) {
                           throw new Exception('Unrecognized encoding for RondavuData field ' . $field);
                   }
                   $json = mb_convert_encoding ($json, 'UTF-8', $enc);
           } */
    
           // create the encoded version so that the exact string, including any whitespace, can be passed along for signature verification
           $RondavuData[$encoded_field] = rondavu_base64_encode($json);
    
           // and delete the original value
           unset($RondavuData[$field]);
    
           // placeholder for the sig data
           $signature;
    
           // create the signature
           $success = openssl_sign($RondavuData[$encoded_field], $signature, $priv_key_id, OPENSSL_ALGO_SHA1);
           if(!$success){
                   throw new Exception('There was an error generating the signature for RondavuData field ' . $field);
           }
    
           // record the signature
           $RondavuData['signature'][$field] = rondavu_base64_encode($signature);
    
    }
    
    // free up the private key from memory
    openssl_free_key($priv_key_id);
    
    // Finally, to output, convert to JSON, then undo the escaped /'s.
    return rondavu_json_encode($RondavuData, 'JSON_PRETTY_PRINT');
}


/**
* Private Method rondavu_base64_encode
* Does magic to data (base64 + newlines + strips that last newline that chunk_split adds)
* @param $data  the data to encode
* @param $newline the newline you want to use
* @return the encoded data
*/
function rondavu_base64_encode($data, $newline = "\r\n"){
       $split = chunk_split(base64_encode($data),76,$newline);
       return substr($split,0,strlen($split)-strlen($newline));
}

/**
* Private Method rondavu_json_encode
* Converts object/array to JSON and unescapes front slash (/) characters
* @param $data
* @return JSON string
*/
function rondavu_json_encode($data){
    return str_replace("\/","/",json_encode($data));
}
?>
