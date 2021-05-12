<?php
namespace App\Utils\GameUtils;

use App\ConnectedKiosk;
use App\ConnectedKioskContact;
use App\Contact;
use stdClass;

class CT
{
    protected $host;
    protected $API_key;
    protected $agentName;
    protected $randStr_len;
    public function __construct(){
        $this->host = "http://api.ct-666.com";
        $this->API_key = "11efcd16502d4553941c3755a876858a";
        $this->agentName = "CTTE000188";
        $this->randStr_len = 6;
    }

    private function getPassword($contact_id)
    {
        $contact = Contact::find($contact_id);
        return !$contact['simple_password'] ? 'bgt54321' : encrypt_decrypt('decrypt', $contact['simple_password']);
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

    public function GetPlayGameUrl($connected_kiosk_id, $user_id, $username)
    {
        try {
            $password = $this->getPassword($user_id);
            if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $user_id)->count() === 0){
                $randstr = $this->generateRandomString($this->randStr_len);
                $fields = [
                    "token" => $this->getToken($randstr),
                    "random" => $randstr,
                    "member" => [
                        "username" => $username,
                        "password" => md5($password),
                        "currencyName" => "MYR",
                        "winLimit" => 1000
                    ]
                ];
                $url = "{$this->host}/api/signup/{$this->agentName}";
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
                if ($result['codeId'] == "0"){
                    ConnectedKioskContact::create([
                        'connected_kiosk_id' => $connected_kiosk_id,
                        'contact_id' => $user_id
                    ]);
                } else {
                    $response = new stdClass();
                    $response->Success = false;
                    $response->Message = "Error on Registering CT";

                    return $response;
                }
            }
            $result = $this->getLoginAccessToken($username);
            $response = new stdClass();
            if($result["codeId"] == 0){
                $forwardUrl = "{$result["list"][0]}{$result["token"]}&language=en";

                $response->Success = true;
                $response->ForwardUrl = $forwardUrl;
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error on logging in CT";

                return $response;
            }
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    private function getLoginAccessToken($username)
    {
        $randstr = $this->generateRandomString($this->randStr_len);
        $fields = [
            "token" => $this->getToken($randstr),
            "random" => $randstr,
            "lang" => "en",
            "device" => 1,
            "member" => [
                "username" => $username
            ]
        ];
        $url = "{$this->host}/api/login/{$this->agentName}";
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
        return $result;
    }

    public function deposit($connected_kiosk_id, $contact_id, $username, $referenceID, $amount)
    {
        try {
            if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->count() === 0){
                $randstr = $this->generateRandomString($this->randStr_len);
                $password = $this->getPassword($contact_id);
                $fields = [
                    "token" => $this->getToken($randstr),
                    "random" => $randstr,
                    "member" => [
                        "username" => $username,
                        "password" => md5($password),
                        "currencyName" => "MYR",
                        "winLimit" => 1000
                    ]
                ];
                $url = "{$this->host}/api/signup/{$this->agentName}";
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
                if ($result['codeId'] == "0"){
                    ConnectedKioskContact::create([
                        'connected_kiosk_id' => $connected_kiosk_id,
                        'contact_id' => $contact_id
                    ]);
                } else {
                    $response = new stdClass();
                    $response->Success = false;
                    $response->Message = "Error on Registering CT";

                    return $response;
                }
            }

            $randstr = $this->generateRandomString($this->randStr_len);
            $fields = [
                "token" => $this->getToken($randstr),
                "random" => $randstr,
                "data" => $referenceID,
                "member" => [
                    "username" => $username,
                    "amount" => $amount
                ]
            ];
            $url = "{$this->host}/api/transfer/{$this->agentName}";
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
            if($result["codeId"] == 0){
                $response->Success = true;
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = $postData;

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
            if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->count() === 0){
                $randstr = $this->generateRandomString($this->randStr_len);
                $password = $this->getPassword($contact_id);
                $fields = [
                    "token" => $this->getToken($randstr),
                    "random" => $randstr,
                    "member" => [
                        "username" => $username,
                        "password" => md5($password),
                        "currencyName" => "MYR",
                        "winLimit" => 1000
                    ]
                ];
                $url = "{$this->host}/api/signup/{$this->agentName}";
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
                if ($result['codeId'] == "0"){
                    ConnectedKioskContact::create([
                        'connected_kiosk_id' => $connected_kiosk_id,
                        'contact_id' => $contact_id
                    ]);
                } else {
                    $response = new stdClass();
                    $response->Success = false;
                    $response->Message = "Error on Registering CT";

                    return $response;
                }
            }

            $randstr = $this->generateRandomString($this->randStr_len);
            $fields = [
                "token" => $this->getToken($randstr),
                "random" => $randstr,
                "data" => $referenceID,
                "member" => [
                    "username" => $username,
                    "amount" => -$amount
                ]
            ];
            $url = "{$this->host}/api/transfer/{$this->agentName}";
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
            if($result["codeId"] == 0){
                $response->Success = true;
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
                $response->Message = "Error while withdrawing from CT";

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
//        try {
            $randstr = $this->generateRandomString($this->randStr_len);
            $fields = [
                "token" => $this->getToken($randstr),
                "random" => $randstr,
                "member" => [
                    "username" => $username
                ]
            ];
            $url = "{$this->host}/api/getBalance/{$this->agentName}";
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
            if($result["codeId"] == 0){
                $response->Success = true;
                $response->balance = $result["member"]["balance"];
                return $response;
            } else {
                $response = new stdClass();
                $response->Success = false;
//                $response->Message = "Error while getting CT balance.";
                $response->Message = json_encode($result);

                return $response;
            }
//        } catch (Exception $e) {
//            $response = new stdClass();
//            $response->Success = false;
//            $response->Message = $e->getMessage();
//            return $response;
//        }
    }
}
