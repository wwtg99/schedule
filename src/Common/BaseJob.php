<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 16:54
 */

namespace Wwtg99\Schedule\Common;


abstract class BaseJob implements IJob
{

    /**
     * @var int
     */
    protected $last_time;

    /**
     * @var int
     */
    protected $next_time;

    /**
     * @var string
     */
    protected $time;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    protected $descr = '';

    /**
     * @param array $config
     * @return IJob
     */
    public function init($config)
    {
        $this->config = $config;
        $this->time = isset($config['time']) ? $config['time'] : '';
        return $this;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return string
     */
    public function getLastTime()
    {
        return $this->last_time;
    }

    /**
     * @param $time
     * @return mixed
     */
    public function setLastTime($time)
    {
        $this->last_time = $time;
        return $this;
    }

    /**
     * @return int
     */
    public function getNextTime()
    {
        return $this->next_time;
    }

    /**
     * @param $time
     * @return mixed
     */
    public function setNextTime($time)
    {
        $this->next_time = $time;
    }

    /**
     * @return bool
     */
    public function shouldRun()
    {
        if (!$this->last_time || $this->last_time < 0) {
            return true;
        }
        return $this->next_time && $this->next_time <= time();
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->descr;
    }

    /**
     * @return IJob
     */
    protected function calNextTime()
    {
        $next = Utils::calNextTime($this->getTime(), $this->getLastTime());
        $this->setNextTime($next);
        return $this;
    }

}