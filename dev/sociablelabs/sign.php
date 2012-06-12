<?php
    //include the signer
    require("dev/sociable_signer.php");
    
    // declare a path to the key
    $key_path = "/sign_private_key";

    // declare RondavuData
    $rondavu_data = array (
        "config" =>  array ( "version" => "1.2" ),
        //information about the currently logged in user
        "user" => array ( 
            "id" => array (
                array ( "id" => "user12345", "type" => "totsy" )
            ),
            // use tracking fields to have query params on click back. e.g. http=>//www.totsy.com?refId=user12345
            "tracking" => array (
                array (
                    "name" => "refId",
                    "value" => "user12345"
                )
            )
        ),
        "primary_mo" => array (
            "id" => array (
                array ( "id"=> "totsy", "type" => "site" )
            ),
            "name" => array (
                array ( "name" => "Totsy" )
            ),
            "url" => array (
                "detail" => "http://www.totsy.com",
                "picture" => array (
                    "primary" => "http://www.totsy.com/img/logo-125x77.png",
                    "primary_secure" => "https://www.totsy.com/img/logo-125x77.png"
                )
            )
        )
    );

    //sign it
    $output = rondavu_sign($rondavu_data, $key_path);
?>
<!doctype html>
<html>
    <head>
        <title>Totsy Signing example</title>
    </head>
    <body>
        <h3>Original Data</h3>
        <p style="margin: 0 auto; font-family:monospace; min-width:600px;">
        <?php echo json_encode($rondavu_data); ?>
        </p>
        <h3>Signed Data:</h3>
        <p style="margin: 0 auto; font-family:monospace; max-width:600px;">
        <?php echo $output; ?>
        </p>
    </body>
</html>    
    
