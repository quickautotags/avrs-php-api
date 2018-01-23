<?php
namespace App\Classes\Examples;

use App\Classes\Src\TestRecords;
use App\Classes\Src\AVRSAPI;

class renewRegistration extends AbstractExample {

    /*
    TEST RECORD
        test-record-id: 4
        plate: 2BPA369
        vin: 369
        expiration: 2017-11-18
        expiration-time: 2018-01-22 23:59:59
        owner: WILLIAMS JOHN
        lien-holder: RENTS CHEAP
        l-lid: 1077
        conditions: 134217985
    */
    public function run() {

        $bitmask = (TestRecords::BIT_AUTO | TestRecords::BIT_RENEWAL_DUE);
        $reservation = $this->getTestRecord($bitmask);
        //die(json_encode($reservation));
        // create a deal with the required fields and save
        //$this->api = new AVRSAPI();
        $this->api->setURL('/api/v1.5/deals/');
        $this->api->setMethod('POST');
        $this->api->addPayload('vehicles', [[
           'vin'       => '369'   ,
           'plate'     => '2BPA369' ,
           'insurance' => 'Y'                   , // for testing environment only, certify that the vehicle is insured
       ]]);
        $this->api->addPayload('transaction-type', 6);
        $this->send();
        $response = json_decode($this->api->getResult(), true);
        $this->logApi();
        sleep(1);
        // create a deal transaction for our deal, providing the desired end state (FR, FP or C)
        $this->resetApi();
        $this->api->setURL('/api/v1.5/deals/transactions/');
        $this->api->setMethod('POST');
        $this->api->addPayload('deal-id', $response['deals'][0]['id']);
        $this->api->addPayload('status', 'FR'); // getting fees, which should be checked for sanity
        $this->send();
        $response = json_decode($this->api->getResult(), true);
        $this->logApi();

        if (empty($response['error'])) {
            sleep(1); // just to be sure that we don't overwrite the first request/response pair
            $this->resetApi();
            $this->api->addPayload('deal-id', $response['deals'][0]['id']);
            $this->api->addPayload('deal-status', 'C'); // accepting fees
            $this->send();
            $this->logApi();
        }
    }
}

