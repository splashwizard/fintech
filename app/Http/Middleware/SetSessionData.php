<?php

namespace App\Http\Middleware;

use App\AdminHasBusiness;
use Closure;
use Illuminate\Support\Facades\Auth;
use App\Utils\BusinessUtil;

use App\Business;
use Modules\Essentials\Entities\EssentialsAttendance;
use App\Utils\ModuleUtil;

class SetSessionData
{
    protected $moduleUtil;
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Checks if session data is set or not for a user. If data is not set then set it.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->session()->has('user')) {
            $business_util = new BusinessUtil;

            $user = Auth::user();

            if($user->hasRole('Superadmin'))
                $business_id = Business::first()->id;
            else if($user->hasRole('Admin')){
                $business_id = AdminHasBusiness::where('user_id', $user->id)->first()->business_id;
            }
            else
                $business_id = $user->business_id;
            // clock in
            $count = EssentialsAttendance::where('business_id', $business_id)
                ->where('user_id', auth()->user()->id)
                ->whereNull('clock_out_time')
                ->count();
            if ($count == 0) {
                $data = [
                    'business_id' => $business_id,
                    'user_id' => auth()->user()->id,
                    'clock_in_time' => \Carbon::now(),
                    'clock_in_note' => null,
                    'ip_address' => $this->moduleUtil->getUserIpAddr()
                ];
                EssentialsAttendance::create($data);
            }


            $session_data = ['id' => $user->id,
                            'surname' => $user->surname,
                            'first_name' => $user->first_name,
                            'last_name' => $user->last_name,
                            'username' => $user->username,
                            'email' => $user->email,
                            'business_id' => $business_id,
                            'language' => $user->language,
                            ];
            $business = Business::findOrFail($business_id);

            $currency = $business->currency;
            $currency_data = ['id' => $currency->id,
                                'code' => $currency->code,
                                'symbol' => $currency->symbol,
                                'thousand_separator' => $currency->thousand_separator,
                                'decimal_separator' => $currency->decimal_separator
                            ];

            $request->session()->put('user', $session_data);
            $request->session()->put('business', $business);
            $request->session()->put('currency', $currency_data);
            if($user->hasRole('Superadmin')){
                $business_list = Business::get()->pluck('name','id');
                $request->session()->put('business_list', $business_list);
            } else if($user->hasRole('Admin') || $user->hasRole('Admin#' . $user->business_id)) {
                $data = AdminHasBusiness::where('user_id', $user->id)->get();
                $business_ids = [];
                if($user->hasRole('Admin#' . $user->business_id)){
                    $business_ids[] = $user->business_id;
                    foreach ($data as $row){
                        if($user->business_id != $row->business_id)
                            $business_ids[] = $row->business_id;
                    }
                } else {
                    foreach ($data as $row){
                        $business_ids[] = $row->business_id;
                    }
                }
                $business_list = Business::whereIn('id', $business_ids)->get()->pluck('name','id');
                $request->session()->put('business_list', $business_list);
            }

            //set current financial year to session
            $financial_year = $business_util->getCurrentFinancialYear($business->id);
            $request->session()->put('financial_year', $financial_year);
        }

        return $next($request);
    }
}
