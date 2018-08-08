<?php
function debug($msg, $die=false) {
	echo "<pre>";
	print_r($msg);
	echo "</pre>";
	if($die) {
		die();
	}
}
?>
<!DOCTYPE html>
<html>
<head><!--260x355 current min size-->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
	body {
		font-family:Arial, sans-serif;
		background: white !important;
	}
	.a {
		width:100%; 
		float:left;
		margin-bottom:5px;
		margin-top:5px;
	}
	input[type=text] {
		line-height: 35px;
	    font-size: 1rem;
	    margin-bottom: 0 !important;
	    padding: 5px 14px;
	    box-shadow: none;
	    font-family: 'OpenSans','Lato',arial,sans-serif;
	    background: #fff;
	    color: #333;
	    border-radius: 0;
	    min-height: 35px;
	    width: 265px !important;
	}
	input[type="button"] {
		width: 100% !important;
		height: 50px !important;
		border-radius: 8px !important;
		margin-top: 1px !important;
		background: #F16D26 !important;
		font-size: 20px !important;
		color: white !important;
	}
	.errorMessage {
		color: #a94442;
	}
	.successMessage {
		color: #327EC8;
	}
	.payInfo {
		font-size: 14px;
	    font-style: italic;
	    background-color: #fff2a8;
		text-align: left;
		margin-bottom: 10px;
	}

	#loading {
		display: none;
		text-align: center;
	}
	
    </style>
	<script src="https://code.jquery.com/jquery-1.12.1.min.js"></script>
	<script>
		
		var theVin = "";
		var thePlate = "";
		var theName = "";
		var theEmail = "";
		var thePhone = "";
		var amtDMVTotal = "";
		var amtServiceFee = "";
		var amtConFee = "";
		var amtTotalPaid = "";
		
		
		function emailErrorMessage(theErrorMsg) {
			$.ajax("http://18.188.190.8/sendErrorEmail.php?VIN="+theVin+"&PLATE="+thePlate+"&NAME="+theName+"&EMAIL="+theEmail+"&PHONE="+thePhone+"&msg="+theErrorMsg);
		}
		
		function conWrite(title, msg) {
			console.log("====== START " + title + " ======");
			console.log(msg);
			console.log("====== END " + title + " ======");
		}

		function doToggle() {
			$("#theForm").toggle();
			$("#loading").toggle();			
		}

		function getParameterByName(name) {
			var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
			return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
		}
		var org = "QAT"; var _dealid;
		switch(org){
			case "QAT":
				var donateUrl = "payQAT";
				var tokenUrl = 'getTokenQAT';
				break;
			default: break;
		}
		function lastStep(){
			//$_REQUEST['dealid'],$_REQUEST['dealstatus']
			var data = new Object();
			data.dealid = _dealid; 
			data.dealstatus="C";
			$.ajax("http://18.221.179.251/avrs/new_avrs_api/public/site/../index.php/exampleRenewRegistrationRest?dealid="+_dealid+"&dealstatus=C",{
				method:"GET",
				dataType:"json",
				success:function(data){
					conWrite("Payment Data", data);
				
					//make sure all Fees look good, status=FR, has a deal-id and no errors
					//show BT form with amount
					conWrite("Paid Properly", data.paidProperly);
				
					// Create an ajax call here to send an email to Uni about the successful purchase.
					//$.ajax("http://18.188.190.8/sendEmail.php?action=send");
					
					
					$.ajax("http://18.188.190.8/sendEmail.php?action=send", {
						method:"GET",
						success:function(data){
							console.log("GOING TO REDIRECT NOW!!!");
							$(location).attr('href', 'http://www.quickautotags.com/thank-you-for-your-submission');
						}
					});
					/*
					//send them email here? or when processing payment, or both
					$(".successMessage").html("DMV processed your order successfully! Thank you for using QuickAutoTags!");
					$("#theForm").show();
					$(".step1").hide();
					$(".step2").hide();
					$("#loading").hide();
					*/
				}
			});
		}
	</script>
	<?php
	if(isset($_REQUEST['payment_method_nonce'])) { 
		?>
			<style>
				#loading {
					display: block;
				}
				#theForm {
					display: none;
				}
			</style>
			<script>
			var data = new Object();
			data.amount = <?=$_REQUEST['amount']?>;
			_dealid = <?=$_REQUEST['avrs_dealid']?>;
			data.payment_method_nonce = "<?=$_REQUEST['payment_method_nonce']?>";
			$.ajax("http://18.221.179.251/avrs/new_avrs_api/public/site/../index.php/"+donateUrl,{
				method:"POST",
				dataType:"json",
				data:data,
				success:function(data){
					console.log(data);
					//alert("Payment Processed! Sending info to the DMV...");
					lastStep();
				}
			});
			</script>
		<?php
	}
	?>
