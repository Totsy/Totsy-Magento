<?php
include("sailthru_config.php");

$result = $sailthru->getLists();
$lists = $result["lists"];

if (!empty($_POST)) {
    /*Creates an campaign message and sends it to the list,
     * $_POST['list'], at the scheduled time.  If you specify a template,
     * from_name, from_email, subject, and content_html aren't necessary.
     * 
     * For more info see: http://docs.sailthru.com/api/blast
     */
    
    
    $result = $sailthru->scheduleBlast($_POST['name'], 
            $_POST['list'],
            $_POST['schedule_time'],
            $_POST['from_name'],
            $_POST['from_email'],
            $_POST['subject'],
            $_POST['content_html'],
            null);
    
    
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
            <label for="list">Blast Name</label><input type="text" name="name" ><br/>
            <label for="list">List</label><select name="list">
                <? foreach ($lists as $list): ?>
                    <option><?= $list["name"] ?></option>
                <? endforeach ?>
            </select><br style="clear:both;"/>
            <label for="schedule_time">Schedule Time</label><input type="text" name="schedule_time" >Enter any date as a string. If you want the Mass email to go out now enter "now".<br/>
            <label for="from_name">From Name</label><input type="text" name="from_name" ><br/>
            <label for="from_email">From Email</label><input type="text" name="from_email">Must be verified.  Click <a href="https://sailthru.com/admin/verify">here</a> to do so.<br/>
            <label for="subject">Subject</label><input type="text" name="subject"><br/>
            <label for="content_html">HTML Content</label><textarea name="content_html">
                <h1>Hi {email},<h1>
            </textarea><br/>
            <input type="submit" value="Send" style="margin-left:200px;"><br/>
        </form>
    </body>
</html>
