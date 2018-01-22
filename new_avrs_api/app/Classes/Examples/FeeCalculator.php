<?php
namespace App\Classes\Examples;

class FeeCalculator extends AbstractExample {

    public function run() {
        /*
        $instance->method = 'POST';
        $instance->url = '/api/v1/authentications/';
        $instance->payload = [{
            "mulid": "pillai.sreenath@gmail.com",
            "password": "Packers!!12"
        }];
        */
        $this->api->setURL('/api/v1/authentications');
        $this->api->setMethod('POST');
        $this->api->addPayload("mulid","pillai.sreenath@gmail.com");
        $this->api->addPayload("password","Packers!!12");
        $this->api->send();
            $response = $this->api->getResult();
            //die(var_dump($response));
        $this->logApi();
        $this->resetApi();
        // create a deal with the required fields and immediately request fees
        $this->api->setURL('/api/v1/deals/');
        $this->api->setMethod('POST');
        $this->api->addPayload('owners', [['zip' => 95492,]]);
        $this->api->addPayload('vehicles', [['cost' => 45000,
            'first-operated-date' => '2017-11-11',
            'first-sold-date' => '2017-11-11',
            'vin' => '3B7HC13YXYG105749',
            // following values can be retrieved from the VIN API
            'fuel-type' => 'F',
            'make' => 'GMC',
            'model-body' => 'SD',
            'model-year' => 2015,
            'type-license-code' => 11,]]);
        $this->api->addPayload('status', 'QF');
        $this->api->addPayload('transaction-type', 3);
        $this->api->addPayload('gateway-type', 'CALC-CA');
        $this->send();
        $this->logApi();
    }
}

