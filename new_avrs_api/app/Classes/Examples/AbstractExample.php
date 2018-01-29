<?php
namespace App\Classes\Examples;

use \Exception;
use App\Classes\Src\AVRSAPI;
use App\Classes\Src\AvrsApiSO;
use App\Classes\Src\Logger;
use App\Classes\Src\TestRecords;

abstract class AbstractExample {

    /* @var AVRSAPI */
    protected $api;
    /* @var AvrsApiSO $settings */
    private $settings;

    const RETRY_ATTEMPTS = 3;
    const RETRY_BASE = 3;

    public function __construct() {
        $settings = json_decode(file_get_contents(__DIR__ . '/settings.json'), true);
        if (!is_array($settings)) {
            throw new Exception('Unable to load and parse settings.json');
        }
        $environment = $settings[ $settings['active'] ];
        if (!is_array($environment)) {
            throw new Exception('Unable to load and parse the active environment');
        }
        $this->settings = new AvrsApiSO();
        $this->settings->setHost($environment['host']);
        $this->settings->setPort($environment['port']);
        $this->settings->setKey($environment['key']);
        $this->settings->setSecret($environment['secret']);
        $this->settings->setPassphrase($environment['passphrase']);
        $this->settings->setVerifySSL($environment['verifySSL']);
        $this->settings->setDebug($environment['debug']);
        $this->resetApi();//$this->api->send();$this->resetApi();
    }

    protected function resetApi() {
        $this->api = AVRSAPI::Factory($this->settings);
    }

    abstract function run();

    /**
     * @return AVRSAPI
     */
    public function getApi(){
        return $this->api;
    }

    protected function send() {
        $retries = 0;
        $this->api->send();
        $response = json_decode($this->api->getResult(), true);
        /**/
        //die(json_encode($response));
        Logger::writeCustom('resp',json_encode($response));
        /**/
        while ($retries++ < static::RETRY_ATTEMPTS && isset($response['deals']) && $response['deals'][0]['error-code'] == 'CADMV/Q023') {
            error_log('DMV Retry Code Encountered');
            sleep(static::RETRY_BASE * pow(2, $retries));
            $this->api->send();
            $response = json_decode($this->api->getResult(), true);
        }
    }

    protected function getTestRecord($attributes) {
        $haveReservation = false;
        $reservations = array_reverse(TestRecords::getRecords($this->settings));
        foreach ($reservations as $reservation) {
            if (($reservation['conditions'] & $attributes) == $attributes) {
                $haveReservation = true;
                break;
            }
        }
        if (!$haveReservation) {
            $reservation = TestRecords::reserveRecord($this->settings, $attributes);
        }

        return $reservation;
    }

    protected function logApi() {
        Logger::writeRequestResponse($this->api);
    }
}