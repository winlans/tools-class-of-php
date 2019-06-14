<?php
/**
 * Created by PhpStorm.
 * User: winlans
 * Date: 2018/8/3
 * Time: 9:47
 */
namespace App\Library\Util;

use App\Library\Traits\ServiceTrait;

class DebugUtil
{
    use ServiceTrait;
    private static $startTimes = [];

    public static function start() {
        array_push(self::$startTimes, microtime(true));
    }


    public static function stop() {
        return number_format((microtime(true) - array_pop(self::$startTimes)) * 1000, 2);
    }

    public static function timeForClosure(\Closure $func, $log = false) {
        self::start();
        $func();
        $spendTime = self::stop();
        if (false !== $log)
            self::getLogger()->info('debug for time has spend time on closure function is: ' . $spendTime);

        return $spendTime;
    }
}