<?php
/**
 * Created by PhpStorm.
 * User: winlans
 * Date: 2019/5/7
 * Time: 13:47
 */
namespace Charge\Util;

class TimeUtil
{
    public static function getCurrentWeekFirstDay() {
        return date('Y-m-d', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
    }

    public static function getCurrentMonthFirstDay() {
        return date('Y-m-d', strtotime(date('Y-m', time()) . '-01 00:00:00'));
    }
}