<style> 
#stickywrap  { background:url("../media/import/brands/12days/bgwhite.gif") top center repeat #ffffff; }  

#12days-div { width: 950px; padding: 0px; margin: 0px auto; }
#12days-div img { padding: 0px; margin: 0px; }

.12days-banner { margin: 0px auto; padding: 0px; }

.left {
	display: inline-block;
	float: left;
	width: 370px;
	padding: 0px 10px 0px 95px;
	margin: 0px;
	text-align: center;
}
.right {
	display: inline-block;
	float: right;
	width: 370px;
	padding: 0px 95px 0px 10px;
	margin: 0px;
	text-align: center;
}
.center {
	width: 475px;
	padding: 0px;
	margin: 0px auto;
	text-align: center;
}

#fields {
	background: #f6f6f6 !important;
	font-size: 11px;
	font-weight: bold;
	color: #000000;
}

#fields input {
	height: 25px;
	width: 360px;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	-moz-background-clip: padding;
	-webkit-background-clip: padding-box;
	background-clip: padding-box;
	border: 1px solid #f4f4f5;
	padding: 2px 0px 0px 15px;
	margin: 12px 0px 0px 0px;
	color: #999999;
	font-size: 16px;
	-webkit-box-shadow: inset 5px 5px 5px 0px #e4e4e5;
	-moz-box-shadow: inset 5px 5px 5px 0px #e4e4e5;
	box-shadow: inset 5px 5px 5px 0px #e4e4e5;
}
.submit-button {
	background: #ee2e24;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	width: 125px;
	color: #ffffff;
	height: 30px;
	line-height: 30px;
	vertical-align: middle;
	font-size: 14px;
	font-weight: bold;
	margin: 0px auto 85px auto;
}
.submit-button a {
	display: block;
	color: #ffffff;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}
.submit-button a:hover {
	background: #ff6961;
	color: #ffffff;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
}
.cleanbreak {
	clear: both; 
	font-size:0px; 
	height: 1px;
	margin: 0px;
	padding: 0px;
}

#success_message {
    background: #f6f6f6 !important;
    font-size: 25px;
    font-weight: bold;
    color: #000000;
    display: none;
    text-align: center;
    margin: 20px 0px 20px 0px;
}

</style>

<script type="text/javascript">

jQuery(document).ready( function() {
    
    var invalidCount = 0;
    
    jQuery(".submit-button").click( function() {
		jQuery("input[type=text]").each(function(i){
		    if(this.value=="") {
		    	invalidCount ++;
		    }
		});
    
		if(invalidCount==0){
		    jQuery.ajax({type: 'POST',
		   	    url: '../dev/totsytwelvedays/totsy_twelve_days.php', 
		   	    data: jQuery("#cluesform").serialize(), 
		   	    success: function () {
		   	       jQuery("#cluesform").hide();
		   	       jQuery("#success_message").show();
		   	    } 
		   	});
		} else {
			invalidCount = 0;
		   	alert("Please fill in all fields");
		}    
    });
});

</script>


<div id="12days-div">
<div class="12days-banner"> <a href="../12daysoftotsy"><img src="../media/import/brands/12days/12days-banner.jpg"></a></div>
<div id="success_message">Thanks for participating. Good Luck!</div>

<form id="cluesform">
<div id="fields">

	<div class="center">
		<input name="email" type="text" value="" />
		<br />ENTER TOTSY EMAIL
		<br />Not a member?  <a href="../customer/account/create/">Join Here</a>
		<input name="twitter_account" type="text" value="" />
		<br />TWITTER HANDLE
	</div>

	<div style="clear: both;">&nbsp;</div>        

	<div class="left">
		<input name="clue_1" type="text" value="" />
		<br />Clue 1
		<input name="clue_2" type="text" value="" />
		<br />Clue 2
		<input name="clue_3" type="text" value="" />
		<br />Clue 3
		<input name="clue_4" type="text" value="" />
		<br />Clue 4
		<input name="clue_5" type="text" value="" />
		<br />Clue 5
		<input name="clue_6" type="text" value="" />
		<br />Clue 6
	</div>
	<div class="right">
		<input name="clue_7" type="text" value="" />
		<br />Clue 7
		<input name="clue_8" type="text" value="" />
		<br />Clue 8
		<input name="clue_9" type="text" value="" />
		<br />Clue 9
		<input name="clue_10" type="text" value="" />
		<br />Clue 10
		<input name="clue_11" type="text" value="" />
		<br />Clue 11
		<input name="clue_12" type="text" value="" />
		<br />Clue 12
	</div>

	<div style="clear: both;">&nbsp;</div>      

	<div class="center">
		<div class="submit-button"><a href="#">SUBMIT</a></div>
		RULES<br />
No purchase necessary. Form must be submitted by <b>5pm EST on 12/30/12.</b> <br />
For more information, see terms and conditions here.
	</div>
</form>

<div class="cleanbreak" style="height: 15px;">&nbsp;</div>
</div>
</div>