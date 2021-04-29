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
use DB;



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
        $business_id = $request->get("business_id");
        $data = Promotion::join('promotion_langs', 'promotion_langs.id', 'promotions.lang_id')
                ->leftjoin('promotion_collections', 'promotions.collection_id', 'promotion_collections.id')
                ->leftjoin('connected_kiosks', 'connected_kiosks.id', 'promotions.connected_kiosk_id')
                ->where('business_id', $business_id)
//                ->where('lang_id', 1)
//                ->select('promotion_id AS id', 'type', 'title', 'desktop_image', 'promotions.connected_kiosk_id',
//                 'content AS description','start_time', 'end_time', 'sequence', 'show', 'sale', 'new', 'promotion_collections.name AS collection')
            ->select('content AS description','promotions.*', 'promotion_id AS id', 'connected_kiosks.name AS brand', 'promotion_collections.name AS collection')
                ->orderBy('promotion_id', 'ASC')->get();

        $formatted_data = [];
        foreach($data as $row){
            $row['collection'] = [$row['collection']];
            $row['images'] = [[
                'src' => strpos($row['desktop_image'], "http://") === false ? env('AWS_IMG_URL').$row['desktop_image'] : $row['desktop_image']
            ]];
            $formatted_data[] = $row;
        }
        $output = ['success' => true, 'data' => $formatted_data];
        return $output;
    }

}
