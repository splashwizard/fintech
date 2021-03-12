<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\ConnectedKiosk;
use App\Contact;
use App\GameId;
use App\Transaction;
use App\User;
use App\Utils\Util;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use App\Utils\ContactUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Account;
use App\AccountTransaction;
use App\TransactionPayment;

use Modules\Essentials\Entities\EssentialsRequest;
use Modules\Essentials\Notifications\NewRequestNotification;
use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;


use DB;

class ConnectedKioskController extends Controller
{
    protected $commonUtil;
    protected $transactionUtil;
    protected $contactUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil,
                                TransactionUtil $transactionUtil,
                                ContactUtil $contactUtil,
                                ModuleUtil $moduleUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->transactionUtil = $transactionUtil;
        $this->contactUtil = $contactUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $accounts = ConnectedKiosk::select(['name']);
            return DataTables::of($accounts)
                            ->make(true);
        }
        return view('connected_kiosk.index');
    }
    public function getKioskData($id){
        $data = ConnectedKiosk::find($id);
        if($data){
            return $data;
        }
    }
}
