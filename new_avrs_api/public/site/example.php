<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>body{font-family:Arial, sans-serif;}.a{width:200px;float:left;}</style>
	<script src="https://code.jquery.com/jquery-1.12.1.min.js"></script>
	<script>
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
		var data = new Object();//$_REQUEST['dealid'],$_REQUEST['dealstatus']
		data.dealid = _dealid; data.dealstatus="C";
		$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+_dealid+"&dealstatus=C",{
			method:"GET",
			dataType:"json",
			success:function(data){
				console.log(data);
				//make sure all Fees look good, status=FR, has a deal-id and no errors
				//show BT form with amount
				alert(data.paidProperly);//hopefully true/1
				//send them email here? or when processing payment, or both
				alert("DMV processed your order successfully! Thank you for using QuickAutoTags!");
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
			var data = new Object();
			alert(<?=$_REQUEST['amount']?>);//1 when testing, chargeUser otherwise
			data.amount = <?=$_REQUEST['amount']?>;
			alert(<?=$_REQUEST['avrs_dealid']?>);
			_dealid = <?=$_REQUEST['avrs_dealid']?>;
			data.payment_method_nonce = "<?=$_REQUEST['payment_method_nonce']?>";
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
	<div style="width:200px">
		<div class="step1">
			<p class="a">Email:<br/><input type="text" id="email" /></p>
			<p class="a">Plate:<br/><input type="text" id="plate" /></p>
			<p class="a">VIN (last 3 digits):<br/><input type="text" id="vin" /></p>
			<input type="button" value="Submit" onclick="step1()" style="width:200px;height:40px;border-radius:8px;background:#f69222;">
		</div>
		<form class="step2" id="checkout" method="post" action="example.php" style="display:none;">
		  <table>
		  	<tr><td colspan=2><b>Summary of Fees</b></td></tr>
		  	<tr><td>DMV Fees:</td><td id="json_total"></td></tr>
		  	<tr><td>Service Fee:</td><td id="json_unifees"></td></tr>
		  	<tr><td>Total:</td><td id="json_chargeUser"></td></tr>
		  	<tr><td colspan=2><b>Enter Payment Info</b></td></tr>
		  </table><br/>
		  <div id="payment-form"></div>
		  <input type="hidden" name="amount" />
		  <input type="hidden" name="da_org" />
		  <input type="hidden" name="avrs_dealid" />
		  <script>
			$("input[name='da_org']").val(getParameterByName("org"));
		  </script>
		  <input type="submit" value="Submit" style="width:200px;height:40px;border-radius:8px;background:#f69222;">
		</form>

		<script src="https://js.braintreegateway.com/js/braintree-2.20.0.min.js"></script>
		<script>
		function step1(){
			var data = new Object(); //!(" ".trim()) is true
			var rr=["vin","plate","email"];
			for(var ii=0;ii<rr.length;ii++){if(!$("#"+rr[ii]).val().trim()){alert("VIN, Plate, and Email are all required!"); return;}}
			data.vin = $("#vin").val(); data.plate=$("#plate").val();
			$.ajax("../index.php/exampleRenewRegistrationFirst?vin="+data.vin+"&plate="+data.plate,{
				method:"GET",
				dataType:"json",
				success:function(data){
					//make sure Transaction is ready, status=R, has a deal-id and no errors
					alert(data.dealid);
					step1point5(data.dealid);
				}
			});
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
						alert("E case");
						$.ajax("../index.php/checkError?dealid="+data.deal_id,{
							method:"GET", dataType:"json",
							success:function(data){
								//Example Return:
								//{"errorcode":"CADMV\/D365","errortext":" - SMOG CERT REQUIRED"}
								var badCodes = ["CADMV/Q201","CADMV/D365","CADMV/Q046","CADMV/Q035"];
								//0 - Q201 - REG SUSP - CALL 1-800-777-0133. REFER CUST TO DMV TO POST FEES.
								//1 - D365 - SMOG CERT REQUIRED FROM STAR STATION
								//2 - Q046 - CLEARING INQUIRY REQUIRED (CLEAR RDF?)
								var userMessages = [
									"Your registration is currently suspended due to insurance-related issues. QuickAutoTags will contact you at "+$("#email").val()+" with instructions to renew your registration.",
									"Your registration renewal requires a Smog certification from the provider listed below. QuickAutoTags will follow up with you at "+$("#email").val()+" to get any documentation needed.",
									"You have already renewed or started a renewal for this registration (either at the DMV or elsewhere), and will have to complete it there. QuickAutoTags cannot process another renewal for your Plate+VIN.",
									"Invalid Plate or VIN. Please enter your License Plate and last 3 digits of your VIN correctly and double-check to make sure you don't have any typos."
								];
								var knownError = false;
								for(var jj=0;jj<badCodes.length;jj++){
									if(data.errorcode==badCodes[jj]){
										knownError = true; var base_msg = userMessages[jj];
										alert(base_msg+"\nDMV Error Code: "+data.errorcode+data.errortext);
									}
								}
								if(!knownError){
									var dat_msg = (data.errorcode.indexOf("CADMV")!==-1)? "Your transaction cannot be processed at the DMV at this time. This may be an address issue, an insurance issue, or a recall issue. QuickAutoTags will contact you to let you know any actions you may have to take (at the DMV or otherwise)." : "AVRS Error" ;
									alert(dat_msg+"\nDMV Error Code: "+data.errorcode+data.errortext);
								}
								//email QAT/Uni with /checkError and /deals result for given deal-id so they can check what the error is and handle appropriately + get back to customer.
								//$("#email").val() used here
								alert("end of /checkError async call"); alert($("input[name='amount']").val());
							}
						});
						return;
					} else {alert("Not E case");}
					
					//show BT form with amount
					$("input[name='amount']").val(data.chargeUser);//use 1 when testing
					//initBT();if have to init after amount
					$("#json_total").html(data.total); $("#json_unifees").html(data.unifees);
					$("#json_chargeUser").html(data.chargeUser);
					$(".step1").hide();$(".step2").show();
				}
			});
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