<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Contact;
use App\Currency;
use App\CustomerGroup;
use App\DisplayGroup;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\Util;
use App\Utils\TransactionUtil;
use App\Utils\ContactUtil;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Account;
use App\AccountTransaction;
use App\TransactionPayment;

use Yajra\DataTables\Facades\DataTables;
use \jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;


use DB;

class AccountController extends Controller
{
    protected $commonUtil;
    protected $transactionUtil;
    protected $contactUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil,
                                TransactionUtil $transactionUtil,
                                ContactUtil $contactUtil,
                                BusinessUtil $businessUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->contactUtil = $contactUtil;
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
                ->leftjoin('currencies', 'currencies.id', 'accounts.currency_id')
                                ->where('is_service', 0)
                                ->where('name', '!=', 'Bonus Account')
                                ->where('business_id', $business_id)
                                ->select(['name', 'account_number', 'accounts.note', 'accounts.id', 'currencies.code as currency',
                                    'is_closed', DB::raw("SUM( IF(AT.type='credit', amount, -1*amount) ) as balance")])
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
            $is_admin_or_super = auth()->user()->hasRole('Admin#' . auth()->user()->business_id) || auth()->user()->hasRole('Superadmin') || auth()->user()->hasRole('Admin');
            if(!$is_admin_or_super){
                $accounts->where('is_safe','0');
            }

            return DataTables::of($accounts)
                            ->addColumn(
                                'action',
                                //<button data-href="{{action('AccountController@getDeposit',[$id])}}" class="btn btn-xs btn-success btn-modal" data-container=".view_modal"><i class="fa fa-money"></i> @lang("account.deposit")</button>
                                $is_admin_or_super?
                                '<button data-href="{{action(\'AccountController@edit\',[$id])}}" data-container=".account_model" class="btn btn-xs btn-primary btn-modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                                <a href="{{action(\'AccountController@show\',[$id])}}" class="btn btn-warning btn-xs"><i class="fa fa-book"></i> @lang("account.account_book")</a>
                                <button data-href="{{action(\'AccountController@getFundTransfer\',[$id])}}" class="btn btn-xs btn-info btn-modal" data-container=".view_modal"><i class="fa fa-exchange"></i> @lang("account.fund_transfer")</button>
                                <button data-url="{{action(\'AccountController@close\',[$id])}}" class="btn btn-xs btn-danger close_account"><i class="fa fa-close"></i> @lang("messages.close")</button>
                                <button data-href="{{action(\'AccountController@getWithdraw\',[$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="fa fa-money"></i> @lang("account.withdraw")</button>
                                <button data-href="{{action(\'AccountController@getExchange\',[$id])}}" class="btn btn-xs btn-warning btn-modal" data-container=".view_modal"><i class="fa fa-exchange"></i> @lang("account.exchange")</button>
                                ':'<a href="{{action(\'AccountController@show\',[$id])}}" class="btn btn-warning btn-xs"><i class="fa fa-book"></i> @lang("account.account_book")</a>
                                <button data-href="{{action(\'AccountController@getWithdraw\',[$id])}}" class="btn btn-xs btn-primary btn-modal" data-container=".view_modal"><i class="fa fa-money"></i> @lang("account.withdraw")</button>'

                            )
                            ->editColumn('name', function ($row) {
                                if ($row->is_closed == 1) {
                                    return $row->name . ' <small class="label pull-right bg-red no-print">' . __("account.closed") . '</small><span class="print_section">(' . __("account.closed") . ')</span>';
                                } else {
                                    return $row->name;
                                }
                            })
                            ->editColumn('balance', function ($row) {
                                return '<span class="display_currency">' . $row->balance . '</span>';
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

        return view('account.index')
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

        $business_id = request()->session()->get('user.business_id');
        $display_groups = DisplayGroup::forDropdown($business_id);
        $currencies = $this->businessUtil->allCurrencies();

        return view('account.create')
                ->with(compact('account_types', 'display_groups', 'currencies'));
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
                $input = $request->only(['name', 'account_number', 'note', 'display_group_id', 'currency_id']);
                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                if(empty($input['display_group_id']))
                    $input['display_group_id'] = 0;
                $input['business_id'] = $business_id;
                $input['created_by'] = $user_id;
                $input['account_type'] = 'saving_current';
               
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
                                'account_transactions.id',
                                'account_transactions.note AS note'
                                ])
                             ->groupBy('account_transactions.id')
                             ->orderBy('account_transactions.operation_date', 'desc');
            if (!empty(request()->input('type'))) {
                $accounts->where('type', request()->input('type'));
            }

            $start_date = request()->input('start_date');
//            $start_date = "2020-06-11";
            $end_date = request()->input('end_date');

            if (!empty($start_date) && !empty($end_date)) {
                $accounts->whereBetween(DB::raw('date(operation_date)'), [$start_date, $end_date]);
            }

