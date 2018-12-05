<?php

namespace App\Http\Controllers;

use App\Classes\Src\TestRecords;
use App\Classes\Src\AVRSAPI;
use App\Classes\Src\AvrsApiSO;
use App\Classes\Src\Logger;
use App\Classes\Examples\FeeCalculator;
use App\Classes\Examples\renewRegistration;
use App\Classes\Examples\viewTestRecords;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use \Braintree_Configuration;
use \Braintree_ClientToken;
use \Braintree_Transaction;
use \DB;

class Controller extends BaseController
{
	//BEGIN CONTROLLER
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
 	public function testFeeCalculator(){
 		$return = '';
 		$example = new FeeCalculator();
 		$return = $example->run();
 		die(json_encode($return));
 	}   
 	public function exampleRenewRegistrationFull(){
 		$return = '';
 		$example = new renewRegistration();
 		$return = $example->run();
 		die(json_encode($return));
 	}
 	public function checkError(){
 		$return = '';
 		$example = new renewRegistration();
 		$return = $example->checkError($_REQUEST['dealid']);
 		//status_json includes that error was detected (data.error==true || data.deal_status=="E") and checked for, and $return to show what error is
 		//check if smog on server side so we can set taht clear rdf should be happening next, and user can pay
 		$sid = $_REQUEST['sid'];
 		$json = DB::select("select status_json from user_submission where id=".$sid);
 		$json = json_decode($json[0]->status_json);
 		$newStep = new \stdClass(); $newStep->action="error_checked";
 		$newStep->result=$return;
 		$newStep->verboseResult="eCode or error:true checked for errorcode and errortext";
 		$json->steps[]=$newStep; $set = "status_json='".json_encode($json)."'";
 		$update = DB::update("update user_submission SET ".$set." WHERE id=".$sid);
 		$return['sid']=$_REQUEST['sid'];
 		die(json_encode($return));
 	}
 	public function clearRDF(){
 		$return = ''; $example = new renewRegistration();
 		//type doesnt matter, clearing RDF only applies to type 1
 		$return = $example->clearRDFForDeal($_REQUEST['vin'],$_REQUEST['plate']);
 		//status_json includes that CLEAR RDF was sent (after smog error detected) and $return to see if new dealid was created or not (have verbose status anyway like exampleRRFirst), also save $_REQUEST in submission_json as 'followup submission' object
 		$sid = $_REQUEST['sid'];
 		$json = DB::select("select status_json from user_submission where id=".$sid);
 		$json = json_decode($json[0]->status_json);
 		$newStep = new \stdClass(); $newStep->action="deal";
 		$newStep->result=$return;
 		$newStep->verboseResult=$_GET['type']." req with resulting dealid ".$return['dealid'];
 		$json->steps[]=$newStep; $set = "status_json='".json_encode($json)."'";
 		$update = DB::update("update user_submission SET ".$set." WHERE id=".$sid);
 		$return['sid'] = $_REQUEST['sid'];
 		die(json_encode($return));
 	}
 	public function exampleRenewRegistrationFirst(){
 		$return = '';
 		$example = new renewRegistration();
 		//initial entry into user_submission with email/plate/vin/zip, and submission_json (all fields inc. above + coa + type + verbose_type)
 		$initial = DB::insert("insert into user_submission(email,zip,plate,vin,submission_json) values('".$_REQUEST['email']."','".$_REQUEST['zip']."','".$_REQUEST['plate']."','".$_REQUEST['vin']."','".json_encode($_REQUEST)."')");
 		$sid = DB::getPdo()->lastInsertId();
 		switch(intval($_GET['type'])) {
 			case 1: //Renewal
	 			$return = $example->runFirstStep($_REQUEST['vin'],$_REQUEST['plate']); break;
 			case 2: //Replacement Sticker -> Substitute Sticker MAYBE NEED quotes around #
	 			$return = $example->runFirstStepWithAttribute($_REQUEST['vin'],$_REQUEST['plate'],8); break;
 			case 3: //Replacement/Duplicate Card -> Duplicate Registration Card
	 			$return = $example->runFirstStepWithAttribute($_REQUEST['vin'],$_REQUEST['plate'],2); break;
	 		case 4: //clear rdf
	 			$return = $example->clearRDFForDeal($_REQUEST['vin'],$_REQUEST['plate']); break;
	 		default: 
	 			die("no type defined!"); break;
 		}
 		//status_json includes $return and check for "dealid created successfully" or "dealid failed to create" 
 		$json = new \stdClass(); $json->steps=[];
 		$newStep = new \stdClass(); $newStep->action="deal";
 		$newStep->result=$return;
 		$newStep->verboseResult=$_GET['type']." req with resulting dealid ".$return['dealid'];
 		$json->steps[]=$newStep; $set = "status_json='".json_encode($json)."'";
 		$update = DB::update("update user_submission SET ".$set." WHERE id=".$sid);
 		$return["sid"]=$sid;
 		die(json_encode($return));
 	}
 	public function exampleRenewRegistrationRest(){
 		$return = '';
 		$example = new renewRegistration();
 		//add reference param ('out param') to save into status_json
 		$return = $example->runTransactionStep($_REQUEST['dealid'],$_REQUEST['dealstatus']);
 		$sid = $_REQUEST['sid'];
 		//update status_json with $return, dealstatus_desired, dealstatus_set, errors
 		$json = DB::select("select status_json from user_submission where id=".$sid);
 		$json = json_decode($json[0]->status_json);
 		$newStep = new \stdClass(); $newStep->action="deal_transaction";
 		$newStep->result=$return;
 		$errorFound = ((isset($return['error'])&&$return['error']==true) || $return['deal_status']=="E")? "yes" : "no";
 		$newStep->verboseResult="errorFound/chkError next?--".$errorFound."|status_desired--".$_REQUEST['dealstatus'];
 		$json->steps[]=$newStep; $set = "status_json='".json_encode($json)."'";
 		$update = DB::update("update user_submission SET ".$set." WHERE id=".$sid);
 		$return['sid'] = $_REQUEST['sid'];
 		die(json_encode($return));
 	}
 	public function viewTestRecords(){
 		$return = '';
 		$example = new viewTestRecords();
 		$return = $example->run();
 		die(json_encode($return));
 	}
 	public function sendEmailReceipt($to,$type){
 		Mail::send('emails.receipt',['type'=>$type],function($message){
 			$message->from('uni@quickautotags.com', 'UNI MATA');
	 		$message->to($to)->cc('pillai.sreenath@gmail.com');
	 		$message->attach(public_path()."/testImages/1.png");
	 		/*
			$message->sender($address, $name = null);
			$message->cc($address, $name = null);
			$message->bcc($address, $name = null);
			$message->replyTo($address, $name = null);
			$message->subject($subject);
			$message->priority($level);
	 		*/
	 		//TODO: generate PDF (either our own since we are upcharging DMV, or official DMV/AVRS PDF)
	 		//$message->attach($pathToFile);
	 		/*
			When attaching files to a message, you may also specify the display name and / or MIME type by passing an array as the second argument to the attach method:
			$message->attach($pathToFile, ['as' => $display, 'mime' => $mime]);
	 		*/
 		});	
 	}

