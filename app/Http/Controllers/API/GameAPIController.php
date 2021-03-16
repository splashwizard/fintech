<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Http\Controllers\Controller;
use App\TransactionPayment;
use App\Utils\GameUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7\Request as Req;



class GameAPIController extends Controller
{
    protected $gameUtil;
    public function __construct(
        TransactionUtil $transactionUtil,
        GameUtil $gameUtil
    ) {
        $this->transactionUtil = $transactionUtil;
        $this->gameUtil = $gameUtil;
    }
    public function createGameUser(Request $request) {
        $agent_code_prefix = 'K112_';
        $password = "Whatpurpose!88";
        try {
            $username = $request->get('username');
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
                $output = ['success' => true, 'account' => $account_name, 'hash' => md5($password)];
            }
            else
                $output = ['success' => false, 'account' => $response];

        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")];
        }
        return $output;
    }

    public function getGameInfo(Request $request) {
        try {
            $username = $request->get('username');
            $game_list = ['XE88'];
            $game_data = [];
            foreach ($game_list as $game){
                $resp = $this->gameUtil->getPlayerInfo($game, $username);
                if($resp->code == 0) {
                    $game_data[$game] = $resp->result->balance;
                }
            }
            $output = ['success' => true, 'data' => $game_data];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")];
        }
        return $output;
    }

    public function getGameReport(Request $request) {
        try {
            $username = $request->get('username');
            $game_list = ['XE88'];
            $data = [];
            for ($i = 0; $i < count($game_list); $i++ ){
                $resp = $this->gameUtil->getGameReport($game_list[$i], $username);
                if($resp->code == 0){ // Player name exist
                    $data[] = ['product_name' => $game_list[$i], 'total' => $resp->total];
                }
                else{
                    switch ($resp->code){
                        case 33:
                            $msg = 'Kiosk Admin is disabled, contact to company';
                            break;
                        case 34:
                            $msg = 'Access is denied, contact to company';
                            break;
                        case 41:
                            $msg = 'Player does not exists';
                            break;
                        case 46:
                            $msg = 'Date Time is not valid';
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
            }
            $output = ['success' => true, 'data' => $data];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")];
        }
        return $output;
    }

    public function getGameLog(Request $request) {
//        try {
            $username = $request->get('username');
            $game = $request->get('product_name'); // 'XE88'
            $date = $request->get('date'); // 'XE88'
            $resp = $this->gameUtil->getGameLog($game, $username, $date);
            if($resp->code == 0){ // Player name exist
                $output = ['success' => true, 'data' => $resp->result];
            }
            else{
                switch ($resp->code){
                    case 33:
                        $msg = 'Kiosk Admin is disabled, contact to company';
                        break;
                    case 34:
                        $msg = 'Access is denied, contact to company';
                        break;
                    case 41:
                        $msg = 'Player does not exists';
                        break;
                    case 46:
                        $msg = 'Date Time is not valid';
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
            }
//        } catch (\Exception $e) {
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")];
//        }
        return $output;
    }
}