            return DataTables::of($accounts)
                            ->addColumn('debit', function ($row) {
                                if ($row->type == 'debit') {
                                    return '<span class="display_currency">' . $row->amount . '</span>';
                                }
                                return '';
                            })
                            ->addColumn('credit', function ($row) {
                                if ($row->type == 'credit') {
                                    return '<span class="display_currency">' . $row->amount . '</span>';
                                }
                                return '';
                            })
                            ->editColumn('balance', function ($row) {
                                return '<span class="display_currency">' . $row->balance . '</span>';
                            })
                            ->editColumn('operation_date', function ($row) {
                                return $this->commonUtil->format_date($row->operation_date, true);
                            })
                            ->editColumn('sub_type', function ($row) {
                                $details = '';
                                if (!empty($row->sub_type)) {
                                    $details = __('account.' . $row->sub_type);
                                    if ($row->sub_type == 'deposit') {
                                        $details .= ' - '.$row->note;
                                    }
                                    else if($row->sub_type == 'withdraw'){
                                        $details =
//                                            '<b>' . __('contact.customer') . ':</b> ' . $row->transaction->contact->name . '<br><b>'.
                                            __('sale.invoice_no') . ':</b> ' . $row->transaction->invoice_no;
                                    }
                                    else if($row->sub_type == 'currency_exchange'){
                                        $note = AccountTransaction::find($row->id)['note'];
                                        $details = 'Currency Exchange'. '<br>'.$note;
                                    }
                                    else if($row->sub_type == 'fund_transfer'){
                                        $note = AccountTransaction::find($row->id)['note'];
                                        $details = $note;
                                    }
                                } else {
                                    if (!empty($row->transaction->type)) {
                                        if ($row->transaction->type == 'purchase') {
                                            $details = '<b>' . __('purchase.supplier') . ':</b> ' . $row->transaction->contact->name . '<br><b>'.
                                            __('purchase.ref_no') . ':</b> ' . $row->transaction->ref_no;
                                        } elseif ($row->transaction->type == 'sell') {
                                            $details = '<b>' . __('contact.customer') . ':</b> ' . $row->transaction->contact->name . '<br><b>'.
                                            __('sale.invoice_no') . ':</b> ' . $row->transaction->invoice_no;
                                        } elseif ($row->transaction->type == 'expense') {
                                            if(!empty($row->transaction->expense_for)){
                                                $user = User::find($row->transaction->expense_for);
                                                $details = '<b>' . __('sale.expense_for') . ':</b> ' . $user->first_name.' '.$user->last_name . '<br><b>'.
                                                    __('sale.reference_no') . ':</b> ' . $row->transaction->ref_no;
                                            } else $details = '';
                                        } elseif ($row->transaction->type == 'payroll') {
                                            if(!empty($row->transaction->expense_for)){
                                                $user = User::find($row->transaction->expense_for);
                                                $details = '<b>' . __('sale.payroll_for') . ':</b> ' . $user->first_name.' '.$user->last_name . '<br><b>'.
                                                    __('sale.reference_no') . ':</b> ' . $row->transaction->ref_no;
                                            } else $details = '';
                                        }
                                    }
                                }

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
                            ->rawColumns(['credit', 'debit', 'balance', 'sub_type', 'action'])
                            ->make(true);
        }
        $account = Account::where('business_id', $business_id)
                            ->find($id);
        $code = Currency::find($account->currency_id)->code;
        return view('account.show')
                ->with(compact('account', 'code'));
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

            $display_groups = DisplayGroup::forDropdown($business_id);
            $currencies = $this->businessUtil->allCurrencies();

            return view('account.edit')
                ->with(compact('account', 'account_types', 'display_groups', 'currencies'));
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
                $input = $request->only(['name', 'account_number', 'note', 'is_safe', 'service_charge', 'display_group_id', 'currency_id']);

                if(empty($input['display_group_id']))
                    $input['display_group_id'] = 0;
                $business_id = request()->session()->get('user.business_id');
                $account = Account::where('business_id', $business_id)
                                                    ->findOrFail($id);
                $account->name = $input['name'];
                $account->account_number = $input['account_number'];
                $account->is_safe = isset($input['is_safe']) ? 1 : 0;
                $account->note = $input['note'];
                $account->service_charge = $input['service_charge'];
                $account->display_group_id = $input['display_group_id'];
                $account->currency_id = $input['currency_id'];
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

            return view('account.transfer')
                ->with(compact('from_account', 'to_accounts'));
        }
    }

    public function getExchange($id)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = session()->get('user.business_id');

            $from_account = Account::join('currencies', 'currencies.id', 'accounts.currency_id')
                ->where('accounts.business_id', $business_id)
                ->where('accounts.id', $id)
                ->select('currencies.code AS code', 'accounts.id as id', 'accounts.name as name')
                ->get()[0];
