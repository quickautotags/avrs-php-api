<?php
namespace App\Classes\Examples;

class viewTestRecords extends AbstractExample {

    public function run() {
        $this->api->setURL('/api/v1/test-records/');
        $this->send();
        $this->logApi();
    }
}
