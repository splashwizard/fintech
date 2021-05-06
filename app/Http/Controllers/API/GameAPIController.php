<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\Contact;
use App\Http\Controllers\Controller;
use App\Promotion;
use App\TransactionPayment;
use App\Utils\ContactUtil;
use App\Utils\GameUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7\Request as Req;



class GameAPIController extends Controller
{
    protected $gameUtil;
    protected $contactUtil;
    public function __construct(
        TransactionUtil $transactionUtil,
        GameUtil $gameUtil,
        ContactUtil $contactUtil
    ) {
        $this->transactionUtil = $transactionUtil;
        $this->gameUtil = $gameUtil;
        $this->contactUtil = $contactUtil;
    }
    public function createGameUser(Request $request) {
//        try {
            $user_id = $request->get('user_id');
            $promotion_id = $request->get('productId');
            $output = $this->gameUtil->createGameUser($promotion_id, $user_id);
            return $output;
//        } catch (\Exception $e) {
//            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
//
//            $output = ['success' => false, 'msg' => __("messages.something_went_wrong")];
//        }
        return $output;
    }

    public function getGameInfo(Request $request) {
        try {
            $username = $request->get('username');
            $business_id = $request->get('business_id');
            $id = $request->get('user_id');
            $game_data = $this->gameUtil->getAllBalances($business_id, $id);
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
            $game_list = ['Xe88'];
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
            $game = $request->get('product_name'); // 'Xe88'
            $date = $request->get('date'); // 'Xe88'
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

    public function mega(Request $request) {
        \Log::emergency("mega ".json_encode($request->all()));
        return;
    }
}
