<!DOCTYPE html>
<html>
	<head>
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
				/*width: 100%;*/
			    /*float: left;*/
			    margin-bottom: 15px;
			    margin-top: 15px;
			    font-family:sans-serif;
			    color:#010507;font-size:15px;font-weight:bold;padding:15px;
			}
			.a2{margin:0!important;padding:10px!important;text-align:center;}
			#theForm{
				border-radius: 5px;
			    background: rgba(255,138,5,0.75);
			    padding-top: 10px;
			    padding-bottom: 10px;
			    min-height:100%; height:1000px;
			    /*background-image: url('oj_arrow2.png');
			    background-repeat: no-repeat;
			    background-position: top center;
			    background-size: 48px 16px;*/
			}
			#address{width:24px;height:24px;}
			p#coa{line-height:24px;font-size:18px;}
			#emdail{margin-top:5px;}
			#logo{width:300px;height:95px;margin:0 auto;background:url('qatlogo.png');background-size:300px 95px;margin-top:0px;}/*1234x648*/
			/*logo-upraisew.png 300x192 no MTOP, bpos center only*/
		</style>
		<script src="https://code.jquery.com/jquery-1.12.1.min.js"></script>
		<script>
			function getParameterByName(name) {
				var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
				return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
			}
		</script>
		<!--SEO-->
		<!-- Global site tag (gtag.js) - Google Ads: 944326063 -->
		<script async src="https://www.googletagmanager.com/gtag/js?id=AW-944326063"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());
		  gtag('config', 'AW-944326063');
		</script>
		<!-- Event snippet for Step One Sign up conversion page
		In your html page, add the snippet and call gtag_report_conversion when someone clicks on the chosen link or button. -->
		<script>
		function gtag_report_conversion(url) {
		  var callback = function () {
		    if (typeof(url) != 'undefined') {
		      window.location = url;
		    }
		  };
		  gtag('event', 'conversion', {
		      'send_to': 'AW-944326063/VZB2CN6EhJEBEK-LpcID',
		      'event_callback': callback
		  });
		  return false;
		}
		</script>
		<!--end SEO-->
	</head>
	<body>
		<div id="wrapper" style="width:100%;margin:0 auto;background:rgb(255,138,5);color:#fff;">
			<!--#585B5D-->
			<div id="logo"></div>
			<!--439x281 orig logo size-->
			<span id="step_one">
				<h1 style="font-family:sans-serif;font-weight:normal;text-align:center;margin-bottom:2px;">Thank You!</h1>
				<p style="margin-top:2px;font-weight:bold;font-size:18px;text-align:center;padding: 15px;">The DMV has processed your order successfully! A copy of your receipt has been emailed to you. QuickAutoTags now needs you to confirm your mailing address to send you your order!</p>
				<div id="form1">
					<div id="theForm1" style="width:100%;text-align:center;">
						<div class="successMessage"></div>
						<div class="errorMessage"></div>
						<div class="step1">
							<p class="a a2"><input type="text" id="to" placeholder="To:" /></p>
							<p class="a a2"><input type="text" id="a1" placeholder="Address 1" /></p>
							<p class="a a2"><input type="text" id="a2" placeholder="Address 2" /></p>
							<p class="a a2"><input type="text" id="c" placeholder="City" /></p>
							<p class="a a2"><input type="text" id="s" placeholder="State" /></p>
							<p class="a a2"><input type="text" id="z" placeholder="Zip" /></p>
							<input type="button" onclick="submitAddress()" value="Submit Mailing Address" style="width:300px;height:58px;color:#010507;font-size:18px;border-radius:8px;background:#FEE761;"><br/>
							<!--to,a1,a2,c,s,z-->
						</div>
					</div>
					<!--4-field w/ CoA checkbox and dropdown-->
				</div>
			</span>
			<span id="step_two" style="display:none;">
				<h1 style="font-family:sans-serif;font-weight:normal;text-align:center;margin-bottom:2px;">Thank You!</h1>
				<p style="margin-top:2px;font-weight:bold;font-size:16px;text-align:center;padding: 15px;">QuickAutoTags will use the delivery address provided. Thank you for using QuickAutoTags!</p>
				<div id="form">
					<div id="theForm" style="width:100%;text-align:center;">
						<div class="successMessage"></div>
						<div class="errorMessage"></div>
						<div class="step1">
							<input type="button" onclick="navCSW()" value="Check Us Out!" style="width:300px;height:58px;color:#010507;font-size:20px;border-radius:8px;background:#FEE761;"><br/>
							<!--#87C508-->
						</div>
					</div>
					<!--4-field w/ CoA checkbox and dropdown-->
				</div>
			</span>
		</div>
		<script>
			function navCSW(){window.location.href = "http://quickautotags.com";}
			function val(which){return $("#"+which).val();}
			function submitAddress(){
				var data = new Object();
				//to,a1,a2,c,s,z
				data.to = val("to"); data.a1 = val("a1");
				data.a2 = val("a2"); data.c = val("c");
				data.s = val("s"); data.z = val("z");
				data.sid = getParameterByName("sid")==null?"":getParameterByName("sid");
				console.log("We will then send "+JSON.stringify(data)+" to the backend, generate a second email linked to the order for UNI/QAT (so each lead gets a lead email + address submit email, but with a code so you know they are the same...and they should be close to each other anyway)");
				$.ajax("../index.php/sendAddressEmail",{
					method:"POST",
					dataType:"json",
					data:data,
					success:function(data){
						alert("Thanks for submitting your address!");
						$("#step_one").hide();$("#step_two").show();
						//TODO: also scroll to top ("nice to have")
					}
				});
			}
			gtag_report_conversion();
		</script>
	</body>
</html>