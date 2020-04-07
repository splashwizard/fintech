<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;
    
    protected $guarded = ['id'];

    public static function forDropdown($business_id, $prepend_none, $closed = false, $is_safe = false, $prepend_all = true)
    {
        $query = Account::where('business_id', $business_id)
                            ->NotCapital();

        if (!$closed) {
            $query->where('is_closed', 0);
        }
        if($is_safe) {
            $query->where('is_service', 0);
            $query->where('is_safe', 0);
            $query->where('name', '!=', 'Bonus Account');
        }

        $dropdown = $query->pluck('name', 'id');
        if ($prepend_none) {
            $dropdown->prepend(__('lang_v1.none'), '');
        }
        //Prepend all
        if ($prepend_all) {
            $dropdown = $dropdown->prepend(__('lang_v1.all'), '-1');
        }

        return $dropdown;
    }

    /**
     * Scope a query to only include not closed accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotClosed($query)
    {
        return $query->where('is_closed', 0);
    }

    /**
     * Scope a query to only include non capital accounts.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotCapital($query)
    {
        return $query->where(function ($q) {
            $q->where('account_type', '!=', 'capital');
            $q->orWhereNull('account_type');
        });
    }

    public static function accountTypes()
    {
        return [
            '' => __('account.not_applicable'),
            'saving_current' => __('account.saving_current'),
            'capital' => __('account.capital')
        ];
    }
}
