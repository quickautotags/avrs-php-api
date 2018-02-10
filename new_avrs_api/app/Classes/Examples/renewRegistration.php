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
        //FIRST STEP
        $bitmask = (TestRecords::BIT_AUTO | TestRecords::BIT_RENEWAL_DUE);
        $reservation = $this->getTestRecord($bitmask);
        //die(json_encode($reservation));
        // create a deal with the required fields and save
        //$this->api = new AVRSAPI();
        $this->api->setURL('/api/v1.5/deals/');
        $this->api->setMethod('POST');
        //Required fields: deals, gateway-type, transaction-type
        $this->api->addPayload('deals', [['vehicles'=>[[
           'vin'       => '369'   ,
           'plate'     => '2BPA369' ,
           'insurance' => 'Y' , // for testing environment only, certify that the vehicle is insured
       ]],'transaction-type'=>'6','gateway-type'=>'CA' ]]);
        //$this->api->addPayload('gateway-type', 'CA');
        //$this->api->addPayload('transaction-type', 6);
        $this->send();
        $response = json_decode($this->api->getResult(), true);
        $this->logApi();
        //END FIRST STEP
        sleep(1);
        //SECOND STEP
        // create a deal transaction for our deal, providing the desired end state (FR, FP or C)
        $this->resetApi();
        $this->api->setURL('/api/v1.5/deals/transactions/');
        $this->api->setMethod('POST');
        $this->api->addPayload('deal-id', $response['deals'][0]['id']);
        $this->api->addPayload('deal-status', 'FR'); // getting fees, which should be checked for sanity
        $this->send();
        $response = json_decode($this->api->getResult(), true);
        $this->logApi();
        //END SECOND STEP

        if (empty($response['error'])) {
            sleep(1); // just to be sure that we don't overwrite the first request/response pair
            //THIRD STEP
            $this->resetApi();
            $this->api->setURL('/api/v1.5/deals/transactions/');
            $this->api->setMethod('POST');
            $this->api->addPayload('deal-id', $response['deals'][0]['id']);
            $this->api->addPayload('deal-status', 'C'); // accepting fees
            $this->send();
            $this->logApi();
            //END THIRD STEP
        }
    }
    public function runFirstStep(){
        $bitmask = (TestRecords::BIT_AUTO | TestRecords::BIT_RENEWAL_DUE);
        $reservation = $this->getTestRecord($bitmask);
        $this->api->setURL('/api/v1.5/deals/');
        $this->api->setMethod('POST');
        //Required fields: deals, gateway-type, transaction-type
        $this->api->addPayload('deals', [['vehicles'=>[[
           'vin'       => '069'   ,
           'plate'     => '4ZPR864' ,
           'insurance' => 'Y' , // for testing environment only, certify that the vehicle is insured
        ]], 'transaction-type'=>'6','gateway-type'=>'CA' ]]);
        /*
        optional change of address
        'mail-address'=>[[
            'city'=>'Santa Cruz',
            'state'=>'CA',
            'street0'=>'1156 High St.',
            'street1'=>'Apt #1',
            'zip'=>'95064'
        ]], 
        */
        $this->send();
        $response = json_decode($this->api->getResult(), true);
        $this->logApi();
        return $response;
    }
    public function runTransactionStep($deal_id, $desired_status){
        $this->resetApi();
        $this->api->setURL('/api/v1.5/deals/transactions/');
        $this->api->setMethod('POST');
        $this->api->addPayload('deal-id', $deal_id); //$response['deals'][0]['id']
        /* STATUSES WE CARE ABOUT:
            R: // ready for processing, information to process renewal is valid
            FR: // getting fees, which should be checked for sanity, needs "permission" from server (!askAVRS!)
            FP: // fees posted, DMV just accepted fees, not that useful to end on since (see below)
            C: // complete, right after fees posted, might as well always go here instead of FP then C
        */
        $this->api->addPayload('deal-status', $desired_status);
        $this->send();
        $response = json_decode($this->api->getResult(), true);
        /*IF DOING FR TO CHECK FEES, ASK AVRS WHERE TO GET FEES FROM
            POTENTIAL CANDIDATES: (same object level as 'vehicles' within deals, aka deals.xxx)
            fee-dmv-amount
            fees.total
            CODE EXAMPLE - ensure its $9 like all renewals/replacement credentials should be, so UNI makes money
            //use intVal if needed, otherwise its a double but should still == 9
            $response['deal-transactions'][0]['fees']['adjusted'];//should be same as ['fees']['total']
            loop thru "by-type", confirm that PASSTHROUGH+AVRS=9.5, either way just charge PASSTHROUGH+AVRS+STATE+10.5 (and check+log if its 20 more than fees adjusted)
            $response['deal-transactions'][0]['fee-dmv-amount'] == 9
            IF NOT 9, LOG what it is and let user know there was a DMV error if its egregious otherwise charge user double of whatever amount it is instead of 20? (my suggestion) To do this, you'd return a "custom-amount"-like field in JSON and UI would check for that and update hidden input for amount.
        */
        /*IF DOING C TO POST FEES AND COMPLETE THE TRANSACTION, ASK AVRS HOW TO GENERATE OFFICIAL RECEIPTS
            OR POTENTIALLY GENERATE OUR OWN, SINCE WE ARE APPLYING A MARKUP
            THEN SEND EMAIL (copy Controller.sendEmailReceipt($to,$type) logic: $to=their email, $type=Registration Renewal or Replacement Credentials...or both) WITH THE RECEIPT/PDF ATTACHED
        */
        $this->logApi();
        return $response;
    }
}

