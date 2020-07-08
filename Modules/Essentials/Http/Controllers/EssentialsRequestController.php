<?php

namespace Modules\Essentials\Http\Controllers;

use App\GameId;
use App\Transaction;
use App\TransactionPayment;
use App\User;
use App\Utils\ModuleUtil;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Essentials\Entities\EssentialsRequest;
use Modules\Essentials\Entities\EssentialsRequestType;
use Modules\Essentials\Notifications\NewRequestNotification;
use Modules\Essentials\Notifications\RequestStatusNotification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;


class EssentialsRequestController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $moduleUtil;
    protected $request_statuses;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->request_statuses = [
            'pending' => [
                'name' => __('lang_v1.pending'),
                'class' => 'bg-yellow',
            ],
            'approved' => [
                'name' => __('essentials::lang.approved'),
                'class' => 'bg-green'
            ],
            'cancelled' => [
                'name' => __('essentials::lang.cancelled'),
                'class' => 'bg-red'
            ]
        ];
        $this->transactionTypes = [
            'sell' => __('sale.sale'),
            'purchase' => __('lang_v1.purchase'),
            'sell_return' => __('lang_v1.sell_return'),
            'purchase_return' =>  __('lang_v1.purchase_return'),
            'opening_balance' => __('lang_v1.opening_balance'),
            'payment' => __('lang_v1.payment')
        ];
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        if (request()->ajax()) {
            $request = EssentialsRequest::where('essentials_requests.business_id', $business_id)
                        ->join('users as u', 'u.id', '=', 'essentials_requests.user_id')
                        ->join('essentials_request_types as rt', 'rt.id', '=', 'essentials_requests.essentials_request_type_id')
                        ->select([
                            'essentials_requests.id',
                            DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                            'rt.request_type',
                            'start_date',
                            'end_date',
                            'ref_no',
                            'essentials_requests.status',
                            'essentials_requests.business_id',
                            'reason',
                            'status_note'
                            ]);

            if (!empty(request()->input('user_id'))) {
                $request->where('essentials_requests.user_id', request()->input('user_id'));
            }

            if (!$is_admin) {
                $request->where('essentials_requests.user_id', auth()->user()->id);
            }

            if (!empty(request()->input('status'))) {
                $request->where('essentials_requests.status', request()->input('status'));
            }

            if (!empty(request()->input('request_type'))) {
                $request->where('essentials_requests.essentials_request_type_id', request()->input('request_type'));
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $request->whereDate('essentials_requests.start_date', '>=', $start)
                            ->whereDate('essentials_requests.start_date', '<=', $end);
            }

            return Datatables::of($request)
                ->addColumn(
                    'action',
                    function ($row) use ($is_admin) {
                        $html = '';
                        if ($is_admin) {
                            $html .= '<button class="btn btn-xs btn-danger delete-leave" data-href="' . action('\Modules\Essentials\Http\Controllers\EssentialsRequestController@destroy', [$row->id]) . '"><i class="fa fa-trash"></i> ' . __("messages.delete") . '</button>';
                        }

                        $html .= '&nbsp;<button class="btn btn-xs btn-info btn-modal" data-container=".view_modal"  data-href="' . action('\Modules\Essentials\Http\Controllers\EssentialsRequestController@activity', [$row->id]) . '"><i class="fa fa-edit"></i> ' . __("essentials::lang.activity") . '</button>';

                        return $html;
                    }
                )
                ->editColumn('start_date', function ($row) {
                    $start_date = \Carbon::parse($row->start_date);
                    $end_date = \Carbon::parse($row->end_date);

                    $diff = $start_date->diffInDays($end_date);
                    $diff += 1;
                    $start_date_formated = $this->moduleUtil->format_date($start_date);
                    $end_date_formated = $this->moduleUtil->format_date($end_date);
                    // return $start_date_formated . ' - ' . $end_date_formated . ' (' . $diff . str_plural(__('lang_v1.day'), $diff).')';
                    return $start_date_formated;
                })
                ->editColumn('status', function ($row) use ($is_admin) {
                    $status = '<span class="label ' . $this->request_statuses[$row->status]['class'] . '">'
                    . $this->request_statuses[$row->status]['name'] . '</span>';

                    if ($is_admin) {
                        $status = '<a href="#" class="change_status" data-status_note="' . $row->status_note . '" data-request-id="' . $row->id . '" data-orig-value="' . $row->status . '" data-status-name="' . $this->request_statuses[$row->status]['name'] . '"> ' . $status . '</a>';
                    }
                    return $status;
                })
                ->filterColumn('user', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        $users = [];
        if ($is_admin) {
            $users = User::forDropdown($business_id, false);
        }
        $request_statuses = $this->request_statuses;

        $request_types = EssentialsRequestType::forDropdown($business_id);

        return view('essentials::request.index')->with(compact('request_statuses', 'users', 'request_types', 'is_admin'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $request_types = EssentialsRequestType::forDropdown($business_id);

        $settings = request()->session()->get('business.essentials_settings');
        $settings = !empty($settings) ? json_decode($settings, true) : [];
        
        $instructions = !empty($settings['leave_instructions']) ? $settings['leave_instructions'] : '';

        return view('essentials::request.create')->with(compact('request_types', 'instructions'));
    }

    public function createWithTransaction($transaction_id){
        $business_id = request()->session()->get('user.business_id');

        $request_types = EssentialsRequestType::forDropdown($business_id);

        $settings = request()->session()->get('business.essentials_settings');
        $settings = !empty($settings) ? json_decode($settings, true) : [];

        $instructions = !empty($settings['leave_instructions']) ? $settings['leave_instructions'] : '';
        // get transaction info
        $query2 = TransactionPayment::join(
            'transactions as t',
            'transaction_payments.transaction_id',
            '=',
            't.id'
        )
            ->join('accounts as a', 'a.id', 'transaction_payments.account_id')
            ->leftJoin('business_locations as bl', 't.location_id', '=', 'bl.id')
            ->join('contacts as c', 'c.id', 't.contact_id')
            // ->where('t.contact_id', $contact_id)
            ->where('t.business_id', $business_id)
            ->where('t.id', $transaction_id);

        $payments = $query2->select('transaction_payments.*', 't.id as transaction_id', 't.bank_in_time as bank_in_time', 'bl.name as location_name', 't.type as transaction_type', 't.ref_no', 't.invoice_no'
            , 'c.id as contact_primary_key', 'c.contact_id as contact_id', 'c.is_default as is_default', 'a.id as account_id', 'a.name as account_name', 't.created_by as created_by')->get();

        $ledger_by_payment = [];
        foreach ($payments as $payment) {
            if(empty($ledger_by_payment[$payment->transaction_id])){
                $ref_no = in_array($payment->transaction_type, ['sell', 'sell_return']) ?  $payment->invoice_no :  $payment->ref_no;
                $user = User::find($payment->created_by);
                $ledger_by_payment[$payment->transaction_id] = [
                    'date' => $payment->paid_on,
                    'ref_no' => $payment->payment_ref_no,
                    'type' => $this->transactionTypes['payment'],
                    'location' => $payment->location_name,
                    'contact_id' => $payment->contact_id,
                    'payment_method' => !empty($paymentTypes[$payment->method]) ? $paymentTypes[$payment->method] : '',
                    'debit' => ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : 0,
                    'credit' => ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : 0,
                    'free_credit' => ($payment->card_type == 'credit' && $payment->method == 'free_credit') ? $payment->amount : 0 ,
                    'service_debit' => ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : 0,
                    'service_credit' => ($payment->card_type == 'credit' && $payment->method == 'service_transfer' ) ? $payment->amount : 0,
                    'others' => '<small>' . $ref_no . '</small>',
                    'bank_in_time' => $payment->bank_in_time,
                    'user' => $user['first_name'].' '.$user['last_name'],
                    'is_default' => $payment->is_default,
                    'account_name' => $payment->account_name
                ];
            } else {
                $ledger_by_payment[$payment->transaction_id]['debit'] += ($payment->card_type == 'debit' && $payment->method != 'service_transfer') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['credit'] += ($payment->card_type == 'credit' && $payment->method == 'bank_transfer') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['free_credit'] += ($payment->card_type == 'credit' && $payment->method == 'free_credit') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['service_debit'] += ($payment->card_type == 'debit' && $payment->method == 'service_transfer') ? $payment->amount : 0;
                $ledger_by_payment[$payment->transaction_id]['service_credit'] += ($payment->card_type == 'credit' && $payment->method == 'service_transfer' ) ? $payment->amount : 0;
            }
            if(($payment->transaction_type == 'sell' || $payment->transaction_type == 'sell_return' ) && $payment->method == 'service_transfer'){
                $ledger_by_payment[$payment->transaction_id]['service_name'] = $payment->account_name;
                $game_data = GameId::where('contact_id', $payment->contact_primary_key)->where('service_id', $payment->account_id)->get();
                if(count($game_data) >= 1){
                    $game_id = $game_data[0]->game_id;
                    $ledger_by_payment[$payment->transaction_id]['game_id'] = $game_id;
                }
            }
            if($payment->method == 'bank_transfer') {
                $ledger_by_payment[$payment->transaction_id]['bank_id'] = $payment->account_id;
            }
        }
        $transaction = $ledger_by_payment[$transaction_id];
        $transaction['id'] = $transaction_id;

        return view('essentials::request.create_with_transaction')->with(compact('request_types', 'instructions', 'transaction'));
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['essentials_request_type_id', 'start_date', 'end_date', 'reason']);
            
            $input['business_id'] = $business_id;
            $input['user_id'] = request()->session()->get('user.id');
            $input['status'] = 'pending';
//            $input['start_date'] = $this->moduleUtil->uf_date($input['start_date']);
//            $input['end_date'] = $this->moduleUtil->uf_date($input['end_date']);
            $input['start_date'] = $input['end_date'] = date('Y-m-d', strtotime('now'));
            $transaction_id = $request->get('transaction_id');
            if(!empty($transaction_id)){
                $input['transaction_id'] = $transaction_id;
                $request_data = [];
                $request_keys = ['bank_in_time', 'credit', 'debit', 'free_credit', 'basic_bonus', 'service_credit', 'service_debit', 'contact_id', 'service_id'];
                foreach ($request_keys as $request_key){
                    if(!empty($request->get($request_key))){
                        $request_data[$request_key] = $request->get($request_key);
                    }
                }
                $input['request_data'] = serialize($request_data);
            }

            //Update reference count
            $ref_count = $this->moduleUtil->setAndGetReferenceCount('leave');
            //Generate reference number
            if(!empty($transaction_id) && EssentialsRequest::where('transaction_id', $transaction_id)->count() > 0){
                $leave = EssentialsRequest::where('transaction_id', $transaction_id)->get()->first();
                $leave->update($input);
            }
            else {
                if (empty($input['ref_no'])) {
                    $settings = request()->session()->get('business.essentials_settings');
                    $settings = !empty($settings) ? json_decode($settings, true) : [];
    //                $prefix = !empty($settings['leave_ref_no_prefix']) ? $settings['leave_ref_no_prefix'] : '';
                    $input['ref_no'] = $this->moduleUtil->generateReferenceNumber('leave', $ref_count, null, 'req');
                }
                ActivityLogger::activity("Created request, reference no ".$input['ref_no']);

                $leave = EssentialsRequest::create($input);
            }
            $admins = $this->moduleUtil->get_admins($business_id);

            \Notification::send($admins, new NewRequestNotification($leave));

            $output = ['success' => true,
                            'msg' => __("lang_v1.added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return $output;
    }

    public function approveRequest(Request $request){

        try {
            $business_id = $request->session()->get('user.business_id');
            $transaction_id = $request->get('transaction_id');
//            $request_keys = ['bank_in_time', 'credit', 'debit', 'free_credit', 'basic_bonus', 'service_credit', 'service_debit'];
            if(!empty($request->get('bank_in_time'))) {
                $bank_in_time = $request->get('bank_in_time');
                Transaction::find($transaction_id)->update(['bank_in_time' => $bank_in_time]);
            }

            if(!empty($request->get('contact_id'))){
                $contact_id = $request->get('contact_id');
                Transaction::find($transaction_id)->update(['contact_id' => $contact_id]);
            }

            if(!empty($request->get('service_id'))){
                $service_id = $request->get('service_id');
                if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->count() > 0) {
                    TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->update(['account_id' => $service_id]);
                }
            }
            if(!empty($request->get('credit'))){
                $credit = $request->get('credit');
                if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'bank_transfer')->where('card_type','credit')->count() > 0){
                    TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'bank_transfer')->where('card_type','credit')->update(['amount' => $credit]);
                }
            }

            if(!empty($request->get('debit'))){
                $debit = $request->get('debit');
                if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', '!=', 'service_transfer')->where('card_type','credit')->count() > 0){
                    TransactionPayment::where('transaction_id', $transaction_id)->where('method', '!=', 'service_transfer')->where('card_type','debit')->update(['amount' => $debit]);
                }
            }

            if(!empty($request->get('free_credit'))){
                $free_credit = $request->get('free_credit');
                if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'free_credit')->where('card_type','credit')->count() > 0){
                    TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'free_credit')->where('card_type','credit')->update(['amount' => $free_credit]);
                }
            }

            if(!empty($request->get('basic_bonus'))){
                $basic_bonus = $request->get('basic_bonus');
                if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'basic_bonus')->where('card_type','credit')->count() > 0){
                    TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'basic_bonus')->where('card_type','credit')->update(['amount' => $basic_bonus]);
                }
            }


            if(!empty($request->get('service_credit'))){
                $service_credit = $request->get('service_credit');
                if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->where('card_type','credit')->count() > 0){
                    TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->where('card_type','credit')->update(['amount' => $service_credit]);
                }
            }


            if(!empty($request->get('service_debit'))){
                $service_debit = $request->get('service_debit');
                if(TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->where('card_type','debit')->count() > 0){
                    TransactionPayment::where('transaction_id', $transaction_id)->where('method', 'service_transfer')->where('card_type','debit')->update(['amount' => $service_debit]);
                }
            }
            $request_row = EssentialsRequest::where('transaction_id', $transaction_id)->get()->first();
            $request_row->update(['status' => 'approved']);
            ActivityLogger::activity("Approved request, reference no ".$request_row['ref_no']);
            $admins = $this->moduleUtil->get_admins($business_id);

            \Notification::send($admins, new NewRequestNotification($request_row));

            $output = ['success' => true,
                'msg' => __("lang_v1.approved_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    public function rejectRequest(Request $request){

        try {
            $business_id = $request->session()->get('user.business_id');
            $transaction_id = $request->get('transaction_id');
            $request_row = EssentialsRequest::where('transaction_id', $transaction_id)->get()->first();
            $request_row->update(['status' => 'cancelled']);
            ActivityLogger::activity("Approved request, reference no ".$request_row['ref_no']);
            $admins = $this->moduleUtil->get_admins($business_id);

            \Notification::send($admins, new NewRequestNotification($request_row));

            $output = ['success' => true,
                'msg' => __("lang_v1.approved_success")
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show()
    {
        return view('essentials::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit()
    {
        return view('essentials::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

                if ($is_admin) {
                    EssentialsRequest::where('business_id', $business_id)->where('id', $id)->delete();

                    $output = ['success' => true,
                                'msg' => __("lang_v1.deleted_success")
                            ];
                } else {
                    $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    public function changeStatus(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            if ($this->moduleUtil->is_admin(auth()->user(), $business_id)) {
                $input = $request->only(['status', 'request_id', 'status_note']);
            
                $leave = EssentialsRequest::where('business_id', $business_id)
                                ->find($input['request_id']);

                $leave->status = $input['status'];
                $leave->status_note = $input['status_note'];
                $leave->save();

                $leave->status = $this->request_statuses[$leave->status]['name'];

                $leave->changed_by = auth()->user()->id;

                $leave->user->notify(new RequestStatusNotification($leave));

                $output = ['success' => true,
                                'msg' => __("lang_v1.added_success")
                            ];
            } else {
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => "File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage()
                        ];
        }

        return $output;
    }

    public function getRequestData($transaction_id){
        if(EssentialsRequest::where('transaction_id', $transaction_id)->where('status', 'pending')->count() > 0){
            $row = EssentialsRequest::where('transaction_id', $transaction_id)->where('status', 'pending')->get()->first();
            $request_data = unserialize($row['request_data']);
            $output = [ 'exist' => 1, 'request_data' => $request_data, 'request_type_id' => $row['essentials_request_type_id'], 'reason' => $row['reason']];
        } else $output = [ 'exist' => 0];
        return $output;
    }

    /**
     * Function to show activity log related to a leave
     * @return Response
     */
    public function activity($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        $leave = EssentialsRequest::where('business_id', $business_id)
                                ->find($id);

        $activities = Activity::forSubject($leave)
                           ->with(['causer', 'subject'])
                           ->latest()
                           ->get();

        return view('essentials::request.activity_modal')->with(compact('leave', 'activities'));
    }

    /**
     * Function to get leave summary of a user
     * @return Response
     */
    public function getUserLeaveSummary()
    {
        $business_id = request()->session()->get('user.business_id');

        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $business_id);

        $user_id = $is_admin ? request()->input('user_id') : auth()->user()->id;

        if (!(auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'essentials_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (empty($user_id)) {
            return '';
        }

        $query = EssentialsRequest::where('business_id', $business_id)
                            ->where('user_id', $user_id)
                            ->with(['leave_type'])
                            ->select(
                                'status',
                                'essentials_request_type_id',
                                'start_date',
                                'end_date'
                            );

        if (!empty(request()->start_date) && !empty(request()->end_date)) {
            $start = request()->start_date;
            $end =  request()->end_date;
            $query->whereDate('start_date', '>=', $start)
                        ->whereDate('start_date', '<=', $end);
        }
        $leaves = $query->get();
        $statuses = $this->request_statuses;
        $leaves_summary = [];
        $status_summary = [];

        foreach ($statuses as $key => $value) {
            $status_summary[$key] = 0;
        }
        foreach ($leaves as $leave) {
            $start_date = \Carbon::parse($leave->start_date);
            $end_date = \Carbon::parse($leave->end_date);
            $diff = $start_date->diffInDays($end_date) + 1;
            
            $leaves_summary[$leave->essentials_request_type_id][$leave->status] =
            isset($leaves_summary[$leave->essentials_request_type_id][$leave->status]) ?
            $leaves_summary[$leave->essentials_request_type_id][$leave->status] + $diff : $diff;
        
            $status_summary[$leave->status] = isset($status_summary[$leave->status]) ? ($status_summary[$leave->status] + $diff) : $diff;
        }

        $leave_types = EssentialsRequestType::where('business_id', $business_id)
                                    ->get();
        $user = User::where('business_id', $business_id)
                    ->find($user_id);
        
        return view('essentials::request.user_leave_summary')->with(compact('leaves_summary', 'leave_types', 'statuses', 'user', 'status_summary'));
    }
}
