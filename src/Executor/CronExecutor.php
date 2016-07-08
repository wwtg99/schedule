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
        $cont = $cron_time . ' php ' . $this->config['runner'] . ' --run';
        file_put_contents($f, $cont);
        exec("crontab $f");
//        echo "crontab $f\n";
        return 'success';
    }

    /**
     * Warning remove all crontab of user
     *
     * @return mixed
     */
    public function unregister()
    {
        parent::unregister();
        exec("crontab -r");
//        echo "crontab -r\n";
        return 'success';
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
                if ($job->shouldRun()) {
                    $re = $job->run();
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
        $round = isset($this->config['min_round']) ? $this->config['min_round'] : 'second';
        switch ($round) {
            case 'year': return '0 0 0 1 1 ? */1';
            case 'month': return '0 0 0 1 */1 ? *';
            case 'week': return '0 0 0 ? * 1 *';
            case 'day': return '0 0 0 */1 * ? *';
            case 'hour': return '0 0 */1 * * ? *';
            case 'minute': return '0 */1 * * * ? *';
            case 'second':
            default: return '*/1 * * * * ? *';
        }
    }

}