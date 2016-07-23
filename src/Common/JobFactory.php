<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 14:44
 */

namespace Wwtg99\Schedule\Common;


use Wwtg99\Schedule\Job\CmdJob;
use Wwtg99\Schedule\Job\PgDumpJob;

class JobFactory
{

    /**
     * @param string $name
     * @param array $config
     * @return IJob|null
     */
    public static function getJob($name, $config)
    {
        if ($name == 'cmd') {
            $job = new CmdJob();
        } elseif ($name == 'pg_dump') {
            $job = new PgDumpJob();
        } else {
            try {
                $rf = new \ReflectionClass($name);
                $job = $rf->newInstance();
            } catch (\Exception $e) {
                return null;
            }
        }
        if (isset($job) && $job instanceof IJob) {
            $job->init($config);
            return $job;
        }
        return null;
    }
}