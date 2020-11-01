<?php

namespace App\Utils;


use App\Notice;

class NoticeUtil extends Util
{
    public function generateNoticeID(){
        $data = Notice::orderBy('notice_id', 'DESC')->get();
        if(count($data) == 0)
            return 1;
        return $data[0]->notice_id + 1;
    }
}
