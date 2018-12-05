<!DOCTYPE html>
<html>
<head><!--260x355 current min size-->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
		input[type=text], .a select {
		    line-height: 35px;
		    font-size: 1rem;
		    margin-bottom: 0 !important;
		    padding: 5px 14px;
		    box-shadow: none;
		    font-family: sans-serif;
		    background: #fff;
		    color: #333;
		    border-radius: 0;
		    min-height: 35px;
		    width: 265px !important;
		}
		.a {
			width: 100%;
		    float: left;
		    margin-bottom: 5px;
		    margin-top: 5px;
		    font-family:sans-serif;
		}
		#theForm{
			border-radius: 5px;
		    background: #eee;
		    padding-top: 10px;
		    padding-bottom: 10px;
		    background-image: url('oj_arrow2.png');
		    background-repeat: no-repeat;
		    background-position: top center;
		    background-size: 48px 16px;
		}
		.theFormStep2{background:rgb(252, 150, 35)!important;}
		#address{width:24px;height:24px;}
		p#coa{line-height:24px;font-size:18px;}
		#emdail{margin-top:5px;}
		#belowTable{padding:0;margin:0;}
		#receiptTable{margin:0 auto;}
		#receiptTable tr td.subhead{padding:5px;color:#fff!important;text-align:center!important;}
		#receiptTable tr td:first-child{padding:5px;text-align:right;color:#fff;}
		#receiptTable tr td:last-child{padding:5px;text-align:left;color:#fff;}
		input.error{background:rgba(255,0,0,0.3);border:1px solid red;}
	</style>
	<script src="https://code.jquery.com/jquery-1.12.1.min.js"></script>
	<script>
	//UTIL
	function getParameterByName(name) {
		var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
		return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
	}
	//1. Plate
	function validatePlate(plate){
		//var re = /^$|([A-Za-z\-0-9]){1,7}$/;
		//var re = /^$|([A-Z]|[a-z]|[0-9]){1,7}$/;
		var re = /^$|^[0-9A-Za-z]{1,7}$/;
		return re.test(plate);
	}
	//2. VIN
	function validateVIN(vin){
		var re = /^$|^\d{3}$/;
		return re.test(vin);
	}
	//3. Email
	function validateEmail(email) {
	    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	    return re.test(String(email).toLowerCase());
	}
	//4. ZIP
	function validateZip(zip){
		var re = /(^\d{5}$)|(^\d{5}-\d{4}$)/;
		return re.test(zip);
	}
	var org = "QAT"; var _dealid; var _sid2;
	switch(org){
		case "QAT":
			var donateUrl = "payQAT";
			var tokenUrl = 'getTokenQAT';
			break;
		default: break;
	}
	window.processingPayment=false;
	//END UTIL
	function lastStep(){
		var data = new Object();//$_REQUEST['dealid'],$_REQUEST['dealstatus']
		data.dealid = _dealid; data.dealstatus="C"; data.sid = _sid2;
		if(parseInt(_dealid)==1){window.location.href = "ty.php";return;}
		$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+_dealid+"&dealstatus=C"+"&sid="+_sid2,{
			method:"GET",
			dataType:"json",
			success:function(data){
				console.log(data);
				//make sure all Fees look good, status=FR, has a deal-id and no errors
				//show BT form with amount
				console.log("PAID PROPERLY: "+data.paidProperly);//hopefully true/1
				//send them email here? or when processing payment, or both
				console.log("DMV processed your order successfully! Thank you for using QuickAutoTags!");
				window.location.href = "ty.php?sid="+_sid2;
			}
		});
	}
	</script>
	<?php
	//echo var_dump($_REQUEST['payment_method_nonce']);
	//echo var_dump($_REQUEST['amount']);
	if(isset($_REQUEST['payment_method_nonce'])){
		?>
			<script>
			window.processingPayment=true;
			var data = new Object();
			//pass in payment info
			console.log(<?=$_REQUEST['amount']?>);//1 when testing, chargeUser otherwise
			data.amount = <?=$_REQUEST['amount']?>;
			console.log(<?=$_REQUEST['avrs_dealid']?>);
			_dealid = <?=$_REQUEST['avrs_dealid']?>;
			console.log(<?=$_REQUEST['uni_sid']?>);
			_sid2 = "<?=$_REQUEST['uni_sid']?>";
			data.payment_method_nonce = "<?=$_REQUEST['payment_method_nonce']?>";
			//pass in full user submission for receipts to user+uni
			data.pay_email = "<?=$_REQUEST['pay_email']?>";
			data.pay_plate = "<?=$_REQUEST['pay_plate']?>";
			data.pay_vin = "<?=$_REQUEST['pay_vin']?>";
			data.pay_zip = "<?=$_REQUEST['pay_zip']?>";
			data.pay_orderType = "<?=$_REQUEST['pay_orderType']?>";
			data.pay_orderTypeText = "<?=$_REQUEST['pay_orderTypeText']?>";
			data.pay_dmv_fees = "<?=$_REQUEST['pay_dmv_fees']?>";
			data.pay_service_fees = "<?=$_REQUEST['pay_uni_fees']?>";
			data.pay_uni_fees = "<?=$_REQUEST['pay_uni_fees']?>";
			data.pay_bt_fees = "<?=$_REQUEST['pay_bt_fees']?>";
			data.pay_total_fees = "<?=$_REQUEST['pay_total_fees']?>";
			data.pay_address = "<?=$_REQUEST['pay_address']?>";
			data.pay_dealid = "<?=$_REQUEST['avrs_dealid']?>";
			data.uni_sid = "<?=$_REQUEST['uni_sid']?>";
			//now ready for request
			console.log(data);
			$.ajax("../index.php/"+donateUrl,{
				method:"POST",
				dataType:"json",
				data:data,
				success:function(data){
					console.log(data);
					alert("Payment Processed! Sending info to the DMV...");
					lastStep();
				}
			});
			</script>
		<?php
	}
	?>
