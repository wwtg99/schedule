<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 14:44
 */

namespace Schedule\Common;


use Schedule\Job\CmdJob;
use Schedule\Job\PgDumpJob;

class JobFactory
{

    /**
     * @param string $type
     * @param array $config
     * @return IJob|null
     */
    public static function getJob($type, $config)
    {
        switch ($type) {
            case 'cmd':
                $job = new CmdJob();
                break;
            case 'pg_dump':
                $job = new PgDumpJob();
                break;
        }
        if (isset($job) && $job instanceof IJob) {
            $job->init($config);
            return $job;
        }
        return null;
    }
}