</head>

<body>
	<div id="loading"><img src="/processing.gif"></div>
	<div id="theForm" style="width:100%;">
		<div class="successMessage"></div>
		<div class="errorMessage"></div>
		<div class="step1">
			<p class="a">Name:<br/><input type="text" id="name" /></p>
			<p class="a">Email:<br/><input type="text" id="email" /></p>
			<p class="a">Phone Number:<br /><input type="text" id="phone" /></p>
			<p class="a">Plate:<br/><input type="text" id="plate" /></p>
			<p class="a">VIN (last 3 digits):<br/><input type="text" id="vin" /></p>
			<!--
			<p class="a" style="margin-top: 20px;"><b>Delivery Address</b></p>
			<p class="a">Address:<br/><input type="text" id="address" /></p>
			<p class="a">City:<br/><input type="text" id="city" /></p>
			<p class="a">State:<br/><input type="text" id="state" /></p>
			<p class="a">Zip:<br/><input type="text" id="zip" /></p>
			-->
			<p class="a"><input id="address" type="checkbox"> Change of Address</p>
			<input type="button" value="What Are My Fees?" onclick="step1()" style="width:200px;height:40px;border-radius:8px;background:#f69222;">
		</div>
		<form class="step2" id="checkout" method="post" action="example.php" style="display:none;">
		  <table>
		  	<tr><td colspan=2><b>Summary of Fees</b></td></tr>
		  	<tr><td>DMV Fees:</td><td id="json_total"></td></tr>
		  	<!--TODO: move AVRS fees from "DMV Fees" to "Service Fee", handle insurance N case-->
		  	<tr><td>Service Fee:</td><td id="json_unifees"></td></tr>
			<tr><td>Convenience Fee:</td><td id="json_confees"></td></tr>
		  	<tr><td>Total:</td><td id="json_chargeUser"></td></tr>
		  	<tr><td colspan=2 style="text-align: center;">
				<br />
				<img src="http://18.221.179.251/avrs/new_avrs_api/braintree.jpg" />
				<br />
				<div class='payInfo'>Your payment is being processed through Braintree, a Paypal company.</div>
			</td></tr>
		  	<tr><td colspan=2><b>Enter Payment Info</b></td></tr>
		  </table><br/>
		  <div id="payment-form"></div>
		  <input type="hidden" name="amount" />
		  <input type="hidden" name="da_org" />
		  <input type="hidden" name="avrs_dealid" />
		  <script>
			$("input[name='da_org']").val(getParameterByName("org"));
		  </script>
		  <input type="submit" value="Submit">
		</form>
		
		<iframe src="http://18.188.190.8/setCookie.php" name="iframe_a" style="border: none; height: 1px; width: 1px;"></iframe>
		
		<script src="https://js.braintreegateway.com/js/braintree-2.20.0.min.js"></script>
		<script>
		function step1(){
			var data = new Object(); //!(" ".trim()) is true
			var rr=["vin","plate","email","name","phone"];
			for(var ii=0;ii<rr.length;ii++){
				if(!$("#"+rr[ii]).val().trim()){
					$(".errorMessage").html("VIN, Plate, Name, Email, and Phone Number are all required!"); 
					return;
				}
			}
			doToggle();
			data.vin = $("#vin").val(); 
			data.plate = $("#plate").val();
			
			//============================================
			// Set our global variables to be used later.
			//============================================
			theVin = data.vin;
			thePlate = data.plate;
			theName = $("#name").val();
			theEmail = $("#email").val();
			thePhone = $("#phone").val();


			//==============================================================
			// Check to see if the user selected the "Change of Address"
			// option. If they did, we need to send an email off to Uni
			// and then show the user a nice message stating that 
			// someone will be in contact with them shortly.
			//==============================================================
			if($("#address").is(':checked')) { 
				doToggle();
				
				$.ajax("http://18.188.190.8/sendAddressEmail.php?VIN="+theVin+"&PLATE="+thePlate+"&NAME="+theName+"&EMAIL="+theEmail+"&PHONE="+thePhone, {
					method: "GET",
					success: function(theData) {
						$(".successMessage").html("<b>Thank you!</b><br /><br />Someone from QuickAutoTags.com will be in touch shortly to help you with your change of address.");
						$("#theForm").show();
						$(".step1").hide();
						$(".step2").hide();
						$("#loading").hide();
					}
				});



			} else {
				
				$.ajax("http://18.188.190.8/sendEmail.php?VIN="+theVin+"&PLATE="+thePlate+"&NAME="+theName+"&EMAIL="+theEmail+"&PHONE="+thePhone, {
					method: "GET",
					success: function(data) {
						$.ajax("../index.php/exampleRenewRegistrationFirst?vin="+theVin+"&plate="+thePlate,{
							method:"GET",
							dataType:"json",
							success:function(data){
								//make sure Transaction is ready, status=R, has a deal-id and no errors
								step1point5(data.dealid);
							}
						});
					}
				});	
			}			
		}
		function step1point5(dealid){
			var data = new Object();//$_REQUEST['dealid'],$_REQUEST['dealstatus']
			data.dealid = dealid; data.dealstatus="FR";
			$("input[name='avrs_dealid']").val(dealid);
			$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+dealid+"&dealstatus=FR",{
				method:"GET",
				dataType:"json",
				success:function(data){
					console.log(data);
					//alert(data.deal_id); alert(data.chargeUser); alert(data.deal_status);
					//make sure all Fees look good, status=FR, has a deal-id and no errors
					if(data.error==true || data.deal_status=="E"){
						//DO NOT SHOW STEP 2, return, and alert user of whats going on if code is 'bad'
						//extra /deals call to get error information + handle specific error properly
						$.ajax("../index.php/checkError?dealid="+data.deal_id,{
							method:"GET", dataType:"json",
							success:function(data){
								//Example Return:
								//{"errorcode":"CADMV\/D365","errortext":" - SMOG CERT REQUIRED"}
								var badCodes = ["CADMV/Q201","CADMV/D108","CADMV/D365","CADMV/Q046","CADMV/Q035"];
								//0 - Q201 - REG SUSP - CALL 1-800-777-0133. REFER CUST TO DMV TO POST FEES.
								//1 - D365 - SMOG CERT REQUIRED FROM STAR STATION
								//2 - Q046 - CLEARING INQUIRY REQUIRED (CLEAR RDF?)
								var userMessages = [
									"Your registration is currently suspended due to insurance-related issues. QuickAutoTags will contact you at "+$("#email").val()+" with instructions to renew your registration.",
									"Your registration renewal requires a Smog certification from the provider listed below. QuickAutoTags will follow up with you at "+$("#email").val()+" to get any documentation needed.",
									"Your registration renewal requires a Smog certification from the provider listed below. QuickAutoTags will follow up with you at "+$("#email").val()+" to get any documentation needed.",
									"You have already renewed or started a renewal for this registration (either at the DMV or elsewhere), and will have to complete it there. QuickAutoTags cannot process another renewal for your Plate+VIN.",
									"Invalid Plate or VIN. Please enter your License Plate and last 3 digits of your VIN correctly and double-check to make sure you don't have any typos."
								];
								//for messages we don't have codes for:
								var badMessages = ["FILE CODE REQUIRED FOR THIS CONFIGURATION","REG SUSP","REFER CUST TO DMV TO POST FEES","3P-VIN UNEQUAL IN VR RECORD LIC","NO RECORD FOUND","CLEARING INQUIRY REQUIRED","TTC NOT VALID-SALV RETENTION ON FILE","BPA SYSTEM IS DOWN","ELP RECORD ON FILE","NAME CODE-VS-DATA FIELDS"];
								var cleanMessages = ["Please double check your plate number. QuickAutoTags will call you.","Your registration is currently suspended due to insurance-related issues. QuickAutoTags will contact you at "+$("#email").val()+" with instructions to renew your registration.","Your registration is currently suspended due to insurance-related issues. QuickAutoTags will contact you at "+$("#email").val()+" with instructions to renew your registration.","Your Plate and VIN do not match. Please double check both values.","Your plate number is not valid or is not in the California DMV Database. Please double check this value.","You have already renewed or started a renewal for this registration (either at the DMV or elsewhere), and will have to complete it there. QuickAutoTags cannot process another renewal for your Plate+VIN.","Your vehicle has been reported salvage to the DMV - you must re-register your vehicle as a salvage vehicle. QuickAutoTags will contact you for further details and instructions","System is only available between 7AM and 10PM PT. Please try again during those hours.","The entered plate number is invalid. Please double check it.","Must process a Change Of Address. QuickAutoTags will contact you with further instructions and steps."];

								var knownError = false; var knownCode = false; var isSmog = false;
								for(var jj=0;jj<badCodes.length;jj++){
									if(data.errorcode==badCodes[jj]){
										knownError = true; var base_msg = userMessages[jj];
										alert(base_msg+"\nDMV Error Code: "+data.errorcode+data.errortext);
									}
								}
								if(data.errorcode=="CADMV/D365" || data.errorcode=="CADMV/D108"){
									isSmog=true;
								}
								if(!knownError){
									for(var ll=0;ll<badMessages.length;ll++){
										if(data.errortext.indexOf(badMessages[ll])!=-1){ //errortext contains badMessage
											knownCode = true; var base_msg = cleanMessages[ll];
											alert(base_msg+"\nDMV Error Code: "+data.errorcode+data.errortext);
										}
									}
								}
								if(!knownError && !knownCode){
									var dat_msg = (data.errorcode.indexOf("CADMV")!==-1)? "Your transaction cannot be processed at the DMV at this time. This may be an address issue, an insurance issue, or a recall issue. QuickAutoTags will contact you to let you know any actions you may have to take (at the DMV or otherwise)." : "AVRS Error" ;
									alert(dat_msg+"\nDMV Error Code: "+data.errorcode+data.errortext);
								}
								//email QAT/Uni with /checkError and /deals result for given deal-id so they can check what the error is and handle appropriately + get back to customer.
								//$("#email").val() used here
								alert("end of /checkError async call"); alert($("input[name='amount']").val());
							}
						});
						if(!isSmog){return;}
						console.log("Smog (or other 'can still pay despite error') case")
					} else {
						console.log("Not E case");
					}
					
					var uniFees = data.unifees + 9.5;
					var conFees = 0;
					if(uniFees > 29) {
						conFees = (uniFees - 29);
						uniFees = 29;
					}
					
					amtDMVTotal = "$"+(data.total-9.5).toFixed(2);
					amtServiceFee = "$"+uniFees.toFixed(2);
					amtConFee = "$"+conFees.toFixed(2);
					amtTotalPaid = "$"+(data.chargeUser).toFixed(2);
						
					$.ajax("http://18.188.190.8/sendEmail.php?TOTAL="+amtTotalPaid+"&DMVTOTAL="+amtDMVTotal+"&SERVICEFEE="+amtServiceFee+"&CONFEE="+amtConFee, {
						method: "GET",
						success: function(theData) {
							
							
							//show BT form with amount
							$("input[name='amount']").val(data.chargeUser);//use 1 when testing
							//initBT();if have to init after amount
							$("#json_total").html("$"+(data.total-9.5).toFixed(2)); 
							$("#json_unifees").html("$"+uniFees.toFixed(2));
							$("#json_confees").html("$"+conFees.toFixed(2));
							$("#json_chargeUser").html("$"+(data.chargeUser).toFixed(2));
							$(".step1").hide();
							$(".step2").show();
							doToggle();						
						}
					});
					
				}
			});
		}
		function initBT(){
			$.ajax("../index.php/"+tokenUrl, {
				method:"GET",
				dataType:"json",
				success:function(data){
					console.log(data.result);
					var clientToken = data.result;
					braintree.setup(clientToken, "dropin", {
					  container: "payment-form"
					});
				}
			});
		}
		initBT();
		</script>
	</div>
</body>
</html>