<?php

namespace App\Utils;

use App\Account;
use App\AccountTransaction;
use App\Contact;
use App\CustomerGroup;

use DB;

class ContactUtil
{

    /**
     * Returns Walk In Customer for a Business
     *
     * @param int $business_id
     *
     * @return array/false
     */
    public function getWalkInCustomer($business_id)
    {
        $contact = Contact::where('type', 'customer')
                    ->where('business_id', $business_id)
                    ->where('is_default', 1)
                    ->first()
                    ->toArray();

        if (!empty($contact)) {
            return $contact;
        } else {
            return false;
        }
    }

    /**
     * Returns the customer group
     *
     * @param int $business_id
     * @param int $customer_id
     *
     * @return array
     */
    public function getCustomerGroup($business_id, $customer_id)
    {
        $cg = [];

        if (empty($customer_id)) {
            return $cg;
        }

        $contact = Contact::leftjoin('customer_groups as CG', 'contacts.customer_group_id', 'CG.id')
            ->where('contacts.id', $customer_id)
            ->where('contacts.business_id', $business_id)
            ->select('CG.*')
            ->first();

        return $contact;
    }

    public function getMainWalletBalance($business_id, $contact_id)
    {
        $balance = 0;
        $main_wallet = 'Main Wallet';
        if(Account::where('business_id', $business_id)->where('name', $main_wallet)->count() > 0){
            $wallet_id = Account::where('business_id', $business_id)->where('name', $main_wallet)->first()->id;
            $is_special_kiosk = Account::find($wallet_id)->is_special_kiosk;
            $business_id = session()->get('user.business_id');
            $shift_closed_at = Account::find($wallet_id)->shift_closed_at;
            $query = AccountTransaction::join(
                'accounts as A',
                'account_transactions.account_id',
                '=',
                'A.id'
            )
                ->leftjoin( 'transactions as T',
                    'transaction_id',
                    '=',
                    'T.id')
                ->where('A.business_id', $business_id)
                ->where('A.id', $wallet_id)
                ->where('T.contact_id', $contact_id);
            if($shift_closed_at != null)
                $query = $query->where('account_transactions.operation_date', '>=', $shift_closed_at);
            $query = $query->whereNull('account_transactions.deleted_at')
                ->where(function ($q) {
                    $q->where('T.payment_status', '!=', 'cancelled');
                    $q->orWhere('T.payment_status', '=', null);
                });

            if($is_special_kiosk)
                $query = $query->where(function ($q) {
                    $q->where('account_transactions.sub_type', '!=', 'opening_balance');
                    $q->orWhere('account_transactions.sub_type', '=', null);
                });
            $total_row = $query->select(DB::raw("SUM( IF(account_transactions.type='credit', -1 * account_transactions.amount, account_transactions.amount) )*( 1 - is_special_kiosk * 2) as balance"))
                ->first();
            $balance = $total_row->balance;
        }
        return $balance;
    }
}
