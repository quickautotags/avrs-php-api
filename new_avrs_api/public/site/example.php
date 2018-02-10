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
	var org = getParameterByName("org") || "<?=$_REQUEST['da_org']?>";
	switch(org){
		case "YWCA":
			var donateUrl = "donateYWCA";
			var tokenUrl = 'getTokenYWCA';
			var org_logo = 'http://www.ywcamadison.org/atf/cf/%7B2487BD0F-90C7-49BC-858D-CC50637ECE23%7D/logo_YWCA_int.png';
			break;
		case "YGB":
			var donateUrl = "donateYGB";
			var tokenUrl = 'getTokenYGB';
			var org_logo = 'logo-ygb.png';
			break;
		default: break;
	}
	</script>
	<?php
	//echo var_dump($_REQUEST['payment_method_nonce']);
	//echo var_dump($_REQUEST['amount']);
	if(isset($_REQUEST['payment_method_nonce'])){
		?>
			<script>
			var data = new Object();
			data.amount = <?=$_REQUEST['amount']?>;
			data.payment_method_nonce = "<?=$_REQUEST['payment_method_nonce']?>";
			console.log(data);
			$.ajax("../braintree_server/laravel/public/index.php/"+donateUrl,{
				method:"POST",
				dataType:"json",
				data:data,
				success:function(data){
					console.log(data);
					alert("Thank you for your donation!");
				}
			});
			</script>
		<?php
	}
	?>
</head>
<body>
	<div style="width:200px">
		<img id="org_logo" src="" class="a" />
		<script>
			$("#org_logo").attr("src",org_logo);
		</script>
		<p class="a">Name: (optional)<br/><input type="text" id="name" /></p>
		<p class="a">Email: <br/><input type="text" id="email" /></p>
		<form id="checkout" method="post" action="d.php">
		  <div id="payment-form"></div>
		  <input type="hidden" name="amount" />
		  <input type="hidden" name="da_org" />
		  <script>
			$("input[name='amount']").val(getParameterByName('a'));
			$("input[name='da_org']").val(getParameterByName("org"));
		  </script>
		  <input type="submit" value="Donate!" style="width:200px;height:40px;border-radius:8px;background:#f69222;">
		</form>
		<p>Powered By <img id="ttlogo" src="ttlogo.png" style="width:20px;" /></p>
		<script src="https://js.braintreegateway.com/js/braintree-2.20.0.min.js"></script>
		<script>
		$.ajax("../braintree_server/laravel/public/index.php/"+tokenUrl,{
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
		
		</script>
	</div>
</body>
</html>