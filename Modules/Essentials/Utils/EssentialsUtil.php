<?php
namespace Modules\Essentials\Utils;

use App\Utils\Util;
use DB;
use Modules\Essentials\Entities\EssentialsAttendance;

class EssentialsUtil extends Util
{
    /**
     * Function to calculate total work duration of a user for a period of time
     * @param  string $unit
     * @param  integer $user_id
     * @param  integer $business_id
     * @param  integer $start_date = null
     * @param  integer $end_date = null
     */


    public function getTotalWorkDuration(
        $unit,
        $user_id,
        $business_id,
        $start_date = null,
        $end_date = null
    ) {
        $total_work_duration = 0;
        if ($unit == 'hour') {
            $query = EssentialsAttendance::where('business_id', $business_id)
                                        ->where('user_id', $user_id)
                                        ->whereNotNull('clock_out_time');

            if (!empty($start_date) && !empty($end_date)) {
                $query->whereDate('clock_in_time', '>=', $start_date)
                            ->whereDate('clock_in_time', '<=', $end_date);
            }

            $hours = $query->select(DB::raw('SUM(TIMESTAMPDIFF(HOUR, clock_in_time, clock_out_time)) as hours'))->first();
            $total_work_duration = !empty($hours->hours) ? $hours->hours : 0;
        }

        return $total_work_duration;
    }
}
