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
 	
}
