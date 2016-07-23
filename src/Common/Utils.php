<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/8
 * Time: 9:28
 */

namespace Wwtg99\Schedule\Common;


use Carbon\Carbon;

class Utils
{

    /**
     * @param string $interval
     * @param int $lastTime
     * @return int|bool
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
     * @return int|bool
     */
    private static function calCronTime($interval, $lastTime)
    {
        $timecol = preg_split('/\s+/', $interval);
        if (count($timecol) < 5) {
            echo 'invalid time ' . $interval;
            return false;
        }
        $min = $timecol[0];
        $hour = $timecol[1];
        $date = $timecol[2];
        $mon = $timecol[3];
        $dow = $timecol[4];
        $last = Carbon::createFromTimestamp($lastTime);
        //num or * or num/step
        $minf = self::parseCronField($min);
        if ($minf === true) {
            $min = $last->minute;
        } elseif (is_array($minf)) {
            //TODO always be */n
            $min = $last->minute + $minf[1];
        } elseif (is_int($minf)) {
            $min = $minf;
        } else {
            return false;
        }
        $hourf = self::parseCronField($hour);
        if ($hourf === true) {
            $hour = $last->hour;
            if ($min < $last->minute) {
                $hour++;
            }
        } elseif (is_array($hourf)) {
            //TODO always be */n
            $hour = $last->hour + $hourf[1];
        } elseif (is_int($hourf)) {
            $hour = $hourf;
        } else {
            return false;
        }
        $datef = self::parseCronField($date);
        if ($datef === true) {
            $date = $last->day;
            if ($hour < $last->hour) {
                $date++;
            }
        } elseif (is_array($datef)) {
            //TODO always be */n
            $date = $last->day + $datef[1];
        } elseif (is_int($datef)) {
            $date = $datef;
        } else {
            return false;
        }
        if ($datef === true && isset($day_add)) {
            $date += $day_add;
        }
        $monf = self::parseCronField($mon);
        if ($monf === true) {
            $mon = $last->month;
            if ($date < $last->day) {
                $mon++;
            }
        } elseif (is_array($monf)) {
            //TODO always be */n
            $mon = $last->month + $monf[1];
        } elseif (is_int($monf)) {
            $mon = $monf;
        } else {
            return false;
        }
        $dowf = self::parseCronField($dow);
        if ($dowf === true) {
            $dow = false;
        } elseif (is_array($dowf)) {
            $dow = false;
        } elseif (is_int($dowf)) {
            $dow = $dowf;
        } else {
            return false;
        }
        $year = $last->year;
//        if ($mon < $last->month) {
//            $year++;
//        }
        if ($minf === true && $hourf === true && $datef === true && $monf === true) {
            $min += 1;
        }
        $next = Carbon::create($year, $mon, $date, $hour, $min);
        if ($dow && $next->dayOfWeek != $dow) {
            if ($next->dayOfWeek < $dow) {
                $next->addDays($dow - $next->dayOfWeek);
            } else {
                $next->addDays(7 - $next->dayOfWeek + $dow);
            }
        }
        return $next->getTimestamp();
    }

    /**
     * @param $val
     * @return array|bool|int
     */
    private static function parseCronField($val)
    {
        $val = trim($val);
        if ($val == '*') {
            return true;
        } elseif (strpos($val, '/') > 0) {
            $c = explode('/', $val);
            if (count($c) == 2) {
                if ($c[1] <= 0) {
                    return false;
                }
                return [$c[0], intval($c[1])];
            } else {
                return false;
            }
        } elseif (is_numeric($val)) {
            return intval($val);
        } else {
            return false;
        }
    }
}