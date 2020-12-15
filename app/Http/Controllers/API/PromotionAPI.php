<?php

namespace App\Http\Controllers\API;

use App\Account;
use App\BankBrand;
use App\Contact;
use App\GameId;
use App\Http\Controllers\Controller;
use App\Promotion;
use App\Transaction;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Http\Request;
use jeremykenedy\LaravelLogger\App\Http\Traits\ActivityLogger;
use Illuminate\Support\Facades\Hash;
use Modules\Essentials\Notifications\EditCustomerNotification;
use Illuminate\Support\Str;



class PromotionAPI extends Controller
{
    protected $commonUtil;
    protected $moduleUtil;
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
    }

    public function promotions(Request $request) {
        $data = Promotion::join('promotion_langs', 'promotion_langs.id', 'promotions.lang_id')
                ->join('promotion_collections', 'promotion_collections.id', 'promotions.collection_id')
                ->where('lang_id', 1)
                ->select('promotion_id AS id', 'type', 'title', 'desktop_image',
                 'content AS description','start_time', 'end_time', 'sequence', 'show', 'promotion_collections.name AS collection')
                ->orderBy('promotion_id', 'ASC')->get();

        $formatted_data = [];
        foreach($data as $row){
            $row['collection'] = [$row['collection']];
            $row['images'] = [[
                'src' => $row['desktop_image']
            ]];
            $row['sale'] = false; 
            $row['new'] = false;
            $formatted_data[] = $row;
        }
        $output = ['success' => true, 'data' => $data];
        return $output;
    }

}
