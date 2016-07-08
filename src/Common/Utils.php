<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/8
 * Time: 9:28
 */

namespace Schedule\Common;


use Carbon\Carbon;

class Utils
{

    /**
     * @param string $interval
     * @param int $lastTime
     * @return int
     */
    public static function calNextTime($interval, $lastTime)
    {
        if (!$lastTime || $lastTime < 0) {
            return time();
        }
        if (substr($interval, 0, 1) == 'I') {
            return self::calInterval($interval, $lastTime);
        } else {
            return self::calCronTime($interval, $lastTime);
        }
    }

    /**
     * @param string $interval
     * @return array
     */
    public static function parseInterval($interval)
    {
        if (substr($interval, 0, 1) == 'I') {
            $interval = substr($interval, 1);
        }
        $invs = [];
        $stack = [];
        foreach (str_split($interval) as $c) {
            if (preg_match('/^\d$/', $c) == 1) {
                array_push($stack, $c);
            } elseif (preg_match('/^[ymdhis]$/', $c) == 1) {
                if ($stack) {
                    $val = implode('', $stack);
                    $stack = [];
                    $invs[$c] = intval($val);
                }
            }
        }
        return $invs;
    }

    /**
     * @param string $interval
     * @param int $lastTime
     * @return int
     */
    private static function calInterval($interval, $lastTime)
    {
        $last = Carbon::createFromTimestamp($lastTime);
        $invs = self::parseInterval($interval);
        if (isset($invs['y'])) {
            $last = $last->addYears($invs['y']);
        }
        if (isset($invs['m'])) {
            $last = $last->addMonths($invs['m']);
        }
        if (isset($invs['d'])) {
            $last = $last->addDays($invs['d']);
        }
        if (isset($invs['h'])) {
            $last = $last->addHours($invs['h']);
        }
        if (isset($invs['i'])) {
            $last = $last->addMinutes($invs['i']);
        }
        if (isset($invs['s'])) {
            $last = $last->addSeconds($invs['s']);
        }
        return $last->getTimestamp();
    }

    /**
     * @param string $interval
     * @param int $lastTime
     * @return int
     */
    private static function calCronTime($interval, $lastTime)
    {
        $timecol = preg_split('/\s+/', $interval);
        if (count($timecol) < 7) {
            echo 'invalid time ' . $interval;
            return false;
        }
        $sec = $timecol[0];
        $min = $timecol[1];
        $hour = $timecol[2];
        $dom = $timecol[3];
        $mon = $timecol[4];
        $dow = $timecol[5];
        $year = $timecol[6];
        $date = Carbon::createFromTimestamp($lastTime);
        return false;//TODO
    }
}