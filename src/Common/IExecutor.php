<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 11:51
 */

namespace Wwtg99\Schedule\Common;


interface IExecutor
{

    /**
     * @return mixed
     */
    public function register();

    /**
     * @return mixed
     */
    public function unregister();

    /**
     * @param $config
     * @return IExecutor
     */
    public function init($config);

    /**
     * @return int
     */
    public function execute();

    /**
     * @param string $name job name
     * @param string $type job type
     * @param string $interval job execute interval
     * @param array $job job configs
     * @return bool
     */
    public function addJob($name, $type, $interval, $job);

    /**
     * @param string $name
     * @return bool
     */
    public function removeJob($name);

    /**
     * @return array
     */
    public function listJobs();
}