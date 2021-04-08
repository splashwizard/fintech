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
            $accounts = ConnectedKiosk::select(['name', 'short_name']);
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

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $account_types = Account::accountTypes();
        $connected_kiosks = ConnectedKiosk::forDropdown(true);

        return view('connected_kiosk.create')
            ->with(compact('account_types', 'connected_kiosks'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'short_name']);
                ConnectedKiosk::create($input);

                $output = ['success' => true,
                    'msg' => __("account.connected_kiosk_created_success")
                ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output = ['success' => false,
                    'msg' => __("messages.something_went_wrong")
                ];
            }

            return $output;
        }
    }
}