//                ->NotClosed()
//                ->find($id);

            $to_accounts = Account::join('currencies', 'currencies.id', 'accounts.currency_id')
                ->where('accounts.business_id', $business_id)
                ->where('accounts.id', '!=', $id)
                ->select(DB::raw("CONCAT(accounts.name, ' (',currencies.code, ')') AS name_code"), 'accounts.id AS account_id')
                ->get()
//                ->NotClosed()
                ->pluck('name_code', 'account_id');

            return view('account.exchange')
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
                        'note' => 'Fund Transfer (To: '.Account::find($to)->name.')',
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
                            'note' => 'Fund Transfer (From: '.Account::find($from)->name.')',
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
     * Transfers fund from one account to another.
     * @return Response
     */
    public function postExchange(Request $request)
    {
        if (!auth()->user()->can('account.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = session()->get('user.business_id');

                $amount_to_send = $this->commonUtil->num_uf($request->input('amount_to_send'));
                $amount_to_receive = $this->commonUtil->num_uf($request->input('amount_to_receive'));
                $from = $request->input('from_account');
                $to = $request->input('to_account');
                $note = $request->input('note');
                $date = new \DateTime('now');
                $debit_data = [
                    'amount' => $amount_to_send,
                    'account_id' => $from,
                    'type' => 'debit',
                    'sub_type' => 'currency_exchange',
                    'created_by' => session()->get('user.id'),
                    'note' => $note,
                    'transfer_account_id' => $to,
                    'operation_date' => $date->format('Y-m-d H:i:s'),
                ];

                DB::beginTransaction();
                $debit = AccountTransaction::createAccountTransaction($debit_data);

                $credit_data = [
                    'amount' => $amount_to_receive,
                    'account_id' => $to,
                    'type' => 'credit',
                    'sub_type' => 'currency_exchange',
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
                            ->where('is_service', 0)
                            // ->where('account_type', 'capital')
                            ->NotClosed()
                            ->pluck('name', 'id');

            return view('account.deposit')
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

//            $from_accounts = Account::where('business_id', $business_id)
//                ->where('id', '!=', $id)
//                // ->where('account_type', 'capital')
//                ->NotClosed()
//                ->pluck('name', 'id');

            $user_id = session()->get('user.id');

            $contacts = Contact::where('business_id', $business_id);

            $selected_contacts = User::isSelectedContacts($user_id);
            if ($selected_contacts) {
                $contacts->join('user_contact_access AS uca', 'contacts.id', 'uca.contact_id')
                    ->where('uca.user_id', $user_id);
            }
            $to_users = $contacts->pluck('name', 'id');


            return view('account.withdraw')
                ->with(compact('account', 'account', 'to_users'));
        }
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

            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $account_id = $request->input('account_id');
            $note = $request->input('note');

            $from_account = $request->input('from_account');

            $account = Account::where('business_id', $business_id)
                            ->findOrFail($account_id);

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

            $amount = $this->commonUtil->num_uf($request->input('amount'));
            $account_id = $request->input('account_id');



//            $from_account = $request->input('from_account');
//
            $account = Account::where('business_id', $business_id)
                ->findOrFail($account_id);

            if (!empty($amount)) {
                $user_id = $request->session()->get('user.id');
                $request->validate([
                    'document' => 'file|max:'. (config('constants.document_size_limit') / 1000)
                ]);

                $business_locations = BusinessLocation::forDropdown($business_id, false, true);
                $business_locations = $business_locations['locations'];
                $input = [];
                if (count($business_locations) >= 1) {
                    foreach ($business_locations as $id => $name) {
                        $input['location_id'] = $id;
                    }
                }
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
                $input['additional_notes'] = $request->input('note');
                $invoice_total = ['total_before_tax' => $amount, 'tax' => 0];
                $document_name = $this->transactionUtil->uploadFile($request, 'document', 'account_documents');
                if (!empty($document_name)) {
                    $input['document'] = $document_name;
                }
                $transaction = $this->transactionUtil->createSellReturnTransaction($business_id, $input, $invoice_total, $user_id);
                ActivityLogger::activity("Created transaction, ticket # ".$transaction->invoice_no);
                $this->transactionUtil->createWithDrawPaymentLine($transaction, $user_id, $account_id, 0);
                $this->transactionUtil->updateCustomerRewardPoints($contact_id, 0, 0, $transaction->rp_redeemed);

                $debit_data = [
                    'amount' => $amount + $account->service_charge,
                    // 'amount' => $amount,
                    'account_id' => $account_id,
                    'type' => 'debit',
                    'sub_type' => 'withdraw',
                    'operation_date' => $date->format('Y-m-d H:i:s'),
                    'created_by' => session()->get('user.id'),
                    'transaction_id' => $transaction->id
                ];

                $debit = AccountTransaction::createAccountTransaction($debit_data);
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
            ->whereNull('AT.deleted_at')
            ->where('accounts.business_id', $business_id)
            ->where('accounts.id', $id)
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
                            
        return view('account.cash_flow')
                 ->with(compact('accounts'));
    }
}
