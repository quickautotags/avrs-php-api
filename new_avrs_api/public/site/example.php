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
	var org = "QAT";
	switch(org){
		case "QAT":
			var donateUrl = "payQAT";
			var tokenUrl = 'getTokenQAT';
			break;
		default: break;
	}
	function lastStep(){
		var data = new Object();//$_REQUEST['dealid'],$_REQUEST['dealstatus']
		data.dealid = dealid; data.dealstatus="C";
		$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+dealid+"&dealstatus=C",{
			method:"GET",
			dataType:"json",
			data:data,
			success:function(data){
				console.log(data);
				//make sure all Fees look good, status=FR, has a deal-id and no errors
				//show BT form with amount
				alert(data.paidProperly);//hopefully true/1
				alert("DMV processed your order successfully! Thank you for using QAT!");
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
			<p class="a">Plate:<br/><input type="text" id="plate" /></p>
			<p class="a">VIN (last 3 digits):<br/><input type="text" id="vin" /></p>
			<input type="button" value="Submit" onclick="step1()" style="width:200px;height:40px;border-radius:8px;background:#f69222;">
		</div>
		<form class="step2" id="checkout" method="post" action="example.php" style="display:none;">
		  <div id="payment-form"></div>
		  <input type="hidden" name="amount" />
		  <input type="hidden" name="da_org" />
		  <script>
			$("input[name='da_org']").val(getParameterByName("org"));
		  </script>
		  <input type="submit" value="Submit" style="width:200px;height:40px;border-radius:8px;background:#f69222;">
		</form>

		<script src="https://js.braintreegateway.com/js/braintree-2.20.0.min.js"></script>
		<script>
		function step1(){
			var data = new Object();
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
			$.ajax("../index.php/exampleRenewRegistrationRest?dealid="+dealid+"&dealstatus=FR",{
				method:"GET",
				dataType:"json",
				success:function(data){
					console.log(data);
					//make sure all Fees look good, status=FR, has a deal-id and no errors
					//show BT form with amount
					alert(data.chargeUser);
					$("input[name='amount']").val(1);//use data.chargeUser after testing
					//initBT();if have to init after amount
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