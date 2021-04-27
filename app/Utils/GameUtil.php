<?php

namespace App\Utils;
use App\Account;
use App\ConnectedKiosk;
use App\ConnectedKioskContact;
use App\Contact;
use App\Promotion;
use App\Utils\GameUtils\Ace333;
use App\Utils\GameUtils\CT;
use App\Utils\GameUtils\TransferWallet;
use Symfony\Polyfill\Intl\Normalizer\Normalizer;

class GameUtil extends Util
{
    protected $games;
    protected $transferwallet;
    protected $ace333;
    protected $ct;
    protected $contactUtil;
    public function __construct(TransferWallet $transferwallet, Ace333 $ace333, CT $ct, ContactUtil $contactUtil){
        $this->games = [
            'Xe88' => [
                "agentid" => "testapi112",
                "account_prefix" => "K112_",
                "signaturekey" => '76dce332-9e17-432b-b8a8-3df22e20f67a',
                "url" => 'http://xespublicapi.eznet88.com/'
            ]
        ];
        $this->transferwallet = $transferwallet;
        $this->ace333 = $ace333;
        $this->ct = $ct;
        $this->contactUtil = $contactUtil;
    }

    public function createGameUser($promotion_id, $user_id){
        $connected_kiosk_id = Promotion::where('promotion_id', $promotion_id)->first()->connected_kiosk_id;
        if($connected_kiosk_id == 0){
            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")];
            return $output;
        }
        $game_name = ConnectedKiosk::find($connected_kiosk_id)->name;
        $username = Contact::find($user_id)->name;
        $game_code = Promotion::where('promotion_id', $promotion_id)->first()->game_code;
        if($game_name == "Xe88"){
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
                $link = "http://vgame.eznet88.com/index.html?language=En&gameid=".$game_code."&userid=".urlencode($account_name)."&userpwd=".md5($password);
                $output = ['success' => true, 'link' => $link];
            }
            else
                $output = ['success' => false, 'msg' => json_decode($response)->message];
        }
        else if($game_name == "Joker"){
            $result = $this->transferwallet->GetPlayGameUrlWithDepositAmount($username, 0.00, uniqid(),$game_code );
            if ($result->Success == true) {
                $output = ['success' => true, 'link' => $result->ForwardUrl];
            }
            else
            {
                $output = ['success' => false, 'msg' => $result->Message];
            }
        }
        else if ($game_name == "Ace333") {
            $result = $this->ace333->GetPlayGameUrl($connected_kiosk_id, $user_id, $username, $game_code);
            if ($result->Success == true) {
                $output = ['success' => true, 'link' => $result->ForwardUrl];
            }
            else
            {
                $output = ['success' => false, 'msg' => $result->Message];
            }
        }
        else if ($game_name == "CT") {
            $result = $this->ct->GetPlayGameUrl($connected_kiosk_id, $user_id, $username);
            if ($result->Success == true) {
                $output = ['success' => true, 'link' => $result->ForwardUrl];
            }
            else
            {
                $output = ['success' => false, 'msg' => $result->Message];
            }
        }
        else $output = ['success' => true, 'link' => ''];
        return $output;
    }

    public function getAllBalances($business_id, $contact_id) {
        $game_data = [];
        $game_data['Main Wallet'] = $this->contactUtil->getMainWalletBalance($business_id, $contact_id);
        $data = Account::where('business_id', $business_id)->where('is_service', 1)->where('connected_kiosk_id', '!=', 0)->get();
        foreach ($data as $row){
            $resp = $this->getBalance($row->connected_kiosk_id, $contact_id);
            if($resp['success']) {
                $game_data[$row->name] = $resp['balance'];
            }
        }
        return $game_data;
    }

    public function getBalance($connected_kiosk_id, $contact_id){
        try{
            $game_name = ConnectedKiosk::find($connected_kiosk_id)->name;
            $username = Contact::find($contact_id)->name;
            if($game_name == 'Xe88'){
                $game_data = $this->games['Xe88'];
                $account = $game_data["account_prefix"].$username;
                $requestbody = '{"agentid":"'.$game_data["agentid"].'","account":"'.$account.'"}';

                $hashdata = hash_hmac("sha256", $requestbody, $game_data["signaturekey"], true);

                $hash = base64_encode($hashdata);

                $headerstring = 'hashkey: ' . $hash;

                $headers = [
                    $headerstring
                ];

                $url = $game_data["url"]."player/info";
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
            else if($game_name == 'Joker'){ //
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
            else if($game_name == 'Ace333'){ //Ace333
                if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->count() > 0) {
                    $row = ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->first();
                    $result = $this->ace333->getBalance(json_decode($row->data)->playerID);
                    if ($result->Success == true) {
                        $output = ['success' => true, 'balance' => $result->balance];
                    }
                    else
                    {
                        $output = ['success' => false, 'msg' => $result->Message];
                    }
                } else
                    $output = ['success' => false];
                return $output;
            }
            else if($game_name == 'CT'){ //
                if(ConnectedKioskContact::where('connected_kiosk_id', $connected_kiosk_id)->where('contact_id', $contact_id)->count() > 0) {
                    $result = $this->ct->getBalance($username);
                    if ($result->Success == true) {
                        $output = ['success' => true, 'balance' => $result->balance];
                    }
                    else
                    {
                        $output = ['success' => false, 'msg' => $result->Message];
                    }
                } else
                    $output = ['success' => true, 'balance' => 0];
                return $output;
            }
            else {
                return ['success' => false];
            }
        } catch (\Exception $e) {

            return ['success' => false,
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

    public function deposit($connected_kiosk_id, $contact_id, $amount, $invoice_no = null){
        $game_name = ConnectedKiosk::find($connected_kiosk_id)->name;
        $username = Contact::find($contact_id)->name;
        if($game_name == 'Xe88') {
            $game = $this->games[$game_name];
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
            } else
                $output = ['success' => true];
        }
        else if($game_name == 'Joker'){ //Joker
            $message = $this->transferwallet->TransferCreditToJoker($username, $amount, uniqid());
            if($message == 'Success')
                $output = ['success' => true];
            else
                $output = ['success' => false, 'msg' => $message];
            return $output;
        }
        else if($game_name == 'Ace333'){ //Joker
            $output = $this->ace333->TopUp($connected_kiosk_id, $contact_id, $username, $invoice_no, $amount);
            $output = json_decode(json_encode($output), true);
        }
        else if($game_name == 'CT'){  //Joker
            $response = $this->ct->deposit($connected_kiosk_id, $contact_id, $username, $invoice_no, $amount);
            if($response->Success)
                $output = ['success' => true];
            else
                $output = ['success' => false, 'msg' => $response->Message];
        }
        else
            $output = ['success' => true];
        return $output;
    }

    public function withdraw($connected_kiosk_id, $contact_id, $amount, $invoice_no = null){
        $game_name = ConnectedKiosk::find($connected_kiosk_id)->name;
        $username = Contact::find($contact_id)->name;
        if($game_name == 'Xe88'){
            $game = $this->games[$game_name];
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
        } else if($game_name == 'Joker'){ //Joker
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
        } else if ($game_name == 'Ace333'){ //Ace333
            $output = $this->ace333->TopUp($connected_kiosk_id, $contact_id, $username, $invoice_no, $amount);
            $result = json_decode(json_encode($output), true);
            if ($result->Success == true) {
                $output = ['success' => true];
            }
            else
            {
                $output = ['success' => false, 'msg' => $result->Message];
            }
            return $output;
        }
        else if($game_name == 'CT'){  //Joker
            $output = $this->ct->withdraw($connected_kiosk_id, $contact_id, $username, $invoice_no, $amount);
            $output = json_decode(json_encode($output), true);
            return $output;
        }
        return ['success' => true];
    }

    public function transfer($contact_id, $from_kiosk_id, $to_kiosk_id, $amount, $invoice_no = null)
    {
        if($from_kiosk_id != 0){
            $resp = $this->withdraw($from_kiosk_id, $contact_id, $amount, $invoice_no);
            if($resp['success'] == false) { // Player name exist
                return $resp;
            }
        }
        if($to_kiosk_id != 0) {
            $resp = $this->deposit($to_kiosk_id, $contact_id, $amount, $invoice_no);
            if ($resp['success'] == false) { // Player name exist
                return $resp;
            }
        }
        return ['success' => true];
    }

}
