<?php
namespace App\Utils\GameUtils;

use App\ConnectedKiosk;
use App\ConnectedKioskContact;
use App\Contact;
use stdClass;

class Pussy
{
    protected $Api_host;
    protected $secretKey;
    protected $secureLogin;
    protected $agent;
    protected $language;
    public function __construct(){
        $this->Api_host = "http://api.pussy888.com/";
        $this->secretKey = "72SPx566y29y3r2Qe44w";
        $this->authCode = "qyZDyNmBzdAfJKPDERwt";
        $this->agentUser = "jdjmain";
    }

    private function getPassword($contact_id)
    {
        $contact = Contact::find($contact_id);
        return !$contact['simple_password'] ? 'Bgt54321' : encrypt_decrypt('decrypt', $contact['simple_password']);
    }

    private function getResponse($subUrl, $username, $params)
    {
        $params['userName'] = $username;
        $params['time'] = time();
        $params['authcode'] = $this->authCode;
        $params['sign'] = strtoupper(md5(strtolower($this->authCode.$username.$params['time'].$this->secretKey)));
        $url = $this->Api_host.$subUrl."?".http_build_query($params);
        $curl = curl_init($url);
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
            $params = ['action' => "RandomUserName"];
            $result = $this->getResponse("ashx/account/account.ashx", $this->agentUser, $params);
            if ($result["success"] == true){
                $account = $result['account'];
                $mobile = Contact::find($user_id)->mobile;
                $params = [ 'action' => 'addUser', 'agent' => $this->agentUser, 'PassWd' => $this->getPassword($user_id), 'userName' => $account, 'Name' => $username,
                    'Tel' => !empty($mobile) ? json_decode($mobile)[0] : '', 'Memo' => '', 'UserType' => '1', 'pwdtype' => 1];
                $result = $this->getResponse("ashx/account/account.ashx", $account, $params);
                if ($result["success"] == true){
                    ConnectedKioskContact::create([
                        'connected_kiosk_id' => $connected_kiosk_id,
                        'contact_id' => $user_id,
                        'data' => json_encode(['account' => $account])
                    ]);
                } else {
                    $response->Success = false;
                    $response->Message = "Error on registering pussy88";

                    return $response;
                }
            } else {
                $response->Success = false;
                $response->Message = "Error on getting username for pussy88";

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
            $util = new \App\Utils\Util();
            $ip_address = $util->getUserIpAddr();
            \Log::emergency("IpAddr:" . $_SERVER['REMOTE_ADDR']);
            $response = new stdClass();
            $response->Success = true;
            $response->ForwardUrl = "http://dl9.pussy888.com";
            return $response;
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

            $account = json_decode(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->first()->data)->account;
            $params = ['action' => "setServerScore", 'scoreNum' => $amount, 'userName' => $account, 'ActionUser' => $username, 'ActionIp'];
            $result = $this->getResponse("ashx/account/setScore.ashx", $this->agentUser, $params);
            if ($result["success"] == true){
                $response->Success = true;
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
//
//
//    public function withdraw($connected_kiosk_id, $contact_id, $username, $referenceID, $amount)
//    {
//        try {
//            $response = $this->createUserIfNotExist($connected_kiosk_id, $contact_id, $username);
//            if($response->Success === false) return $response;
//
//            $params = ['secureLogin' => $this->secureLogin, 'externalPlayerId' => $username, 'externalTransactionId' => $referenceID, 'amount' => -$amount];
//            $result = $this->getResponse("balance/transfer", $params);
//            $response = new stdClass();
//            if ($result["error"] == "0"){
//                $response->Success = true;
//                $response->balance = $result["balance"];
//                return $response;
//            } else {
//                $response = new stdClass();
//                $response->Success = false;
//                $response->Message = "Error on withdrawing PlayTech";
//
//                return $response;
//            }
//        } catch (Exception $e) {
//            $response = new stdClass();
//            $response->Success = false;
//            $response->Message = $e->getMessage();
//            return $response;
//        }
//    }
//
//    public function getBalance($username)
//    {
//        try {
//            $params = ['secureLogin' => $this->secureLogin, 'externalPlayerId' => $username];
//            $result = $this->getResponse("balance/current", $params);
//            $response = new stdClass();
//            if ($result["error"] == "0"){
//                $response->Success = true;
//                $response->balance = $result["balance"];
//                return $response;
//            } else {
//                $response = new stdClass();
//                $response->Success = false;
//                $response->Message = "Error on getting Balance of PlayTech";
//
//                return $response;
//            }
//        } catch (Exception $e) {
//            $response = new stdClass();
//            $response->Success = false;
//            $response->Message = $e->getMessage();
//            return $response;
//        }
//    }
}
