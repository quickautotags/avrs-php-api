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
 	public function exampleRenewRegistrationFirst(){
 		$return = '';
 		$example = new renewRegistration();
 		$return = $example->runFirstStep();
 		die(json_encode($return));
 	}
 	public function exampleRenewRegistrationRest(){
 		$return = '';
 		$example = new renewRegistration();
 		$return = $example->runTransactionStep($_REQUEST['deal-id'],$_REQUEST['deal-status']);
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
//END CONTROLLER
}
