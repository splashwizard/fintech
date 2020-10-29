<?php

namespace App\Http\Controllers\API;

use App\Contact;
use App\GameId;
use App\Http\Controllers\Controller;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;
use Illuminate\Support\Facades\Hash;


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
        $required_fields = ['name', 'password'];
        $input = $request->only($required_fields);
        foreach ($required_fields as $key) {
            if(!isset($input[$key])){
                $output = ['success' => false,
                    'msg' => $key.' is missing!'
                ];
                return $output;
            }
        }

        $count = Contact::where('name', $input['name'])->count();
        if($count == 0){
            return ['success' => false,
                'msg' => "The username doesn't exist"
            ];
        }
        if(Hash::make($input['password']) == Contact::where('name', $input['name'])->first()->password){
            $output = ['success' => true,
                'msg' => "Login successfully"
            ];
        } else {
            $output = ['success' => false,
                'msg' => "The password doesn't match"
            ];
        }
        return $output;
    }

    public function signUp(Request $request) {
        $business_id = 21;

        $required_fields = ['name', 'email', 'password' , 'birthday', 'mobile', 'country_code_id'];
        $input = $request->only($required_fields);

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
                        'data' => $contact,
                        'msg' => __("contact.added_success").'</br>Name: '.$contact->name.'</br>Contact ID:'.$contact->contact_id
                    ];
                }
            }
        }

        return $output;
    }
}
