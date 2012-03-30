<?php
include("sailthru_config.php");

$result = $sailthru->getTemplate("");
$templates = $result['templates'];

if (!empty($_POST)) {
    /*Sends a transaction email to a single address, $_POST["email"]
     * with the template, $_POST["template"].
     * 
     * For more info see: http://docs.sailthru.com/api/send
     */
    $result = $sailthru->send($_POST["template"], $_POST["email"]);
    print_r($result);
}
?>

<html>
    <head>
        <style type="text/css">
            label{
                width:200px;
                float:left;
                text-align:right;
                margin-right:5px;
            }

        </style>
    </head>
    <body>
        <form method="post">
            <label for="template">Template</label><select name="template">
                <? foreach ($templates as $template): ?>
                    <option><?= $template["name"] ?></option>
                <? endforeach ?>
            </select><br style="clear:both;"/>
            <label for="email">Email Address</label><input type="text" name="email" >
            <input type="submit" value="Sign Up!">
        </form>
    </body>
</html>