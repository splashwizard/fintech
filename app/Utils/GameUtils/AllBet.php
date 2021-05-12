<?php
namespace App\Utils\GameUtils;

use App\ConnectedKiosk;
use App\ConnectedKioskContact;
use App\Contact;
use App\Utils\GameUtils\TripleDES;
use stdClass;

//replace it with your 3Des key
define("ALLBET_DES_KEY", "9KsO/UU7te+YawvltrBz3AggqGsOlynp");
//replace it with your MD5 key
define("ALLBET_MD5_KEY", "E8DlMrlfwFnsyslPcgJ1bfVvww19oN+/ZO9ALd7EOzk=");
//replace it with your propertyId
define("ALLBET_PROPERTY_ID", "8866258");
//replace it with API URL
define("ALLBET_API_URL", "https://api3.apidemo.net:8443");
//replace it with API URL
define("ALLBET_AGENT", "fjz7qa");
define("ALLBET_LANGUAGE", "en");

class AllBet
{
    private function getPassword($contact_id)
    {
        $contact = Contact::find($contact_id);
        return !$contact['simple_password'] ? 'bgt54321' : encrypt_decrypt('decrypt', $contact['simple_password']);
    }

    private function createUserIfNotExist($connected_kiosk_id, $user_id, $username)
    {
        $password = $this->getPassword($user_id);
        $response = new stdClass();
        if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $user_id)->count() === 0){ // Player doesn't exist
            $params = ['agent' => ALLBET_AGENT, 'random' => mt_rand(), 'client' => $username, 'password' => $password, 'orHallRebate' => 0, 'language' => ALLBET_LANGUAGE];
            $result = $this->getResponse("check_or_create", $params);
            if ($result["error_code"] == "OK"){
                ConnectedKioskContact::create([
                    'connected_kiosk_id' => $connected_kiosk_id,
                    'contact_id' => $user_id
                ]);
            } else {
                $response->Success = false;
                $response->Message = "Error on Registering AllBet";

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

            $password = $this->getPassword($user_id);
            $params = ['random' => mt_rand(), 'client' => $username, 'password' => $password];
            $result = $this->getResponse("forward_game", $params);
            $response = new stdClass();
            if($result["error_code"] == "OK"){
                $response->Success = true;
                $response->ForwardUrl = $result["gameLoginUrl"];
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error on getting ForwardURL of AllBet";

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

            $params = ['random' => mt_rand(),'agent' => ALLBET_AGENT, 'sn' => ALLBET_PROPERTY_ID.'00'.$referenceID, 'client' => $username, 'operFlag' => '1', 'credit' => $amount];
            $result = $this->getResponse("agent_client_transfer", $params);
            $response = new stdClass();
            if($result["error_code"] == "OK"){
                $response->Success = true;
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $result["error_code"];

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

            $params = ['random' => mt_rand(),'agent' => ALLBET_AGENT, 'sn' => ALLBET_PROPERTY_ID.'00'.$referenceID, 'client' => $username, 'operFlag' => '0', 'credit' => $amount];
            $result = $this->getResponse("agent_client_transfer", $params);
            $response = new stdClass();
            if($result["error_code"] == "OK"){
                $response->Success = true;
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $result["error_code"];

                return $response;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    private function getResponse($subUrl, $params)
    {
        $data = TripleDES::encryptText(http_build_query($params), ALLBET_DES_KEY);

        $to_sign = $data.ALLBET_MD5_KEY;

        $sign = base64_encode(md5($to_sign, TRUE));
        $curl = curl_init(ALLBET_API_URL."/{$subUrl}?".http_build_query(array('data' => $data, 'sign' => $sign, 'propertyId' => ALLBET_PROPERTY_ID)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($data, true);
        return $result;
    }

    public function getBalance($username, $contact_id)
    {
        try {
            $password = $this->getPassword($contact_id);
            $params = ['random' => mt_rand(), 'client' => $username, 'password' => $password];
            $result = $this->getResponse("get_balance", $params);
            if ($result['error_code'] === "OK"){
                $response = new stdClass();
                $response->Success = true;
                $response->balance = $result["balance"];
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $result["message"];
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
