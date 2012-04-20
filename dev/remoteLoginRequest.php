<?php
// customer can register through remote affiliate post.
// response {"status":"failed","error_message":"xxx"}  ;
//          {"status":"success","login_url":"http://totsy.local.com/affiliate/remote/login?email=xx&password=yyy&product_url=zzz"} 
// work for both http remost post and parents.com requirement
// request example follow
//$baseUrlTotsy = 'http://totsy.local.com/affiliate/remote/register';  
$baseUrlTotsy = 'http://magento-totsy.totsy.com/affiliate/?genpswd=true';                      //tosty 
//$baseUrlMamasource = 'http://totsy.local.com/mamasource/affiliate/remote/register';       //mamasource
$baseUrlMamasource = 'http://magento-mamasource.totsy.com/affiliate/?genpswd=true';       //mamasource
$email = 'asdas123dsad@asd.com';
$productUrl = 'product_url';                                                              // parents.com only param
$affiliateCode = 'parents_com';
$otherfield = '';                                                                         //subaffiliate_code etc
$password = 'pwdasda';                                                                        // if null autogenerate
//totsy mamasource
$url = $baseUrlTotsy.'&affiliate_code='.$affiliateCode;                                   //work with mamasource too
$data = array(
        'email' => $email,
        'password' => $password,
        'product_url' => $productUrl,
		'other_field'=> $otherfield
         );
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ( $ch, CURLOPT_URL, $url );
curl_setopt ( $ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$result = curl_exec ( $ch );
curl_close ( $ch );
foreach (json_decode($result,true) as $index=>$value) {
	echo $index.': '.$value.'<br/>';
}