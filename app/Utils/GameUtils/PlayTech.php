<?php
namespace App\Utils\GameUtils;

use App\ConnectedKiosk;
use App\ConnectedKioskContact;
use App\Contact;
use stdClass;

class PlayTech
{
    protected $Api_host;
    protected $secretKey;
    protected $secureLogin;
    protected $agent;
    protected $language;
    public function __construct(){
        $this->Api_host = "https://staging.slotcasino.ml/IntegrationService/v3/http/CasinoGameAPI/";
        $this->secretKey = "vbRhFhAj65";
        $this->secureLogin = "judijom";
        $this->agent = "fjz7qa";
        $this->language = 'en';
    }

    private function getResponse($subUrl, $params)
    {
        $tmp_params = $params;
        ksort($tmp_params);
        $hasharr = [];
        foreach ($tmp_params as $key => $value){
            $hasharr[] = $key.'='.$value;
        }
        $hashstring = join('&', $hasharr).$this->secretKey;
        $params['hash'] = md5($hashstring);
        $url = $this->Api_host.$subUrl;
        $postData = json_encode($params);
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));

        $data = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($data, true);
        return $result;
    }

    private function createUserIfNotExist($connected_kiosk_id, $user_id, $username)
    {
        $response = new stdClass();
        if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $user_id)->count() === 0){ // Player doesn't exist
            $params = ['secureLogin' => $this->secureLogin, 'externalPlayerId' => $username, 'currency' => 'MYR'];
            $result = $this->getResponse("player/account/create", $params);
            if ($result["error"] == "0"){
                ConnectedKioskContact::create([
                    'connected_kiosk_id' => $connected_kiosk_id,
                    'contact_id' => $user_id
                ]);
            } else {
                $response->Success = false;
                $response->Message = "Error on Registering PlayTech";

                return $response;
            }
        }
        $response->Success = true;
        return $response;
    }

    public function GetPlayGameUrl($connected_kiosk_id, $user_id, $username)
    {
        try {
            $response = $this->createUserIfNotExist($connected_kiosk_id, $user_id, $username);
            if($response->Success === false) return $response;

            $params = ['secureLogin' => $this->secureLogin, 'externalPlayerId' => $username, 'gameId' => 'sw_ch8', 'language' => $this->language];
            $result = $this->getResponse("game/start", $params);
            $response = new stdClass();
            if ($result["error"] == "0"){
                $response->Success = true;
                $response->ForwardUrl = $result["gameURL"];
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error on getting ForwardURL of PlayTech";

                return $response;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    public function deposit($connected_kiosk_id, $contact_id, $username, $referenceID, $amount)
    {
        try {
            $response = $this->createUserIfNotExist($connected_kiosk_id, $contact_id, $username);
            if($response->Success === false) return $response;

            $params = ['secureLogin' => $this->secureLogin, 'externalPlayerId' => $username, 'externalTransactionId' => $referenceID, 'amount' => $amount];
            $result = $this->getResponse("balance/transfer", $params);
            $response = new stdClass();
            if ($result["error"] == "0"){
                $response->Success = true;
                $response->balance = $result["balance"];
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error on depositing PlayTech";

                return $response;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }


    public function withdraw($connected_kiosk_id, $contact_id, $username, $referenceID, $amount)
    {
        try {
            $response = $this->createUserIfNotExist($connected_kiosk_id, $contact_id, $username);
            if($response->Success === false) return $response;

            $params = ['secureLogin' => $this->secureLogin, 'externalPlayerId' => $username, 'externalTransactionId' => $referenceID, 'amount' => -$amount];
            $result = $this->getResponse("balance/transfer", $params);
            $response = new stdClass();
            if ($result["error"] == "0"){
                $response->Success = true;
                $response->balance = $result["balance"];
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error on withdrawing PlayTech";

                return $response;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    public function getBalance($username)
    {
        try {
            $params = ['secureLogin' => $this->secureLogin, 'externalPlayerId' => $username];
            $result = $this->getResponse("balance/current", $params);
            $response = new stdClass();
            if ($result["error"] == "0"){
                $response->Success = true;
                $response->balance = $result["balance"];
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error on getting Balance of PlayTech";

                return $response;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }
}