</head>
<body>
	<div id="overlay" style="display:none;position:absolute;left:0;top:0;z-index: 1001;width:100%;height: 150%;background:rgba(0,0,0,.7);"><img src="load.gif" style="width:20%;left:40%;top:25%;position:absolute;"></div>
	<div id="wrapper" style="width:100%;margin:0 auto;background:rgb(255,138,5);color:#fff;">
		<div id="logo" style="width:300px;height:100px;margin:0 auto;background:url('qatlogo.png');background-size:300px 95px;background-position-y:5px;background-repeat:no-repeat;"></div>
		<h1 style="font-family:sans-serif;font-size:20px;font-weight:normal;text-align:center;margin-bottom:10px;margin-top:10px;">Renew Your Auto Registration Today!</h1>
		<!--p style="margin-top:2px;font-weight:bold;font-size:14px;text-align:center;">Licensed by the California DMV</p-->
		<div id="form">
			<div id="theForm" style="width:100%;text-align:center;">
				<div class="successMessage"></div>
				<div class="errorMessage"></div>
				<div class="step1">
					<p class="a"><input type="text" id="email" placeholder="Email Address"></p>
					<p class="a"><input type="text" id="plate" placeholder="License Plate"></p>
					<p class="a"><input type="text" id="vin" placeholder="Last 3 Digits of VIN"></p>
					<p class="a"><input type="text" id="zip" placeholder="ZIP Code"></p>
					<p class="a">
						<select id="orderType">
							<option value="1">Registration Renewal</option>
							<option value="2">Replacement Sticker</option>
							<option value="3">Replacement Card<!--Duplicate Registration?--></option>
							<!--option value="8">Test Reg ($1 any plate/vin)</option-->
						</select>
					</p>
					<p class="a" id="coa" style="color:#000;"><input id="address" type="checkbox"><span style="position:relative;bottom:6px;">Change of Address</span></p>
					<input type="button" value="NEXT" onclick="step1()" style="cursor:pointer;width:200px;height:58px;color:#fff;font-size:20px;border-radius:8px;background:rgb(255,138,5);z-index:99999;">
				</div>
				<form class="step2" id="checkout" method="post" action="example_new.php" style="display:none;">
				  <table id="receiptTable" style="width:100%;">
				  	<tr><td class="subhead" colspan=2><b>Summary of Fees</b></td></tr>
				  	<tr><td>DMV Fees:</td><td id="json_total"></td></tr>
				  	<!--TODO: move AVRS fees from "DMV Fees" to "Service Fee", handle insurance N case-->
				  	<tr><td>QuickAutoTags Fee:</td><td id="json_unifees"></td></tr>
				  	<tr><td>Convenience Fee:</td><td id="json_btfees"></td></tr>
				  	<tr><td>Total:</td><td id="json_chargeUser"></td></tr>
				  	<tr><td class="subhead" colspan=2><b>Enter Payment Info</b></td></tr>
				  </table><br id="belowTable" />
				  <div id="payment-form"></div>
				  <input type="hidden" name="amount" />
				  <input type="hidden" name="da_org" value="QAT" />
				  <input type="hidden" name="avrs_dealid" />
				  <input type="hidden" name="uni_sid" />
				  <input type="hidden" name="pay_email" />
				  <input type="hidden" name="pay_plate" />
				  <input type="hidden" name="pay_vin" />
				  <input type="hidden" name="pay_zip" />
				  <input type="hidden" name="pay_dmv_fees" />
				  <input type="hidden" name="pay_uni_fees" />
				  <input type="hidden" name="pay_bt_fees" />
				  <input type="hidden" name="pay_total_fees" />
				  <input type="hidden" name="pay_orderType" />
				  <input type="hidden" name="pay_orderTypeText" />
				  <input type="hidden" name="pay_address" />
				  <input type="submit" value="NEXT" style="cursor:pointer;width:200px;height:58px;color:#fff;font-size:20px;border-radius:8px;background:rgb(255,138,5);z-index:99999;">
				</form>
			</div>
		</div>
		<script src="https://js.braintreegateway.com/js/braintree-2.20.0.min.js"></script>
		<script>
		function loadOverlay(){$("#overlay").toggle();}
		$(document).ready(function(){
			$(".step1 .a input[type='text']").click(function(){
				if($(this).hasClass("error")){$(this).removeClass("error");}
			});
			if(window.processingPayment){loadOverlay();}
		});
		function step1(){
			var data = new Object(); //!(" ".trim()) is true
			var rr=["vin","plate","email","zip"];

			var message = ""; var valid=true;
			for(var ii=0;ii<rr.length;ii++){if(!$("#"+rr[ii]).val().trim()){message ="VIN, Plate, Zip, and Email are all required!"; valid=false;}}
			if(!validateZip($("#zip").val().trim())){message+="\nInvalid ZIP code."; valid=false;$("#zip").addClass("error");}
			if(!validateEmail($("#email").val().trim())){message+="\nInvalid Email."; valid=false;$("#email").addClass("error");}
			if(!validateVIN($("#vin").val().trim())){message+="\nInvalid VIN."; valid=false;$("#vin").addClass("error");}
			if(!validatePlate($("#plate").val().trim())){message+="\nInvalid Plate."; valid=false;$("#plate").addClass("error");}
			if(!valid){alert(message);return;}

			for(var ij=0;ij<rr.length;ij++){
				$("input[name='pay_"+rr[ij]+"']").val($("#"+rr[ij]).val().trim());
			}
			$("input[name='pay_orderType']").val($("#orderType option:selected").val());
			$("#input[name='pay_orderTypeText']").val($("#orderType option:selected").text());
			$("input[name='pay_address']").val(($("#address").is(":checked"))? "yes" : "no");

			if(parseInt($("#orderType option:selected").val())>5){
				toPaymentF();$("input[name='avrs_dealid']").val(1);return;
			}

			data.vin = $("#vin").val(); data.plate=$("#plate").val();
			data.type = $("#orderType option:selected").val();
			data.email = $("#email").val(); data.zip = $("#zip").val();
			loadOverlay();
			$.ajax("../index.php/exampleRenewRegistrationFirst?vin="+data.vin+"&plate="+data.plate+"&type="+data.type+"&email="+data.email+"&zip="+data.zip,{
				method:"GET",
				dataType:"json",
				success:function(data){
					//make sure Transaction is ready, status=R, has a deal-id and no errors
					console.log(data.dealid);console.log(data.sid);
					step1point5(data.dealid,data.sid);
				}
			});
		}
		function clearRDFAndResubmitToPayment(old_data, sid){
			var data = new Object(); data.vin = $("#vin").val(); data.plate=$("#plate").val();
			data.type = $("#orderType option:selected").val(); data.sid = sid;
			$.ajax("../index.php/clearRDF?vin="+data.vin+"&plate="+data.plate+"&type="+data.type+"&sid="+data.sid,{
				method:"GET",
				dataType:"json",
				success:function(data){
					//in this case we dont ensure Transaction is status=R, has a deal-id and no errors
					//instead we attach result of attempted FR/fee check along with attempted C for UNI
					//if not worked, they still pay and he can just manually create & complete deal
					console.log(data.dealid);
					$("input[name='avrs_dealid']").val(data.dealid);
					$("input[name='uni_sid']").val(window._sid);
					$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+data.dealid+"&dealstatus=FR",{
						method:"GET",
						dataType:"json",
						success:function(data){
							window.eRRR_return = data; //should be same as before but still just to be sure
							$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+data.dealid+"&dealstatus=C",{
								method:"GET",
								dataType:"json",
								success:function(data){
									//email UNI result of attempted deal Completion
									//if not worked, they still pay and he can just manually complete deal
									toPayment(window.eRRR_return);
								}
							});
						}
					});
					//toPayment(window.eRRR_return);
				}
			});
		}
		function step1point5(dealid,sid){
			var data = new Object();//$_REQUEST['dealid'],$_REQUEST['dealstatus']
			data.dealid = dealid; data.dealstatus="FR";
			$("input[name='avrs_dealid']").val(dealid);
			$("input[name='uni_sid']").val(sid);
			$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+dealid+"&dealstatus=FR&sid="+sid,{
				method:"GET",
				dataType:"json",
				success:function(data){
					console.log(data);
					window.eRRR_return = data; window._sid = data.sid;
					//alert(data.deal_id); alert(data.chargeUser); alert(data.deal_status);
					//make sure all Fees look good, status=FR, has a deal-id and no errors
					if(data.error==true || data.deal_status=="E"){
						//DO NOT SHOW STEP 2, return, and alert user of whats going on if code is 'bad'
						//extra /deals call to get error information + handle specific error properly
						console.log("E case");
						$.ajax("../index.php/checkError?dealid="+data.deal_id+"&sid="+window._sid,{
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
								console.log("end of /checkError async call, amt:");
								console.log($("input[name='amount']").val());
								//if smog (or any other error where they can still pay), don't return
								console.log("is smog bool:");
								console.log(isSmog);//breakpoint here to 'manually' handle/document test cases
								if(isSmog){
									console.log("this is an error where you can proceed to pay still");
									//but need to clear RDF
									clearRDFAndResubmitToPayment(window.eRRR_return, window._sid);
								} else {
									console.log("this is an error where you cannot proceed to pay");
								}
							}
						});
					} else {console.log("Not E case");toPayment(data);}
				}
			});
		}
		function toPayment(data){
			alertUserCOA();
			loadOverlay();
						//show BT form with amount
			//FOR SMOG, MAY HAVE TO SET MANUALLY OR RETURN FEES DIFFERENTLY FROM SERVER?
			$("input[name='amount']").val(data.chargeUser);//use 1 when testing
			//initBT();if have to init after amount
			$("#json_total").html("$"+(data.total).toFixed(2)); 
			$("#json_unifees").html("$"+(data.unionly).toFixed(2));
			$("#json_btfees").html("$"+(data.btfees).toFixed(2));
			$("#json_chargeUser").html("$"+(data.chargeUser).toFixed(2));

			$("input[name='pay_dmv_fees']").val((data.total).toFixed(2));
			$("input[name='pay_uni_fees']").val((data.unionly).toFixed(2));
			$("input[name='pay_bt_fees']").val((data.btfees).toFixed(2));
			$("input[name='pay_total_fees']").val((data.chargeUser).toFixed(2));

			$(".step1").hide();$(".step2").show();
			$("#theForm").addClass("theFormStep2");
		}
		function toPaymentF(){
			alertUserCOA();
			$("input[name='amount']").val(2);

			$("#json_total").html("$"+(1).toFixed(2));
			$("#json_unifees").html("$"+(1).toFixed(2));
			$("#json_btfees").html("$"+(0).toFixed(2));
			$("#json_chargeUser").html("$"+(2).toFixed(2));

			$("input[name='pay_dmv_fees']").val((1).toFixed(2));
			$("input[name='pay_uni_fees']").val((1).toFixed(2));
			$("input[name='pay_bt_fees']").val((0).toFixed(2));
			$("input[name='pay_total_fees']").val((2).toFixed(2));

			$(".step1").hide();$(".step2").show();
			$("#theForm").addClass("theFormStep2");
		}
		function alertUserCOA(){
			//CAN HAPPEN EITHER WHEN CHECKING Change of Address OR AFTER SUBMIT FIRsT STEP, IF COA IS CHECKED
			if($("#address").is(":checked")){
				alert("You indicated wanting to submit a Change of Address. This will not occur during the transaction; QuickAutoTags will send you a form. Note that the delivery address is not necessarily the same as the address on your registration.");
			}
		}
		function initBT(){
			$.ajax("../index.php/"+tokenUrl,{
				method:"GET",
				dataType:"json",
				success:function(data){
					//alert(data.result);
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