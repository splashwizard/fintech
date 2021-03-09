<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Http\Controllers\Controller;
use App\TransactionPayment;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7\Request as Req;



class GameAPIController extends Controller
{
    public function __construct(
        TransactionUtil $transactionUtil
    ) {
        $this->transactionUtil = $transactionUtil;
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
}
