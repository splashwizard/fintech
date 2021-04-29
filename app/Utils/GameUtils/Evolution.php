<?php
namespace App\Utils\GameUtils;

use App\ConnectedKiosk;
use App\ConnectedKioskContact;
use stdClass;

class Evolution
{
    protected $host;
    protected $Casino_key;
    protected $token;
    public function __construct(){
        $this->host = "http://staging.evolution.asia-live.com";
        $this->Casino_key = "bkakn7qlvjzbe8wh";
        $this->token = "c706ec9bb6d4719c4a481ecd2ebc8a98";
    }

    private function getToken($str)
    {
        return md5($this->agentName.$this->API_key.$str);
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    // Function to get the client IP address
    public function get_client_ip() {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function GetPlayGameUrl($connected_kiosk_id, $user_id, $username)
    {
        try {
            if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $user_id)->count() === 0) {
                ConnectedKioskContact::create([
                    'connected_kiosk_id' => $connected_kiosk_id,
                    'contact_id' => $user_id
                ]);
            }
            $fields = [
                "uuid" => uniqid(),
                "player" => [
                    "id" => $username,
                    "update" => true,
                    "firstName" => $username,
                    "lastName" => "jdj",
                    "nickname" => "nickname",
                    "country" => "MY",
                    "language" => "my",
                    "currency" => "MYR",
                    "session" => [
                        "id" => $username.time().$this->get_client_ip(),
                        "ip" => $this->get_client_ip()
                    ]
                ]
            ];
            $url = "{$this->host}/ua/v1/{$this->Casino_key}/{$this->token}";
            $postData = json_encode($fields);
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
            $response = new stdClass();
            if(array_key_exists('entry', $result)){

                $response->Success = true;
                $response->ForwardUrl = $result['entry'];
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error on getting entry for Evolution";

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
            if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->count() === 0) {
                ConnectedKioskContact::create([
                    'connected_kiosk_id' => $connected_kiosk_id,
                    'contact_id' => $contact_id
                ]);
            }
            $fields = [
                "cCode" => "ECR",
                "ecID" => $this->Casino_key,
                "euID" => $username,
                "eTransID" => $referenceID,
                "amount" => $amount,
                "output" => 0
            ];
            $curl = curl_init("{$this->host}/api/ecashier?".http_build_query($fields));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json', "Token: {$this->token}")
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            if (!array_key_exists("error", $result)){
                $response = new stdClass();
                $response->Success = true;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $result["error"]["errormsg"];
            }
            return $response;
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
            if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->count() === 0) {
                ConnectedKioskContact::create([
                    'connected_kiosk_id' => $connected_kiosk_id,
                    'contact_id' => $contact_id
                ]);
            }
            $fields = [
                "cCode" => "EDB",
                "ecID" => $this->Casino_key,
                "euID" => $username,
                "eTransID" => $referenceID,
                "amount" => $amount,
                "output" => 0
            ];
            $curl = curl_init("{$this->host}/api/ecashier?".http_build_query($fields));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json', "Token: {$this->token}")
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            if ($result["transfer"]["result"] === "Y"){
                $response = new stdClass();
                $response->Success = true;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $result["transfer"]["errormsg"];
            }
            return $response;
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
            $fields = [
                "cCode" => "RWA",
                "ecID" => $this->Casino_key,
                "euID" => $username,
                "output" => 0
            ];
            $curl = curl_init("{$this->host}/api/ecashier?".http_build_query($fields));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json', "Token: {$this->token}")
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            if (!array_key_exists("error", $result)){
                $response = new stdClass();
                $response->Success = true;
                $response->balance = $result["userbalance"]["tbalance"];
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $result["error"]["errormsg"];
            }
            return $response;

        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
        }
        return json_encode($response);
    }
}
