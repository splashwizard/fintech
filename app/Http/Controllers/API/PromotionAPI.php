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
                ->select('promotion_id', 'lang_id', 'promotion_langs.lang','title', 'desktop_image', 'content', 'start_time', 'end_time', 'sequence', 'show')
                ->orderBy('promotion_id', 'ASC')->get();

        $output = ['success' => true,
            'data' => $data
        ];
        return $output;
    }

}
