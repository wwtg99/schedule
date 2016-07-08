<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 11:52
 */

namespace Schedule\Common;


interface IJob
{

    /**
     * Function to register the job.
     * Return false if won't be registered.
     *
     * @return mixed
     */
    public function register();

    /**
     * Function to run the job, return status code, 0 for success.
     *
     * @return int
     */
    public function run();

    /**
     * @param array $config
     * @return IJob
     */
    public function init($config);

    /**
     * @return string
     */
    public function getTime();

    /**
     * @return int
     */
    public function getLastTime();

    /**
     * @param $time
     * @return mixed
     */
    public function setLastTime($time);

    /**
     * @return int
     */
    public function getNextTime();

    /**
     * @param $time
     * @return mixed
     */
    public function setNextTime($time);

    /**
     * @return bool
     */
    public function shouldRun();
}