 	public function fetchClientTokenQAT(){
		/*production*/
		Braintree_Configuration::environment('production');
		Braintree_Configuration::merchantId('8k76bhrq64kjwrbk');
		Braintree_Configuration::publicKey('b9hzschq7gcd8jg7');
		Braintree_Configuration::privateKey('87e386879e816502fe4d3b74c35111e2');
		/*end production*/
		$toReturn = array();
		$token = ($clientToken = Braintree_ClientToken::generate(array(
			//"customerId" => $aCustomerId
		)));
		$toReturn["result"] = $token;
		return json_encode($toReturn);
	}

	public function sendAddressEmail(){
		/*data.to = val("to"); data.a1 = val("a1");
				data.a2 = val("a2"); data.c = val("c");
				data.s = val("s"); data.z = val("z");*/
		$curl = curl_init();
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'http://54.149.200.91/uni/receiptaddr.php',
		    CURLOPT_USERAGENT => 'quickautotags_application',
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS => $_POST
		));
		$resp = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
		return json_encode(array("success"=>"true","emailRes"=>$resp));
	}
	
	public function payQAT(){
		//1. Execute Payment
		Braintree_Configuration::environment('production');
		Braintree_Configuration::merchantId('8k76bhrq64kjwrbk');
		Braintree_Configuration::publicKey('b9hzschq7gcd8jg7');
		Braintree_Configuration::privateKey('87e386879e816502fe4d3b74c35111e2');
		$result = Braintree_Transaction::sale([
		  'amount' => $_POST["amount"],
		  'paymentMethodNonce' => $_POST["payment_method_nonce"],
		  'options' => [
			'submitForSettlement' => True
		  ]
		]);
		//2. Execute Email
		// Get cURL resource
		$curl = curl_init();
		// Set some options - we are passing in a useragent too here
		curl_setopt_array($curl, array(
		    CURLOPT_RETURNTRANSFER => 1,
		    CURLOPT_URL => 'http://54.149.200.91/uni/receipt.php?u=user',
		    CURLOPT_USERAGENT => 'quickautotags_application',
		    CURLOPT_POST => 1,
		    CURLOPT_POSTFIELDS => $_POST
		));
		/*use the following if entire $_POST doesn't work well:
		    array(
		        item1 => 'value',
		        item2 => 'value2'
		    )*/
		// Send the request & save response to $resp
		$resp = curl_exec($curl);
		// Close request to clear up some resources
		curl_close($curl);
		//2b. Email2
			if(isset($_POST['uni_sid'])&&$_POST['uni_sid']!=""){
				$json = DB::select("select status_json from user_submission where id=".$_POST['uni_sid']);
		 		$_POST['raw_status_json']=$json[0]->status_json;
	 		}
			$curl2 = curl_init();
			// Set some options - we are passing in a useragent too here
			curl_setopt_array($curl2, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL => 'http://54.149.200.91/uni/receipt.php?u=uni',
			    CURLOPT_USERAGENT => 'quickautotags_application',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => $_POST
			));
			/*use the following if entire $_POST doesn't work well:
			    array(
			        item1 => 'value',
			        item2 => 'value2'
			    )*/
			// Send the request & save response to $resp
			$resp2 = curl_exec($curl2);
			// Close request to clear up some resources
			curl_close($curl2);
		//3. Return
		return json_encode(array("pay"=>$result,"email"=>$resp,"email2"=>$resp2));
	}
//END CONTROLLER
}
