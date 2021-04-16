<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\BankBrand;
use App\Contact;
use App\GameId;
use App\Http\Controllers\Controller;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;
use Illuminate\Support\Facades\Hash;
use Modules\Essentials\Notifications\EditCustomerNotification;
use Illuminate\Support\Str;
use stdClass;


class AuthController extends Controller
{
    protected $commonUtil;
    protected $moduleUtil;
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
    }

    public function login(Request $request) {
        $required_fields = ['name', 'password', 'business_id'];
        $input = $request->only($required_fields);
        foreach ($required_fields as $key) {
            if(!isset($input[$key])){
                $output = ['success' => false,
                    'msg' => $key.' is missing!'
                ];
                return $output;
            }
        }

        $count = Contact::where('name', $input['name'])->where('business_id', $input['business_id'])->count();
        if($count == 0){
            return ['success' => false,
                'msg' => "The username doesn't exist"
            ];
        }
        if(Hash::check($input['password'], Contact::where('name', $input['name'])->where('business_id', $input['business_id'])->first()->password)){
            $row = Contact::where('name', $input['name'])->where('business_id', $input['business_id'])->first();

            $token = Str::random(60);
            $row->api_token = hash('sha256', $token);
            $row->save();
            $output = ['success' => true,
                'msg' => "Login successfully",
                'data' => [
                    'token' => $token,
                    'business_id' => $row->business_id,
                    'user_id' => $row->id,
                    "username" => $row->name,
                    "mobile" => json_decode($row->mobile),
                    "email" => $row->email,
                    "birthday" => $row->birthday,
                    "bank_details" => json_decode($row->bank_details),
                ]
            ];
        } else {
            $output = ['success' => false,
                'msg' => "The password doesn't match"
            ];
        }
        return $output;
    }


    public function changePassword(Request $request) {
        $required_fields = ['user_id', 'new_password'];
        $input = $request->only($required_fields);
        foreach ($required_fields as $key) {
            if(!isset($input[$key])){
                $output = ['success' => false,
                    'msg' => $key.' is missing!'
                ];
                return $output;
            }
        }

        $count = Contact::where('id', $input['user_id'])->count();
        if($count == 0){
            return ['success' => false,
                'msg' => "The user doesn't exist"
            ];
        }
        $row = Contact::find($input['user_id']);
        $row->password = Hash::make($input['new_password']);
        $row->save();
        $output = ['success' => true,
            'msg' => "Password changed successfully",
        ];
        return $output;
    }

    public function addBankDetail(Request $request) {
        $new_bank_detail = $request->get('bank_detail');
        $contact_id = $request->get('user_id');
        $count = Contact::where('id', $contact_id)->count();
        if($count == 0){
            return ['success' => false,
                'msg' => "The user doesn't exist"
            ];
        }
        $contact = Contact::find($contact_id);
        $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
        $bank_details[] = $new_bank_detail;
        $contact->bank_details = json_encode($bank_details);
        $contact->save();
        $output = ['success' => true,
            'data' => [
                'business_id' => $contact->business_id,
                'user_id' => $contact->id,
                "username" => $contact->name,
                "mobile" => json_decode($contact->mobile),
                "email" => $contact->email,
                "birthday" => $contact->birthday,
                "bank_details" => json_decode($contact->bank_details),
            ]
        ];
        return $output;
    }

    public function signUp(Request $request) {

        $required_fields = ['name', 'password' , 'mobile', 'business_id'];
        $input = $request->only($required_fields);
        $business_id = $input['business_id'];

        foreach ($required_fields as $key) {
            if(!isset($input[$key])){
                $output = ['success' => false,
                    'msg' => $key.' is missing!'
                ];
                return $output;
            }
        }

        $mobile_list = $request->get('mobile');
        if(!is_array($mobile_list)){
            $mobile_list = [$mobile_list];
            $input['mobile'] = json_encode($mobile_list);
        }

        $data = Contact::where('banned_by_user', '!=', null)->get(['mobile']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile) {
                    foreach ($mobile_list as $new_mobile) {
                        if ($old_mobile == $new_mobile) {
                            $msg = ' has been banned in the system!';
                            $output = ['success' => false,
                                'msg' => $request->get('mobile') . $msg . ' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        $data = Contact::where('business_id', $business_id)->get(['mobile', 'blacked_by_user']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile){
                    foreach ($mobile_list as $new_mobile){
                        if($old_mobile == $new_mobile){
                            if($item->blacked_by_user){
                                $msg = ' has been blacklisted in the system!';
                            } else
                                $msg = ' already exist in the system!';
                            $output = ['success' => false,
                                'msg' => $new_mobile.$msg.' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        if(Contact::where('name', $request->get('name'))->count() > 0 && Contact::where('name', $request->get('name'))->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->count() && !empty($request->get('name'))){
            if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->count() > 0 && Contact::where('email', $request->get('email'))->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->count() && !empty($request->get('email'))){
            if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else{
            $contacts = Contact::where('business_id', $business_id)->get();
            $new_bank_details = $request->get('bank_details');
            $is_equal = 0;$bank_account_number = 0; $equal_id = 0;
            foreach ($contacts as $contact){
                if($is_equal)
                    break;
                $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                if(isset($new_bank_details)){
                    foreach ($bank_details as $bank_detail){
                        foreach ($new_bank_details as $new_bank_detail){
                            if(!empty($bank_detail->bank_brand_id)){
                                if($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number){
                                    $is_equal = 1;
                                    $equal_id = $contact->id;
                                    $bank_account_number = $bank_detail->account_number;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            if($is_equal){
                if(Contact::find($equal_id)->blacked_by_user)
                    $msg = ' has been blacklisted in the system!';
                else
                    $msg = ' already exist in the system!';
                $output = ['success' => false,
                    'msg' => $bank_account_number.$msg.' Please use another account number!'
                ];
            }
            else {
                $contacts = Contact::get();
                $new_bank_details = $request->get('bank_details');
                $is_equal = 0;$bank_account_number = 0; $equal_id = 0;
                foreach ($contacts as $contact){
                    if($is_equal)
                        break;
                    $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                    if(isset($new_bank_details)) {
                        foreach ($bank_details as $bank_detail) {
                            foreach ($new_bank_details as $new_bank_detail) {
                                if (!empty($bank_detail->bank_brand_id)) {
                                    if ($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number) {
                                        $is_equal = 1;
                                        $equal_id = $contact->id;
                                        $bank_account_number = $bank_detail->account_number;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
                if($is_equal && Contact::find($equal_id)->banned_by_user){
                    $msg = ' has been banned in the system!';
                    $output = ['success' => false,
                        'msg' => $bank_account_number.$msg.' Please use another account number!'
                    ];
                }
                else {

                    if (!$this->moduleUtil->isSubscribed($business_id)) {
                        return $this->moduleUtil->expiredResponse();
                    }
                    $input['type'] = 'customer';
                    $input['business_id'] = $business_id;
                    $input['password'] = Hash::make($input['password']);
                    $admin = User::whereHas(
                        'roles', function($q)  use ($business_id) {
                            $q->where('roles.name', 'Admin#'.$business_id);
                        }
                    )->first();
                    $input['created_by'] = $admin->id;
                    $bank_details = $request->get('bank_details');
                    $input['bank_details'] = !empty($bank_details) ? json_encode($bank_details) : null;

                    //Update reference count
                    $ref_count = $this->commonUtil->setAndGetReferenceCount('contacts', $business_id);

                    if (empty($input['contact_id'])) {
                        //Generate reference number
                        $input['contact_id'] = $this->commonUtil->generateReferenceNumber('contacts', $ref_count, $business_id);
                    }

                    $contact = Contact::create($input);

                    ActivityLogger::activity("Created customer, contact ID ".$contact->contact_id);


                    $game_ids = request()->get('game_ids');
                    if(isset($game_ids)){
                        foreach ($game_ids as $service_id => $game_id){
                            if(!empty($game_id['cur_game_id']) || !empty($game_id['old_game_id'])){
                                GameId::create([
                                    'service_id' => $service_id,
                                    'contact_id' => $contact->id,
                                    'cur_game_id' => $game_id['cur_game_id'],
                                    'old_game_id' => $game_id['old_game_id']
                                ]);
                            }
                        }
                    }

                    //Add opening balance
                    if (!empty($request->input('opening_balance'))) {
                        $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                    }

                    $output = ['success' => true,
                        'data' => [
                            'business_id' => $contact->business_id,
                            'user_id' => $contact->id,
                            "username" => $contact->name,
                            "mobile" => json_decode($contact->mobile),
                            "email" => $contact->email,
                            "birthday" => $contact->birthday,
                            "bank_details" => json_decode($contact->bank_details)
                        ],
                        'msg' => __("contact.added_success").'</br>Name: '.$contact->name.'</br>Contact ID:'.$contact->contact_id
                    ];
                }
            }
        }

        return $output;
    }

    public function updateUser(Request $request) {

        $required_fields = ['business_id', 'user_id', 'username', 'email' , 'birthday', 'mobile'];
        $input = $request->only($required_fields);

        foreach ($required_fields as $key) {
            if(!isset($input[$key])){
                $output = ['success' => false,
                    'msg' => $key.' is missing!'
                ];
                return $output;
            }
        }
        $business_id = $input['business_id'];
        $id = $input['user_id'];

        $mobile_list = [];
        $mobile_list[] = $input['mobile'];
        $data = Contact::where('business_id', $business_id)->where('id', '!=', $id)->get(['mobile', 'blacked_by_user']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile){
                    foreach ($mobile_list as $new_mobile){
                        if($old_mobile == $new_mobile){
                            if($item->blacked_by_user){
                                $msg = ' has been blacklisted in the system!';
                            } else
                                $msg = ' already exist in the system!';
                            $output = ['success' => false,
                                'msg' => $new_mobile.$msg.' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        $data = Contact::where('banned_by_user', '!=', null)->where('id', '!=', $id)->get(['mobile']);
        foreach ($data as $item){
            if(!empty($item->mobile)){
                foreach (json_decode($item->mobile) as $old_mobile) {
                    foreach ($mobile_list as $new_mobile) {
                        if ($old_mobile == $new_mobile) {
                            $msg = ' has been banned in the system!';
                            $output = ['success' => false,
                                'msg' => $request->get('mobile') . $msg . ' Please use another contact!'
                            ];
                            return $output;
                        }
                    }
                }
            }
        }

        if(Contact::where('name', $request->get('name'))->where('id', '!=', $id)->count() > 0 && Contact::where('name', $request->get('name'))->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->where('id', '!=', $id)->count() && !empty($request->get('name'))){
            if(Contact::where('name', $request->get('name'))->where('business_id', $business_id)->where('id', '!=', $id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('name').$msg.' Please use another IC Name!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->where('id', '!=', $id)->count() > 0 && Contact::where('email', $request->get('email'))->where('id', '!=', $id)->get()[0]->banned_by_user){
            $msg = ' has been banned in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->where('id', '!=', $id)->count() && !empty($request->get('email')) > 0){
            if(Contact::where('email', $request->get('email'))->where('business_id', $business_id)->where('id', '!=', $id)->get()[0]->blacked_by_user)
                $msg = ' has been blacklisted in the system!';
            else
                $msg = ' already exist in the system!';
            $output = ['success' => false,
                'msg' => $request->get('email').$msg.' Please use another email!'
            ];
        }
        else{
            $contacts = Contact::where('business_id', $business_id)->where('id', '!=', $id)->get();
            $new_bank_details = $request->get('bank_details');
            $is_equal = 0;$bank_account_number = 0; $equal_id = 0;
            foreach ($contacts as $contact){
                if($is_equal)
                    break;
                $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                foreach ($bank_details as $bank_detail){
                    foreach ($new_bank_details as $new_bank_detail){
                        if(!empty($bank_detail->bank_brand_id)){
                            if($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number){
                                $is_equal = 1;
                                $equal_id = $contact->id;
                                $bank_account_number = $bank_detail->account_number;
                                break;
                            }
                        }
                    }
                }
            }
            if($is_equal){
                if(Contact::find($equal_id)->blacked_by_user)
                    $msg = ' has been blacklisted in the system!';
                else
                    $msg = ' already exist in the system!';
                $output = ['success' => false,
                    'msg' => $bank_account_number.$msg.' Please use another account number!'
                ];
            }
            else {
                $contacts = Contact::where('id', '!=', $id)->get();
                $new_bank_details = $request->get('bank_details');
                $is_equal = 0;
                $bank_account_number = 0;
                $equal_id = 0;
                foreach ($contacts as $contact) {
                    if ($is_equal)
                        break;
                    $bank_details = empty($contact->bank_details) ? [] : json_decode($contact->bank_details);
                    foreach ($bank_details as $bank_detail) {
                        foreach ($new_bank_details as $new_bank_detail) {
                            if (!empty($bank_detail->bank_brand_id)) {
                                if ($new_bank_detail['bank_brand_id'] == $bank_detail->bank_brand_id && $new_bank_detail['account_number'] == $bank_detail->account_number) {
                                    $is_equal = 1;
                                    $equal_id = $contact->id;
                                    $bank_account_number = $bank_detail->account_number;
                                    break;
                                }
                            }
                        }
                    }
                }
                if ($is_equal && Contact::find($equal_id)->banned_by_user) {
                    $msg = ' has been banned in the system!';
                    $output = ['success' => false,
                        'msg' => $bank_account_number . $msg . ' Please use another account number!'
                    ];
                } else {
                    $input = $request->only(['business_id', 'email']);

                    if (!$this->moduleUtil->isSubscribed($business_id)) {
                        return $this->moduleUtil->expiredResponse();
                    }

                    $count = 0;

                    //Check Contact id
                    if (!empty($input['contact_id'])) {
                        $count = Contact::where('business_id', $business_id)
                            ->where('contact_id', $input['contact_id'])
                            ->where('id', '!=', $id)
                            ->count();
                    }

                    if ($count == 0) {
                        $contact = Contact::where('business_id', $business_id)->findOrFail($id);
                        $activity = 'Customer ID: ' . $contact->contact_id;
                        foreach ($input as $key => $value) {
                            $contact->$key = $value;
                        }
                        $new_bank_details = $request->get('bank_details');
                        if (!empty($contact->bank_details)) {
                            foreach (json_decode($contact->bank_details) as $old_bank_detail) {
                                foreach ($new_bank_details as $new_bank_detail) {
                                    if ($old_bank_detail->bank_brand_id == $new_bank_detail['bank_brand_id'] && $old_bank_detail->account_number != $new_bank_detail['account_number']) {
                                        $activity .= chr(10) . chr(13) . BankBrand::find($old_bank_detail->bank_brand_id)->name . ': ' . $old_bank_detail->account_number . ' >>>' . $new_bank_detail['account_number'];
                                    }
                                }
                            }
                        }
                        $contact->name = $request->get('username');
                        $dtime = date_create_from_format("d/m/Y", $request->get('birthday'));
                        $timestamp = $dtime->getTimestamp();
                        $birthday = date( 'Y-m-d', $timestamp);
                        $contact->birthday = $birthday;
                        $contact->bank_details = json_encode($new_bank_details);
                        $contact->mobile = json_encode($mobile_list);

                        $contact->save();

                        //Get opening balance if exists
                        $ob_transaction = Transaction::where('contact_id', $id)
                            ->where('type', 'opening_balance')
                            ->first();

                        if (!empty($ob_transaction)) {
                            $amount = $this->commonUtil->num_uf($request->input('opening_balance'));
                            $opening_balance_paid = $this->transactionUtil->getTotalAmountPaid($ob_transaction->id);
                            if (!empty($opening_balance_paid)) {
                                $amount += $opening_balance_paid;
                            }

                            $ob_transaction->final_total = $amount;
                            $ob_transaction->save();
                            //Update opening balance payment status
                            $this->transactionUtil->updatePaymentStatus($ob_transaction->id, $ob_transaction->final_total);
                        } else {
                            //Add opening balance
                            if (!empty($request->input('opening_balance'))) {
                                $this->transactionUtil->createOpeningBalanceTransaction($business_id, $contact->id, $request->input('opening_balance'));
                            }
                        }

                        $output = ['success' => true,
                            'msg' => __("contact.updated_success")
                        ];
                    } else {
                        throw new \Exception("Error Processing Request", 1);
                    }
                }
            }
        }
        return $output;
    }

    // Ace333 provider auth
    public function authenticate(Request $request) {
        $response = new stdClass();
        $response->playerID = 4973;
        $response->error = 0;
        $response->description = null;
        return json_encode($response);

        $required_fields = ['userName', 'password'];
        $business_id = 35;
        $input = $request->only($required_fields);
        foreach ($required_fields as $key) {
            if(!isset($input[$key])){
                $output = ['success' => false,
                    'msg' => $key.' is missing!'
                ];
                return $output;
            }
        }

        $count = Contact::where('name', $input['userName'])->where('business_id', $business_id)->count();
        if($count == 0){
            $response = new stdClass();
            $response->playerID = 0;
            $response->error = 1;
            $response->description = "Username doesn't exist";
            return json_encode($response);
        }
        if(Hash::check($input['password'], Contact::where('name', $input['userName'])->where('business_id', $business_id)->first()->password)){
            $row = Contact::where('name', $input['userName'])->where('business_id', $business_id)->first();

            $response = new stdClass();
            $response->playerID = $row->name;
            $response->error = 0;
            return json_encode($response);
        } else {
            $response = new stdClass();
            $response->playerID = 0;
            $response->error = 2;
            $response->description = "Password doesn't match ";
            return json_encode($response);
        }
    }
}

