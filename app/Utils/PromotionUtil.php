<?php

namespace App\Utils;


use App\Promotion;

class PromotionUtil extends Util
{
    public function generatePromotionID(){
        $data = Promotion::orderBy('promotion_id', 'DESC')->get();
        if(count($data) == 0)
            return 1;
        return $data[0]->promotion_id + 1;
    }
}
