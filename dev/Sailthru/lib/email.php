<?php
include("sailthru_config.php");


if (!empty($_POST)) {
    $lists = array($_POST["list"] => 1);
    $vars = array("first_name" => $_POST["first_name"], "last_name" => $_POST["last_name"]);

    $lists = array();
    if(!empty($_POST["list1"])){
        $lists["list1"] = 1;
    }
    if(!empty($_POST["list1"])){
        $lists["list2"] = 1;
    }
    if(!empty($_POST["list1"])){
        $lists["list3"] = 1;
    }
    if(!empty($_POST["list1"])){
        $lists["list4"] = 1;
    }
    
    /*Adds an email address to your system with an array of variables, 
     * $vars, that contain their first name and last name and an array
     * of lists they will be added to.
     * 
     * For more info see: http://docs.sailthru.com/api/email
     */
    $result = $sailthru->setEmail($_POST["email"], $vars, $lists);

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
            <label for="list">Email</label><input type="text" name="email" ><br/>
            <label for="first_name">First Name</label><input type="text" name="first_name" ><br/>
            <label for="last_name">Last Name</label><input type="text" name="last_name" ><br/>
            <label for="list">List 1</label><input type="text" name="list1"><br/>
            <label for="list">List 2</label><input type="text" name="list2"><br/>
            <label for="list">List 3</label><input type="text" name="list3"><br/>
            <label for="list">List 4</label><input type="text" name="list4"><br/>
            <input type="submit" value="add" style="margin-left:200px;">
        </form>
    </body>
</html>

