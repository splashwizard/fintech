<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
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

class ServiceController extends Controller
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

        $business_id = session()->get('user.business_id');
        if (request()->ajax()) {
            $accounts = Account::leftjoin('account_transactions as AT', function ($join) {
                $join->on('AT.account_id', '=', 'accounts.id');
                $join->whereNull('AT.deleted_at');
            })
                ->leftjoin( 'transactions as T',
                    'AT.transaction_id',
                    '=',
                    'T.id')
                                ->where('accounts.is_service', 1)
                                ->where('accounts.business_id', $business_id)
                                ->where(function ($q) {
                                    $q->where('T.payment_status', '!=', 'cancelled');
                                    $q->orWhere('T.payment_status', '=', null);
                                })
                                ->select(['accounts.name', 'accounts.account_number', 'accounts.note', 'accounts.id',
                                    'accounts.is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")])
                                ->groupBy('accounts.id');

            $account_type = request()->input('account_type');

            if ($account_type == 'capital') {
                $accounts->where('account_type', 'capital');
            } elseif ($account_type == 'other') {
                $accounts->where(function ($q) {
                    $q->where('account_type', '!=', 'capital');
                    $q->orWhereNull('account_type');
                });
            }

            $accounts->where('name', '!=', 'Safe Kiosk Account');

            $is_admin_or_super = auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin');

            return DataTables::of($accounts)
                            ->addColumn(
                                'action',
                                $is_admin_or_super?
                                '<button data-href="{{action(\'ServiceController@edit\',[$id])}}" data-container=".account_model" class="btn btn-xs btn-primary btn-modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                                <a href="{{action(\'ServiceController@show\',[$id])}}" class="btn btn-warning btn-xs"><i class="fa fa-book"></i> @lang("account.account_book")</a>&nbsp;
                                <button data-href="{{action(\'ServiceController@getFundTransfer\',[$id])}}" class="btn btn-xs btn-info btn-modal" data-container=".view_modal"><i class="fa fa-exchange"></i> @lang("account.fund_transfer")</button>

                                <button data-href="{{action(\'ServiceController@getDeposit\',[$id])}}" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fa fa-money"></i> @lang("account.deposit")</button>

                                <button data-url="{{action(\'ServiceController@close\',[$id])}}" class="btn btn-xs btn-danger close_account"><i class="fa fa-close"></i> @lang("messages.close")</button>
                                <button data-href="{{action(\'ServiceController@getWithdraw\',[$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="fa fa-money"></i> @lang("account.withdraw")</button>
                                ':'<a href="{{action(\'ServiceController@show\',[$id])}}" class="btn btn-warning btn-xs"><i class="fa fa-book"></i> @lang("account.account_book")</a>&nbsp;
                                    <button data-href="{{action(\'ServiceController@getWithdraw\',[$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="fa fa-money"></i> @lang("account.withdraw")</button>'
                            )
                            ->editColumn('name', function ($row) {
                                if ($row->is_closed == 1) {
                                    return $row->name . ' <small class="label pull-right bg-red no-print">' . __("account.closed") . '</small><span class="print_section">(' . __("account.closed") . ')</span>';
                                } else {
                                    return $row->name;
                                }
                            })
                            ->editColumn('balance', function ($row) {
                                return '<span class="display_currency" >' . $row->balance . '</span>';
                            })
                            ->removeColumn('id')
                            ->removeColumn('is_closed')
                            ->rawColumns(['action', 'balance', 'name'])
                            ->make(true);
        }

        $not_linked_payments = TransactionPayment::leftjoin(
            'transactions as T',
            'transaction_payments.transaction_id',
            '=',
            'T.id'
        )
                                    ->whereNull('transaction_payments.parent_id')
                                    ->where('transaction_payments.business_id', $business_id)
                                    ->whereNull('account_id')
                                    ->count();

        // $capital_account_count = Account::where('business_id', $business_id)
        //                             ->NotClosed()
        //                             ->where('account_type', 'capital')
        //                             ->count();

        return view('service.index')
                ->with(compact('not_linked_payments'));
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

        return view('service.create')
                ->with(compact('account_types'));
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
                $input = $request->only(['name', 'account_number', 'note']);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $input['business_id'] = $business_id;
                $input['created_by'] = $user_id;
                $input['account_type'] = 'saving_current';
                $input['is_service'] = 1;

                $account = Account::create($input);

                //Opening Balance
                $opening_bal = $request->input('opening_balance');

                if (!empty($opening_bal)) {
                    $ob_transaction_data = [
                        'amount' =>$this->commonUtil->num_uf($opening_bal),
                        'account_id' => $account->id,
                        'type' => 'credit',
                        'sub_type' => 'opening_balance',
                        'operation_date' => \Carbon::now(),
                        'created_by' => $user_id
                    ];

                    AccountTransaction::createAccountTransaction($ob_transaction_data);
                }
                
                $output = ['success' => true,
                            'msg' => __("account.account_created_success")
                        ];
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                    
                $output = ['success' => false,
                            'msg' => __("messages.something_fwent_wrong")
                            ];
            }

            return $output;
        }
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $accounts = AccountTransaction::join(
                'accounts as A',
                'account_transactions.account_id',
                '=',
                'A.id'
            )
                            ->where('A.business_id', $business_id)
                            ->where('A.id', $id)
                            ->with(['transaction', 'transaction.contact', 'transfer_transaction'])
                            ->select(['type', 'amount', 'operation_date',
                                'sub_type', 'transfer_transaction_id',
                                DB::raw('(SELECT SUM(IF(AT.type="credit", AT.amount, -1 * AT.amount)) from account_transactions as AT WHERE AT.operation_date <= account_transactions.operation_date AND AT.account_id  =account_transactions.account_id AND AT.deleted_at IS NULL) as balance'),
                                'transaction_id',
                                'account_transactions.id'
                                ])
                             ->groupBy('account_transactions.id')
                             ->orderBy('account_transactions.operation_date', 'desc');
            if (!empty(request()->input('type'))) {
                $accounts->where('type', request()->input('type'));
            }

            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');
            
            if (!empty($start_date) && !empty($end_date)) {
                $accounts->whereBetween(DB::raw('date(operation_date)'), [$start_date, $end_date]);
            }
            return DataTables::of($accounts)
                            ->addColumn('debit', function ($row) {
                                if ($row->type == 'debit') {
                                    $html = '<span class="display_currency" data-currency_symbol="true">' . $row->amount . '</span>';

                                    if( isset($row->transaction) && $row->transaction->payment_status == 'cancelled')
                                        $html = '<strike>'.$html.'</strike>';
                                    return $html;
                                }
                                return '';
                            })
                            ->addColumn('credit', function ($row) {
                                if ($row->type == 'credit') {
                                    $html = '<span class="display_currency" data-currency_symbol="true">' . $row->amount . '</span>';

                                    if( isset($row->transaction) && $row->transaction->payment_status == 'cancelled')
                                        $html = '<strike>'.$html.'</strike>';
                                    return $html;
                                }
                                return '';
                            })
                            ->editColumn('balance', function ($row) {
                                $html = '<span class="display_currency" data-currency_symbol="true">' . $row->balance . '</span>';

                                if( isset($row->transaction) && $row->transaction->payment_status == 'cancelled')
                                    $html = '<strike>'.$html.'</strike>';
                                return $html;
                            })
                            ->editColumn('operation_date', function ($row) {
                                $html = $this->commonUtil->format_date($row->operation_date, true);

                                if( isset($row->transaction) && $row->transaction->payment_status == 'cancelled')
                                    $html = '<strike>'.$html.'</strike>';
                                return $html;
                            })
                            ->editColumn('sub_type', function ($row) {
                                $details = '';
                                if (!empty($row->sub_type)) {
                                    $details = __('account.' . $row->sub_type);
                                    if (in_array($row->sub_type, ['fund_transfer', 'deposit']) && !empty($row->transfer_transaction)) {
                                        if ($row->type == 'credit') {
                                            $details .= ' ( ' . __('account.from') .': ' . $row->transfer_transaction->account->name . ')';
                                        } else {
                                            $details .= ' ( ' . __('account.to') .': ' . $row->transfer_transaction->account->name . ')';
                                        }
                                    }
                                } else {
                                    if (!empty($row->transaction->type)) {
                                        if ($row->transaction->type == 'purchase') {
                                            $details = '<b>' . __('purchase.supplier') . ':</b> ' . $row->transaction->contact->name . '<br><b>'.
                                            __('purchase.ref_no') . ':</b> ' . $row->transaction->ref_no;
                                        } elseif ($row->transaction->type == 'sell') {
                                            $details = '<b>' . __('contact.customer') . ':</b> ' . $row->transaction->contact->name . '<br><b>'.
                                            __('sale.invoice_no') . ':</b> ' . $row->transaction->invoice_no;
                                        }
                                    }
                                }
                                if( isset($row->transaction) && $row->transaction->payment_status == 'cancelled')
                                    $details = '<strike>'.$details.'</strike>';
                                return $details;
                            })
                            ->editColumn('action', function ($row) {
                                $action = '';
                                if ($row->sub_type == 'fund_transfer' || $row->sub_type == 'deposit') {
                                    $action = '<button type="button" class="btn btn-danger btn-xs delete_account_transaction" data-href="' . action('AccountController@destroyAccountTransaction', [$row->id]) . '"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</button>';
                                }
                                return $action;
                            })
                            ->removeColumn('id')
                            ->removeColumn('is_closed')
                            ->rawColumns(['credit', 'debit', 'balance', 'sub_type', 'action', 'operation_date'])
                            ->make(true);
        }
        $account = Account::where('business_id', $business_id)
                            ->find($id);
                            
        return view('service.show')
                ->with(compact('account'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $account = Account::where('business_id', $business_id)
                                ->find($id);

            $account_types = Account::accountTypes();

            return view('service.edit')
                ->with(compact('account', 'account_types'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $input = $request->only(['name', 'account_number', 'note']);

                $business_id = request()->session()->get('user.business_id');
                $account = Account::where('business_id', $business_id)
                                                    ->findOrFail($id);
                $account->name = $input['name'];
                $account->account_number = $input['account_number'];
                $account->note = $input['note'];
                $account->save();

                $output = ['success' => true,
                                'msg' => __("account.account_updated_success")
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

    /**
     * Remove the specified resource from storage.
     * @return Response
     */
    public function destroyAccountTransaction($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $account_transaction = AccountTransaction::findOrFail($id);
                
                if (in_array($account_transaction->sub_type, ['fund_transfer', 'deposit'])) {
                    //Delete transfer transaction for fund transfer
                    if (!empty($account_transaction->transfer_transaction_id)) {
                        $transfer_transaction = AccountTransaction::findOrFail($account_transaction->transfer_transaction_id);
                        $transfer_transaction->delete();
                    }
                    $account_transaction->delete();
                }

                $output = ['success' => true,
                            'msg' => __("lang_v1.deleted_success")
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

    /**
     * Closes the specified account.
     * @return Response
     */
    public function close($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            try {
                $business_id = session()->get('user.business_id');
            
                $account = Account::where('business_id', $business_id)
                                                    ->findOrFail($id);
                $account->is_closed = 1;
                $account->save();

                $output = ['success' => true,
                                    'msg' => __("account.account_closed_success")
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

    /**
     * Shows form to transfer fund.
     * @param  int $id
     * @return Response
     */
    public function getFundTransfer($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $from_account = Account::where('business_id', $business_id)
                            ->NotClosed()
                            ->find($id);

            $to_accounts = Account::where('business_id', $business_id)
                            ->where('id', '!=', $id)
                            ->NotClosed()
                            ->pluck('name', 'id');

            return view('service.transfer')
                ->with(compact('from_account', 'to_accounts'));
        }
    }

    /**
     * Transfers fund from one account to another.
     * @return Response
     */
    public function postFundTransfer(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            try {
                $business_id = session()->get('user.business_id');

                $amount = $this->commonUtil->num_uf($request->input('amount'));
                $from = $request->input('from_account');
                $to = $request->input('to_account');
                $note = $request->input('note');
                $date = new \DateTime('now');
                if (!empty($amount)) {
                    $debit_data = [
                        'amount' => $amount,
                        'account_id' => $from,
                        'type' => 'debit',
                        'sub_type' => 'fund_transfer',
                        'created_by' => session()->get('user.id'),
                        'note' => $note,
                        'transfer_account_id' => $to,
                        'operation_date' => $date->format('Y-m-d H:i:s'),
                    ];

                    DB::beginTransaction();
                    $debit = AccountTransaction::createAccountTransaction($debit_data);

                    $credit_data = [
                            'amount' => $amount,
                            'account_id' => $to,
                            'type' => 'credit',
                            'sub_type' => 'fund_transfer',
                            'created_by' => session()->get('user.id'),
                            'note' => $note,
                            'transfer_account_id' => $from,
                            'transfer_transaction_id' => $debit->id,
                            'operation_date' => $date->format('Y-m-d H:i:s'),
                        ];

                    $credit = AccountTransaction::createAccountTransaction($credit_data);

                    $debit->transfer_transaction_id = $credit->id;
                    $debit->save();
                    DB::commit();
                }
                
                $output = ['success' => true,
                                    'msg' => __("account.fund_transfered_success")
                                    ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
                $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    /**
     * Shows deposit form.
     * @param  int $id
     * @return Response
     */
    public function getDeposit($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');
            
            $account = Account::where('business_id', $business_id)
                            ->NotClosed()
                            ->find($id);

            $from_accounts = Account::where('business_id', $business_id)
                            ->where('id', '!=', $id)
                            // ->where('account_type', 'capital')
                            ->NotClosed()
                            ->pluck('name', 'id');

            return view('service.deposit')
                ->with(compact('account', 'account', 'from_accounts'));
        }
    }

    public function getWithdraw($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');
            $account = Account::where('business_id', $business_id)
                ->NotClosed()
                ->find($id);
            $user_id = session()->get('user.id');
            $contacts = Contact::where('business_id', $business_id);
            $selected_contacts = User::isSelectedContacts($user_id);

            if ($selected_contacts) {
                $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                    ->where('uca.user_id', $user_id);
            }
            $to_users = $contacts->pluck('contact_id', 'id');

//            $withdraw_mode = ['w' => 'Wallet', 'b' => 'Bank'];
            $withdraw_mode = ['b' => 'Withdraw to customer', 'gt' => 'Game Credit Transfer', 'gd' => 'Game Credit Deduction'];

            $sql = Account::where('business_id', $business_id)
                ->where('id', '!=', $id)
                ->where('is_service', 0)
                ->where('name', '!=', 'Bonus Account')
                ->NotClosed();
//            if (!(auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin')))
                $sql->where('is_safe', 0);
            $bank_accounts = $sql->pluck('name', 'id');
            $service_accounts = Account::where('business_id', $business_id)
                ->where('id', '!=', $id)
                ->where('is_service', 1)
                ->NotClosed()
                ->pluck('name', 'id');



            return view('service.withdraw')
                ->with(compact('account', 'account', 'to_users', 'withdraw_mode', 'bank_accounts', 'service_accounts'));
        }
    }


    public function getGameID(){
        $service_id = request()->get('service_id');
        $customer_id = request()->get('customer_id');
        if(GameId::where('service_id', $service_id)->where('contact_id', $customer_id)->count() > 0){
           return json_encode([ 'game_id' => GameId::where('service_id', $service_id)->where('contact_id', $customer_id)->get()->first()->cur_game_id ] );
        }
        return 0;
    }

    public function checkWithdraw(){
        $customer_id = request()->get('customer_id');
        $now = date('Y-m-d H:i:s', strtotime('now'));
        $yesterday  = date('Y-m-d H:i:s', strtotime('-1 day', strtotime('now')));
        $query = Transaction::where('type', 'sell_return')->where('contact_id', $customer_id)
            ->whereBetween(DB::raw('date(transaction_date)'), [$yesterday, $now]);
        if($query->count() > 2){
            $exceeded = 1;
        } else
            $exceeded = 0;
        $is_admin_or_super = auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin');
        return json_encode(['exceeded' => $exceeded, 'is_admin_or_super' => $is_admin_or_super]);
    }


    private function createOrGetKioskAccount($business_id, $user_id){
        if(Account::where('business_id', $business_id)->where('name', 'Safe Kiosk Account')->count() > 0){ // get id
            return Account::where('business_id', $business_id)->where('name', 'Safe Kiosk Account')->get()[0]->id;
        }
        // create
        $input['business_id'] = $business_id;
        $input['created_by'] = $user_id;
        $input['account_type'] = 'saving_current';
        $input['is_service'] = 1;
        $input['name'] = 'Safe Kiosk Account';

        $account = Account::create($input);
        return $account->id;
    }
    /**
     * Deposits amount.
     * @param  Request $request
     * @return json
     */
    public function postDeposit(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');
            $user_id = $request->session()->get('user.id');

            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $account_id = $request->input('account_id');
            $note = $request->input('note');

//            $from_account = $request->input('from_account');
            $from_account = $this->createOrGetKioskAccount($business_id, $user_id);



            if (!empty($amount)) {
                $date = new \DateTime('now');
                $credit_data = [
                    'amount' => $amount,
                    'account_id' => $account_id,
                    'type' => 'credit',
                    'sub_type' => 'deposit',
                    'operation_date' => $date->format('Y-m-d H:i:s'),
                    'created_by' => session()->get('user.id'),
                    'note' => $note
                ];
                $credit = AccountTransaction::createAccountTransaction($credit_data);

                $debit_data = $credit_data;
                $debit_data['type'] = 'debit';
                $debit_data['account_id'] = $from_account;
                $debit_data['transfer_transaction_id'] = $credit->id;

                $debit = AccountTransaction::createAccountTransaction($debit_data);

                $credit->transfer_transaction_id = $debit->id;

                $credit->save();
            }
            
            $output = ['success' => true,
                                'msg' => __("account.deposited_successfully")
                                ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        
            $output = ['success' => false,
                        'msg' => __("messages.something_went_wrong")
                    ];
        }

        return $output;
    }


    /**
     * Withdraws amount.
     * @param  Request $request
     * @return json
     */
    public function postWithdraw(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = session()->get('user.business_id');
            $is_request = $request->input('is_request');
            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $account_id = $request->input('account_id');
            $contact_id = $request->input('withdraw_to');
            $note = $request->input('note');
            if($is_request == 0){
                if (!empty($amount)) {
                    $user_id = $request->session()->get('user.id');
                    $request->validate([
                        'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
                    ]);

                    $withdraw_mode = request()->get('withdraw_mode');

                    if($withdraw_mode == 'b' ){
                        $bank_account_id = $request->input('bank_account_id');
                        $accounts = Account::leftjoin('account_transactions as AT', function ($join) {
                            $join->on('AT.account_id', '=', 'accounts.id');
                            $join->whereNull('AT.deleted_at');
                        })
                            ->leftjoin('currencies', 'currencies.id', 'accounts.currency_id')
                            ->where('accounts.id', $bank_account_id)
                            ->select([DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")])
                            ->groupBy('accounts.id');
                        if($accounts->get()->first()->balance < $amount){
                            $output = ['success' => false,
                                'msg' => 'Insufficient Bank Balance, please top up bank credit!'
                            ];
                            return $output;
                        }
                    }

                    $business_locations = BusinessLocation::forDropdown($business_id, false, true);
                    $business_locations = $business_locations['locations'];
                    $input = [];
                    if (count($business_locations) >= 1) {
                        foreach ($business_locations as $id => $name) {
                            $input['location_id'] = $id;
                        }
                    }
                    $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                    $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
                    $input['contact_id'] = $contact_id;
                    $input['ref_no'] = 0;
                    $now = new \DateTime('now');
                    $input['transaction_date'] = $now->format('Y-m-d H:i:s');
                    $input['discount_type'] = 'percentage';
                    $input['discount_amount'] = 0;
                    $input['final_total'] = $amount;
                    $input['additional_notes'] = $note;
                    $invoice_total = ['total_before_tax' => $amount, 'tax' => 0];
    //                $transaction = $this->transactionUtil->createSellReturnTransaction($business_id, $input, $invoice_total, $user_id);
                    //upload document
                    $document_name = $this->transactionUtil->uploadFile($request, 'document', 'service_documents');
                    if (!empty($document_name)) {
                        $input['document'] = $document_name;
                    }
                    if($withdraw_mode == 'b')
                        $sub_type = 'withdraw_to_customer';
                    else if($withdraw_mode == 'gt')
                        $sub_type = 'game_credit_transfer';
                    else
                        $sub_type = 'game_credit_deduct';
                    $transaction = $this->transactionUtil->createSellReturnTransaction($business_id, $input, $invoice_total, $user_id, $sub_type);
                    ActivityLogger::activity("Created transaction, ticket # ".$transaction->invoice_no);
                    $this->transactionUtil->createWithDrawPaymentLine($transaction, $user_id, $account_id, 1, 'credit');
                    $this->transactionUtil->updateCustomerRewardPoints($contact_id, $amount, 0, 0);

                    $credit_data = [
                        'amount' => $amount,
                        'account_id' => $account_id,
                        'type' => 'credit',
                        'sub_type' => 'withdraw',
                        'operation_date' => $now->format('Y-m-d H:i:s'),
                        'created_by' => session()->get('user.id'),
                        'transaction_id' => $transaction->id
                    ];

                    AccountTransaction::createAccountTransaction($credit_data);
                    if($withdraw_mode == 'b' || $withdraw_mode == 'gt') { // bank mode
                        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
                        $business_locations = $business_locations['locations'];
                        $input = [];
                        if (count($business_locations) >= 1) {
                            foreach ($business_locations as $id => $name) {
                                $input['location_id'] = $id;
                            }
                        }
                        $bank_account_id = $withdraw_mode == 'b' ? $request->input('bank_account_id') : $request->input('service_id');

                        $contact_id = $request->input('withdraw_to');
                        $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                        $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
                        $input['contact_id'] = $contact_id;
                        $input['ref_no'] = 0;
                        $date = new \DateTime('now');
                        $input['transaction_date'] = $date->format('Y-m-d H:i:s');
                        $input['discount_type'] = 'percentage';
                        $input['discount_amount'] = 0;
                        $input['final_total'] = $amount;
                        if($withdraw_mode == 'gt'){
                            $input['commission_agent'] = null;
                            $input['status'] = 'final';
                        }
                        $input['additional_notes'] = $request->input('note');
                        $invoice_total = ['total_before_tax' => $amount, 'tax' => 0];
    //                    if($withdraw_mode == 's')
    //                        $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);
    //                    else
    //                        $transaction = $this->transactionUtil->createSellReturnTransaction($business_id, $input, $invoice_total, $user_id);
                        $is_service = $withdraw_mode == 'b' ? 0 : 1;
                        $this->transactionUtil->createWithDrawPaymentLine($transaction, $user_id, $bank_account_id, $is_service, 'debit');
                        $this->transactionUtil->updateCustomerRewardPoints($contact_id, 0, 0, $amount);

    //                    $debit_data = [
    //                        'amount' => $amount,
    //                        'account_id' => $bank_account_id,
    //                        'type' => 'debit',
    //                        'sub_type' => 'withdraw',
    //                        'operation_date' => $now->format('Y-m-d H:i:s'),
    //                        'created_by' => session()->get('user.id')
    //                    ];
    //
    //                    AccountTransaction::createAccountTransaction($debit_data);
                        if(!$is_service){
                            $account = Account::where('business_id', $business_id)
                                ->findOrFail($bank_account_id);
                            $amount += $account->service_charge;
                        }
                        $credit_data = [
                            'amount' => $amount,
                            'account_id' => $bank_account_id,
                            'type' => 'debit',
                            'sub_type' => 'withdraw',
                            'created_by' => session()->get('user.id'),
                            'note' => $request->input('note'),
                            'transfer_account_id' => $account_id,
                            'transfer_transaction_id' => $transaction->id,
                            'operation_date' => $now->format('Y-m-d H:i:s'),
                            'transaction_id' => $transaction->id,
                            'shift_closed_at' => Account::find($bank_account_id)->shift_closed_at
                        ];

                        $credit = AccountTransaction::createAccountTransaction($credit_data);
                    }

    //                $credit_data = [
    //                    'amount' => $amount,
    //                    'account_id' => $account_id,
    //                    'type' => 'credit',
    //                    'sub_type' => 'deposit',
    //                    'operation_date' => $this->commonUtil->uf_date($request->input('operation_date'), true),
    //                    'created_by' => session()->get('user.id'),
    //                    'note' => $note
    //                ];
    //                $credit = AccountTransaction::createAccountTransaction($credit_data);
    //
    //                $debit_data = $credit_data;
    //                $debit_data['type'] = 'debit';
    //                $debit_data['account_id'] = $from_account;
    //                $debit_data['transfer_transaction_id'] = $credit->id;
    //
    //                $debit = AccountTransaction::createAccountTransaction($debit_data);
    //
    //                $credit->transfer_transaction_id = $debit->id;
    //
    //                $credit->save();
                }

                $output = ['success' => true,
                    'msg' => __("account.withdrawn_successfully")
                ];
            }
            else if(request()->get('withdraw_mode') == 'b'){
                $input['business_id'] = $business_id;
                $input['essentials_request_type_id'] = 3;
                $input['user_id'] = request()->session()->get('user.id');
                $input['status'] = 'pending';
                $input['start_date'] = $input['end_date'] = date('Y-m-d', strtotime('now'));
                $bank_account_id = $request->input('bank_account_id');
                $reason = "<b>Amount:</b> ".$amount;
                $reason .= "<br/>\n<b>Withdraw to:</b> ". Contact::find($contact_id)->contact_id;
                $reason .= "<br/>\n<b>Via Account:</b> ". Account::find($bank_account_id)->name;
                $reason .= "<br/>\n<b>Note:</b> ".$note;
                $input['reason'] = $reason;

                //Update reference count
                $ref_count = $this->moduleUtil->setAndGetReferenceCount('leave');
                //Generate reference number

                if (empty($input['ref_no'])) {
                    $settings = request()->session()->get('business.essentials_settings');
                    $settings = !empty($settings) ? json_decode($settings, true) : [];
                    $input['ref_no'] = $this->moduleUtil->generateReferenceNumber('leave', $ref_count, null, 'req');
                }
                ActivityLogger::activity("Created request, reference no ".$input['ref_no']);

                $leave = EssentialsRequest::create($input);
                $admins = $this->moduleUtil->get_admins($business_id);

                \Notification::send($admins, new NewRequestNotification($leave));
                $output = ['success' => true,
                    'msg' => __("account.requested_successfully")
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false,
                'msg' => __("messages.something_went_wrong")
            ];
        }

        return $output;
    }

    /**
     * Calculates account current balance.
     * @param  int $id
     * @return json
     */
    public function getAccountBalance($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = session()->get('user.business_id');
        $account = Account::leftjoin(
            'account_transactions as AT',
            'AT.account_id',
            '=',
            'accounts.id'
        )
            ->leftjoin( 'transactions as T',
                'transaction_id',
                '=',
                'T.id')
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id)
            ->where(function ($q) {
                $q->where('T.payment_status', '!=', 'cancelled');
                $q->orWhere('T.payment_status', '=', null);
            })
            ->select('accounts.*', DB::raw("SUM( IF(AT.type='credit', amount, -1 * amount) ) as balance"))
            ->first();

        return $account;
    }

    /**
     * Show the specified resource.
     * @return Response
     */
    public function cashFlow()
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $accounts = AccountTransaction::join(
                    'accounts as A',
                    'account_transactions.account_id',
                    '=',
                    'A.id'
                )
                ->leftjoin('transaction_payments as TP', 'account_transactions.transaction_payment_id', 
                    '=',
                    'TP.id'
                )
                ->where('A.business_id', $business_id)
                ->with(['transaction', 'transaction.contact', 'transfer_transaction'])
                ->select(['type', 'account_transactions.amount', 'operation_date',
                    'sub_type', 'transfer_transaction_id',
                    DB::raw('(SELECT SUM(IF(AT.type="credit", AT.amount, -1 * AT.amount)) from account_transactions as AT WHERE AT.operation_date <= account_transactions.operation_date AND AT.deleted_at IS NULL) as balance'),
                    'account_transactions.transaction_id',
                    'account_transactions.id',
                    'A.name as account_name',
                    'TP.payment_ref_no as payment_ref_no'
                    ])
                 ->groupBy('account_transactions.id')
                 ->orderBy('account_transactions.operation_date', 'desc');
            if (!empty(request()->input('type'))) {
                $accounts->where('type', request()->input('type'));
            }

            if (!empty(request()->input('account_id'))) {
                $accounts->where('A.id', request()->input('account_id'));
            }

            $start_date = request()->input('start_date');
            $end_date = request()->input('end_date');
            
            if (!empty($start_date) && !empty($end_date)) {
                $accounts->whereBetween(DB::raw('date(operation_date)'), [$start_date, $end_date]);
            }

            return DataTables::of($accounts)
                ->addColumn('debit', function ($row) {
                    if ($row->type == 'debit') {
                        return '<span class="display_currency" data-currency_symbol="true">' . $row->amount . '</span>';
                    }
                    return '';
                })
                ->addColumn('credit', function ($row) {
                    if ($row->type == 'credit') {
                        return '<span class="display_currency" data-currency_symbol="true">' . $row->amount . '</span>';
                    }
                    return '';
                })
                ->editColumn('balance', function ($row) {
                    return '<span class="display_currency" data-currency_symbol="true">' . $row->balance . '</span>';
                })
                ->editColumn('operation_date', function ($row) {
                    return $this->commonUtil->format_date($row->operation_date, true);
                })
                ->editColumn('sub_type', function ($row) {
                    $details = '';
                    if (!empty($row->sub_type)) {
                        $details = __('account.' . $row->sub_type);
                        if (in_array($row->sub_type, ['fund_transfer', 'deposit']) && !empty($row->transfer_transaction)) {
                            if ($row->type == 'credit') {
                                $details .= ' ( ' . __('account.from') .': ' . $row->transfer_transaction->account->name . ')';
                            } else {
                                $details .= ' ( ' . __('account.to') .': ' . $row->transfer_transaction->account->name . ')';
                            }
                        }
                    } else {
                        if (!empty($row->transaction->type)) {
                            if ($row->transaction->type == 'purchase') {
                                $details = '<b>' . __('purchase.supplier') . ':</b> ' . $row->transaction->contact->name . '<br><b>'.
                                __('purchase.ref_no') . ':</b> ' . $row->transaction->ref_no;
                            } elseif ($row->transaction->type == 'sell') {
                                $details = '<b>' . __('contact.customer') . ':</b> ' . $row->transaction->contact->name . '<br><b>'.
                                __('sale.invoice_no') . ':</b> ' . $row->transaction->invoice_no;
                            }
                        }
                    }

                    if(!empty($row->payment_ref_no)){
                        if(!empty($details)){
                            $details .= '<br/>';
                        }

                        $details .= '<b>' . __('lang_v1.pay_reference_no') . ':</b> ' . $row->payment_ref_no;
                    }

                    return $details;
                })
                ->removeColumn('id')
                ->rawColumns(['credit', 'debit', 'balance', 'sub_type'])
                ->make(true);
        }
        $accounts = Account::forDropdown($business_id, false);

        $accounts->prepend(__('messages.all'), '');
                            
        return view('service.cash_flow')
                 ->with(compact('accounts'));
    }
}
