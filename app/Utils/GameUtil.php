<?php

namespace App\Utils;
use App\Utils\GameUtils\TransferWallet;

class GameUtil extends Util
{
    protected $games;
    protected $transferwallet;
    public function __construct(TransferWallet $transferwallet){
        $this->games = [
            'Xe88' => [
                "agentid" => "testapi112",
                "account_prefix" => "K112_",
                "signaturekey" => '76dce332-9e17-432b-b8a8-3df22e20f67a',
                "url" => 'http://xespublicapi.eznet88.com/'
            ]
        ];
        $this->transferwallet = $transferwallet;
    }

    public function createGameUser($game_key, $username){
        if($game_key == "XE88"){
            $agent_code_prefix = 'K112_';
            $password = "Whatpurpose!88";
            $account_name = $agent_code_prefix.$username;
            $requestbody = '{"agentid":"testapi112","account": "'.$account_name.'","password":"'.$password.'"}';

            $signaturekey= '76dce332-9e17-432b-b8a8-3df22e20f67a';

            $hashdata = hash_hmac("sha256", $requestbody, $signaturekey, true);

            $hash = base64_encode($hashdata);

            $headerstring = 'hashkey: ' . $hash;

            $headers = [
                $headerstring
            ];
            $url = 'http://xespublicapi.eznet88.com/player/create/';
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestbody);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);
            if(json_decode($response)->code == 31 || json_decode($response)->code == 0){ // Player name exist
                $gameid = 979;
                $link = "http://vgame.eznet88.com/index.html?language=En&gameid=".$gameid."&userid=".urlencode($account_name)."&userpwd=".md5($password);
                $output = ['success' => true, 'link' => $link];
            }
            else
                $output = ['success' => false, 'msg' => json_decode($response)->message];
        }
        else if($game_key == 'Transfer Wallet'){ //
            $result = $this->transferwallet->GetPlayGameUrlWithDepositAmount($username, 0.00, uniqid(),'s6xhiogba5dhe' );
            if ($result->Success == true) {
                $output = ['success' => true, 'link' => $result->ForwardUrl];
            }
            else
            {
                $output = ['success' => false, 'msg' => $result->Message];
            }
        } else $output = ['success' => true, 'link' => ''];
        return $output;
    }

    public function getBalance($game_key, $username){
        try{
            if($game_key == 'Xe88'){
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
                $resp = json_decode($response);
                if($resp->code == 0) {
                    return ['success' => true, 'balance' => $resp->result->balance];
                }
                return ['success' => false, 'msg' => $resp->message];
            }
            else if($game_key == 'Transfer Wallet'){ //
                $result = $this->transferwallet->GetUserCredit($username);
                if ($result->Success == true) {
                    $output = ['success' => true, 'balance' => $result->Credit];
                }
                else
                {
                    $output = ['success' => false, 'msg' => $result->Message];
                }
                return $output;
            }
        } catch (\Exception $e) {

            return ['success' => 0,
                'msg' => __('messages.something_went_wrong')
            ];
        }
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
        if($game_key == 'Xe88') {
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
        else if($game_key == 'Transfer Wallet'){ //Transfer Wallet
            $this->transferwallet->TransferCreditToJoker($username, $amount, uniqid());
            $output = ['success' => true];
            return $output;
        }
        return ['success' => true];
    }

    public function withdraw($game_key, $username, $amount){
        if($game_key == 'Xe88'){
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
        } else if($game_key == 'Transfer Wallet'){ //Transfer Wallet
             $result = $this->transferwallet->GetUserCredit($username);
             if ($result->Success == true) {
                 $resultTransferOut = $this->transferwallet->TransferCreditOutJoker($username, $amount, uniqid());
                 $output = ['success' => true];
             }
             else
             {
                 $output = ['success' => false, 'msg' => $result->Message];
             }
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
