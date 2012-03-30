<?php

include("sailthru_config.php");

if (!empty($_POST)) {
    /* adds a list of emails, $_POST["emails"], to a list, $_POST["list"].
     * 
     * For more info see http://docs.sailthru.com/api/list
     */
    $result = $sailthru->saveList($_POST["list"], $_POST["emails"]);
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
    <label for="list">List Name</label><input type="text" name="list" ><br/>
    <label for="list">Emails</label><textarea name="emails" ></textarea><br/>
    <input type="submit" value="Add Users" style="margin-left:200px">
</form>
</body>
</html>