<?php

namespace App\Utils;

class GameUtil extends Util
{
    protected $games;
    public function __construct(){
        $this->games = [
            'Xe88' => [
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

    public function deposit($game_key, $username, $amount){
        if($game_key != 'Xe88'){
            return ['success' => true];
        }
        $game = $this->games[$game_key];
        $account = $game["account_prefix"].$username;
        $requestbody = '{"agentid":"'.$game["agentid"].'","account":"'.$account.'", "amount": "'.$amount.'"}';

        $hashdata = hash_hmac("sha256", $requestbody, $game["signaturekey"], true);

        $hash = base64_encode($hashdata);

        $headerstring = 'hashkey: ' . $hash;

        $headers = [
            $headerstring
        ];

        $url = $game["url"]."player/deposit";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $response = json_decode($response);

        if($response->code != 0){
            switch ($response->code){
                case 33:
                    $msg = 'Kiosk Admin is disabled, contact to company';
                    break;
                case 34:
                    $msg = 'Access is denied, contact to company';
                    break;
                case 35:
                    $msg = 'Kiosk admin doesn’t have enough balance to deposit, please deposit first';
                    break;
                case 37:
                    $msg = 'The possible values of amount can be only numbers';
                    break;
                case 38:
                    $msg = 'Cannot make deposit, Amount is less than minimum deposit amount for this player';
                    break;
                case 41:
                    $msg = 'Player does not exists';
                    break;
                case 42:
                    $msg = 'Player is frozen';
                    break;
                case 72:
                    $msg = "Could not load data. Error: 'Service error accessing API'";
                    break;
                case 73:
                    $msg = "Could not load data from database. Error: 'Database error occured, please contact support'. Please, try again later";
                    break;
                default:
                    $msg = __("messages.something_went_wrong");
            }
            $output = ['success' => false, 'msg' => $msg];
            return $output;
        }
        return ['success' => true];
    }

    public function withdraw($game_key, $username, $amount){
        if($game_key != 'Xe88'){
            return ['success' => true];
        }
        $game = $this->games[$game_key];
        $account = $game["account_prefix"].$username;
        $requestbody = '{"agentid":"'.$game["agentid"].'","account":"'.$account.'", "amount": "'.$amount.'"}';

        $hashdata = hash_hmac("sha256", $requestbody, $game["signaturekey"], true);

        $hash = base64_encode($hashdata);

        $headerstring = 'hashkey: ' . $hash;

        $headers = [
            $headerstring
        ];

        $url = $game["url"]."player/withdraw";
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbody);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        $response = json_decode($response);

        if($response->code != 0) { // Player name exist
            switch ($response->code) {
                case 33:
                    $msg = 'Kiosk Admin is disabled, contact to company';
                    break;
                case 34:
                    $msg = 'Access is denied, contact to company';
                    break;
                case 36:
                    $msg = 'Can’t withdraw, because player is playing game now.';
                    break;
                case 37:
                    $msg = 'The possible values of amount can be only numbers';
                    break;
                case 39:
                    $msg = 'Cannot make withdraw, Amount is not bigger than current balance.';
                    break;
                case 41:
                    $msg = 'Player does not exists';
                    break;
                case 42:
                    $msg = 'Player is frozen';
                    break;
                case 72:
                    $msg = "Could not load data. Error: 'Service error accessing API'";
                    break;
                case 73:
                    $msg = "Could not load data from database. Error: 'Database error occured, please contact support'. Please, try again later";
                    break;
                default:
                    $msg = __("messages.something_went_wrong");
            }
            $output = ['success' => false, 'msg' => $msg];
            return $output;
        }
        return ['success' => true];
    }

    public function transfer($username, $from_game, $to_game, $amount)
    {
        $resp = $this->withdraw($from_game, $username, $amount);
        if($resp['success'] == false) { // Player name exist
            return $resp;
        }
        $resp = $this->deposit($to_game, $username, $amount);
        if($resp['success'] == false) { // Player name exist
            return $resp;
        }
        return ['success' => true];
    }

}
