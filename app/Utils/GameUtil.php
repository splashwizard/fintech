<?php

namespace App\Utils;

class GameUtil extends Util
{
    protected $games;
    public function __construct(){
        $this->games = [
            'XE88' => [
                "agentid" => "testapi112",
                "account_prefix" => "K112_",
                "signaturekey" => '76dce332-9e17-432b-b8a8-3df22e20f67a',
                "url" => 'http://xespublicapi.eznet88.com/'
            ]
        ];
    }

    public function getPlayerInfo($game_key, $username){
        $game = $this->games[$game_key];
        $account = $game["account_prefix"].$username;
        $requestbody = '{"agentid":"'.$game["agentid"].'","account":"'.$account.'"}';

        $hashdata = hash_hmac("sha256", $requestbody, $game["signaturekey"], true);

        $hash = base64_encode($hashdata);

        $headerstring = 'hashkey: ' . $hash;

        $headers = [
            $headerstring
        ];

        $url = $game["url"]."player/info";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        return json_decode($response);
    }

    public function getGameReport($game_key, $username){
        $game = $this->games[$game_key];
        $account = $game["account_prefix"].$username;
        $requestbody = '{"agentid":"'.$game["agentid"].'","account":"'.$account.'","startdate":"2020-03-16 00:00:00","enddate":"2021-03-16 23:59:00"}';

        $hashdata = hash_hmac("sha256", $requestbody, $game["signaturekey"], true);

        $hash = base64_encode($hashdata);

        $headerstring = 'hashkey: ' . $hash;

        $headers = [
            $headerstring
        ];

        $url = $game["url"]."customreport/playerreport";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        return json_decode($response);
    }
    public function getGameLog($game_key, $username, $date){
        $game = $this->games[$game_key];
        $account = $game["account_prefix"].$username;
        $requestbody = '{"agentid":"'.$game["agentid"].'","account":"'.$account.'", "date":"'.$date.'", "starttime":"00:00:00", "endtime":"23:59:59"}';

        $hashdata = hash_hmac("sha256", $requestbody, $game["signaturekey"], true);

        $hash = base64_encode($hashdata);

        $headerstring = 'hashkey: ' . $hash;

        $headers = [
            $headerstring
        ];

        $url = $game["url"]."customreport/playergamelog";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        return json_decode($response);
    }
}
