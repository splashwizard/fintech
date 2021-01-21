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
use App\Page;



class PagesAPIController extends Controller
{
    protected $commonUtil;
    protected $moduleUtil;
    public function __construct(Util $commonUtil, ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
        $this->commonUtil = $commonUtil;
    }

    public function pages(Request $request) {
        try {
            $data = Page::join('promotion_langs', 'promotion_langs.id', 'pages.lang_id')
                ->select('pages.*', 'promotion_langs.lang as lang')
                ->get();
            $pages_data = [];
            foreach($data as $row){
                $pages_data[] = [
                    'page_id' => $row['page_id'],
                    'lang_id' => $row['lang_id'],
                    'lang' => $row['lang'],
                    'title' => $row['title'],
                    'content' => $row['content']
                ];
            }
            $output = ['success' => true, 'data' => $pages_data];
            return $output;
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

            $output = ['success' => false, 'msg' => __("messages.something_fwent_wrong")
            ];
        }
        return $output;
    }

}
