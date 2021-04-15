<?php
namespace App\Utils\GameUtils;

use stdClass;

class Ace333
{
    protected $Provider_url;
    protected $H5_api_domain;
    protected $H5_game_domain;
    protected $token;
    public function __construct(){
        $this->Provider_url = "http://mega333.dynu.net:3160";
        $this->H5_api_domain = "http://apiplay.mega333.dynu.net:8801";
        $this->H5_game_domain = "http://staging.mega777.net:8080/h5/h5games";
        $this->token = "Q2Fwalk1WCs4WkdIRC9WaE9DTGJvbmt1V3luNFMzZW5QK0FUUHViUGpNRFd6Y3BXaFFQVmgvRWtJcHhWNVQvUXpDM2FFeG4xZkZRVVJZNkdQSkpPSVNydGxCM2FkZ3VyZjdlbkdSYXdoRXpkdGV2WTA3ZlEybUYyYksvN1ptN0x0QjJZMzJndlZTZ2VhbHc3a0NGUnlBPT0=";
    }

    private function pkcsPadding($str, $blocksize)
    {
        $pad = $blocksize - (strlen($str) % $blocksize);
        return $str . str_repeat(chr($pad), $pad);
    }

    private function DESEncrypt($str, $key)
    {
        $sign = openssl_encrypt($str, 'DES-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, $key);
        $sign = base64_encode($sign);
        return $sign;
    }

    private function getLoginAccessToken($username)
    {
        $secretKey = "BBBBaaaa";
        $md5Key = "UUUUqqqq";
        $encryptKey = "MMMMzzzz";
        $delimiter = "♂♫‼◄¶";
        $currTime = date("YmdHms"); // Time format "yyyyMMddHHmmss" of current time, UTC +0
        $userName = "{$username}@jdj"; // UserName of player
        $password = "bgt54321"; // Password of player on OPERATOR side
        $currency = "MYR"; // The currency code of player.
        $nickName = $username; // Nick name of player, for display purposes
        // Build QS (request string) before encryption
        $QS="key={$secretKey}{$delimiter}time={$currTime}{$delimiter}userName={$userName}{$delimiter}password={$password}{$delimiter}currency={$currency}{$delimiter}nickName={$nickName}";
        // Build "q" (Encrypted Request String) from QS
        $str = $QS;
        $str = $this->pkcsPadding($str, 8);
        $q = urlencode($this->DESEncrypt($str, $encryptKey));
        // Build "s" (Signature)
        $s = md5($QS.$md5Key.$currTime.$secretKey);

        $fields = [
            "q" => $q,
            "s" => $s,
            "accessToken" => $this->token,
        ];
        $postData = json_encode($fields);
        $curl = curl_init("{$this->H5_api_domain}/api/Acc/Login");
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json', "Token: {$this->token}")
        );

        $data = curl_exec($curl);
        curl_close($curl);

        $result = json_decode($data, true);
        return $result;
    }

    public function GetPlayGameUrl($username, $gameCode)
    {
        try {
            $fields = [
                "accountID" => $username,
                "nickname" => $username,
                "currency" => "MYR",
            ];
            $url = $this->Provider_url . "/api/createPlayer";

            $postData = json_encode($fields);
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json', "Token: {$this->token}")
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            $response = new stdClass();
            if ($result['error'] == "0" || $result['error'] == "1000") {
                $result = $this->getLoginAccessToken($username);
                if($result["status"] === "1"){
                    $forwardUrl = "{$this->H5_game_domain}/{$gameCode}?actk={$result["actk"]}&lang=1&userName={$username}";

                    $response->Success = true;
                    $response->ForwardUrl = $forwardUrl;
                    return $response;
                }
            }
            $response->Success = false;
            $response->Message = $result['description'];

            return $response;
        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
            return $response;
        }
    }

    public function GetGameList()
    {
        try {
            $secretKey = "BBBBaaaa";
            $md5Key = "UUUUqqqq";
            $encryptKey = "MMMMzzzz";
            $delimiter = "♂♫‼◄¶";
            $currTime = date("YmdHms"); // Time format "yyyyMMddHHmmss" of current time, UTC +0
            // Build QS (request string) before encryption
            $QS="key={$secretKey}{$delimiter}time={$currTime}{$delimiter}gameType=1";
            // Build "q" (Encrypted Request String) from QS
            $str = $QS;
            $str = $this->pkcsPadding($str, 8);
            $q = urlencode($this->DESEncrypt($str, $encryptKey));
            // Build "s" (Signature)
            $s = md5($QS.$md5Key.$currTime.$secretKey);

            $fields = [
                "q" => $q,
                "s" => $s,
                "accessToken" => $this->token,
            ];
            $curl = curl_init("{$this->H5_api_domain}/api/Game/GameList?".http_build_query($fields));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json', "Token: {$this->token}")
            );

            $data = curl_exec($curl);
            curl_close($curl);

            $result = json_decode($data, true);
            $response = new stdClass();
            $response->Success = true;
            $response->data =$result;

        } catch (Exception $e) {
            $response = new stdClass();
            $response->Success = false;
            $response->Message = $e->getMessage();
        }
        return json_encode($response);
    }
}