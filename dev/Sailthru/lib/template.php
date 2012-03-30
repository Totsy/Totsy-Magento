<?php
include("sailthru_config.php");

if(!empty($_POST)){
        
        $template_params = array(
            "from_name" => $_POST["from_name"],
            "from_email" => $_POST["from_email"],
            "subject" => $_POST["subject"],
            "content_html" => $_POST["content_html"],
        );
        
        /* Creates a template, $_POST["template"], with the above parameters.
         * 
         * For more info see: http://docs.sailthru.com/api/template
         */
        $result = $sailthru->saveTemplate($_POST["template"], $template_params);
        
        echo "<pre>";
        print_r($result);
        echo "</pre>";
        echo "<hr>";
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
    <label for="template">Template Name</label><input type="text" name="template" value="3"><br>
    <label for="subject">Subject</label><input type="text" name="subject"><br>
    <label for="content_html">Content</label><textarea cols="50" rows="4" name="content_html" value="5"></textarea><br>
    <label for="from_name">From Name</label><input type="text" name="from_name"> <br>
    <label for="from_email">From Email</label><input type="text" name="from_email">Must be verified.  Click <a href="https://sailthru.com/admin/verify">here</a> to do so.<br>
    <input type="submit" value="Submit Template" style="margin-left:200px;">
</form>
    <p>Note: the above fields are all optional for saving a template, however if you want to use it to send messages they must all be filled out.</p>
</body>

</html>