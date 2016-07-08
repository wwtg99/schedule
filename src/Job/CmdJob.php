<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 14:26
 */

namespace Schedule\Job;


use Schedule\Common\BaseJob;

class CmdJob extends BaseJob
{

    /**
     * @var string
     */
    protected $cmd = '';

    /**
     * Function to register the job.
     * Return false if won't be registered.
     *
     * @return mixed
     */
    public function register()
    {
        $this->cmd = isset($this->config['cmd']) ? $this->config['cmd'] : '';
        return true;
    }

    /**
     * Function to run the job, return status code, 0 for success.
     *
     * @return int
     */
    public function run()
    {
        if ($this->cmd) {
            echo 'run ' . $this->cmd . "\n";
            exec($this->cmd);
            $this->setLastTime(time());
            $this->calNextTime();
            return 0;
        }
        return 1;
    }

}