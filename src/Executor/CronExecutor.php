<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 14:25
 */

namespace Schedule\Executor;


use Schedule\Common\BaseExecutor;
use Schedule\Common\IJob;

class CronExecutor extends BaseExecutor
{
    /**
     * @return mixed
     */
    public function register()
    {
        parent::register();
        $cron_time = $this->getCronTime();
        $f = 'crontab.cache';
        $cont = $cron_time . ' php ' . $this->config['runner'] . ' --run --cache ' . realpath($this->cache_file) . "\n";
        file_put_contents($f, $cont);
        try {
            exec("crontab $f");
//        echo "crontab $f\n";
            $re = 'success';
        } catch (\Exception $e) {
            $re = 'failed';
        }
        unlink($f);
        return $re;
    }

    /**
     * Warning remove all crontab of user
     *
     * @return mixed
     */
    public function unregister()
    {
        parent::unregister();
        try {
            exec("crontab -r");
//        echo "crontab -r\n";
            $re = 'success';
        } catch (\Exception $e) {
            $re = 'failed';
        }
        return $re;
    }

    /**
     * @return int
     */
    public function execute()
    {
        $this->loadCache();
        $code = 0;
        foreach ($this->register_jobs as $name => $job) {
            if ($job instanceof IJob) {
                if ($this->force || $job->shouldRun()) {
                    if ($this->verbose) {
                        echo "Run job $name\n";
                    }
                    $re = $job->run();
                    if ($this->verbose) {
                        echo "Return code of job $name: $re\n";
                    }
                    $code |= $re;
                }
            }
        }
        $this->saveCache();
        return $code;
    }

    /**
     * @return string
     */
    private function getCronTime()
    {
        if (isset($this->config['cron_time'])) {
            return $this->config['cron_time'];
        }
        $round = isset($this->config['min_round']) ? $this->config['min_round'] : 'minute';
        switch ($round) {
            case 'month': return '0 0 1 * *';
            case 'week': return '0 0 * * 1';
            case 'day': return '0 0 * * *';
            case 'hour': return '0 * * * *';
            case 'minute':
            default: return '*/1 * * * *';
        }
    }

}