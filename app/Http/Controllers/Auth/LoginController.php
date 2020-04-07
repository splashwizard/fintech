<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use App\Utils\BusinessUtil;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * All Utils instance.
     *
     */
    protected $businessUtil;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(BusinessUtil $businessUtil)
    {
        $this->middleware('guest')->except('logout');
        $this->businessUtil = $businessUtil;
    }

    /**
     * Change authentication from email to username
     *
     * @return void
     */
    public function username()
    {
        return 'username';
    }

    public function logout()
    {
        $user_id = request()->session()->get('user.id');
        $row = User::where('id', $user_id)->first();
        $row->is_logged = false;
        $row->update();
        request()->session()->flush();
        \Auth::logout();
        return redirect('/login');
    }

    /**
     * The user has been authenticated.
     * Check if the business is active or not.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if(!$user->hasRole('Superadmin') && !$user->hasRole('Admin')){
            if (!$user->business->is_active) {
                \Auth::logout();
                return redirect('/login')
                  ->with(
                      'status',
                      ['success' => 0, 'msg' => __('lang_v1.business_inactive')]
                  );
            } elseif ($user->status != 'active') {
                \Auth::logout();
                return redirect('/login')
                  ->with(
                      'status',
                      ['success' => 0, 'msg' => __('lang_v1.user_inactive')]
                  );
            }
            elseif($user->ipaddr_restrict && $user->ipaddr_restrict != $request->ip()) {
//                print_r($_SERVER['REMOTE_ADDR']);exit;
                \Auth::logout();
                return redirect('/login')
                    ->with(
                        'status',
                        ['success' => 0, 'msg' => __('lang_v1.ipaddr_diff')]
                    );
            }
        }
//        if($user->is_logged){
//            \Auth::logout();
//            return redirect('/login')
//                ->with(
//                    'status',
//                    ['success' => 0, 'msg' => __('lang_v1.another_log')]
//                );
//        }
        $date = new \DateTime('now');
        $user->last_online = $date->format('Y-m-d H:i:s');
        $user->is_logged = true;
        $user->save();
    }

    protected function redirectTo()
    {
        $user = \Auth::user();
        if (!$user->can('dashboard.data') && $user->can('sell.create')) {
            return '/pos_deposit/create';
        }

        return '/home';
    }
}
