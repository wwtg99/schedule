<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 14:29
 */

namespace Wwtg99\Schedule\Common;


abstract class BaseExecutor implements IExecutor
{

    /**
     * @var array
     */
    protected $jobs = [];

    /**
     * @var array
     */
    protected $register_jobs = [];

    /**
     * @var string
     */
    protected $cache_file = '';

    /**
     * Script to run
     *
     * @var string
     */
    protected $runner = '';

    /**
     * @var bool
     */
    protected $verbose = false;

    /**
     * @var bool
     */
    protected $force = false;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @return mixed
     */
    public function register()
    {
        foreach ($this->jobs as $name => $job) {
            if ($job instanceof IJob) {
                $re = $job->register();
                if ($re !== false) {
                    $this->register_jobs[$name] = $job;
                    if ($this->verbose) {
                        echo "Register job $name\n";
                    }
                }
            }
        }
        $this->saveCache();
        return 0;
    }

    /**
     * @return mixed
     */
    public function unregister()
    {
        $this->register_jobs = [];
        $this->saveCache();
        if ($this->verbose) {
            echo "Unregister all jobs\n";
        }
        return 0;
    }

    /**
     * @param $config
     * @return IExecutor
     */
    public function init($config)
    {
        $this->config = $config;
        $this->runner = isset($config['runner']) ? $config['runner'] : 'scheduler.php';
        if (isset($config['cache_file'])) {
            $this->cache_file = $config['cache_file'];
        }
        if (isset($config['jobs'])) {
            foreach ($config['jobs'] as $job) {
                $name = isset($job['name']) ? $job['name'] : '';
                $type = isset($job['type']) ? $job['type'] : '';
                $inv = isset($job['time']) ? $job['time'] : '';
                if (!$name || !$type || !$inv) {
                    continue;
                }
                $this->addJob($name, $type, $inv, $job);
            }
        }
        if (isset($config['verbose']) && $config['verbose']) {
            $this->verbose = true;
        }
        if (isset($config['force']) && $config['force']) {
            $this->force = true;
        }
        return $this;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string $interval
     * @param array $job
     * @return bool
     */
    public function addJob($name, $type, $interval, $job)
    {
        if ($name && !array_key_exists($name, $this->jobs) && is_array($job)) {
            $job['time'] = $interval;
            $j = JobFactory::getJob($type, $job);
            if ($j) {
                $this->jobs[$name] = $j;
                if ($this->verbose) {
                    echo "Add job $name\n";
                }
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function removeJob($name)
    {
        if (array_key_exists($name, $this->jobs)) {
            unset($this->jobs[$name]);
        }
        if (array_key_exists($name, $this->register_jobs)) {
            unset($this->register_jobs[$name]);
        }
        $this->saveCache();
        if ($this->verbose) {
            echo "Remove job $name\n";
        }
        return true;
    }

    /**
     * @return array
     */
    public function listJobs()
    {
        $this->loadCache();
        $js = [];
        foreach ($this->register_jobs as $name => $job) {
            $nt = $job->getNextTime();
            if ($nt) {
                $nt = strftime('%Y-%m-%d %H:%M:%S', $nt);
            } else {
                $nt = 'Immediately';
            }
            $des = $job->getDescription();
            $js[$name] = [$nt, $des];
        }
        return $js;
    }

    /**
     * @return int
     */
    protected function saveCache()
    {
        if (!$this->cache_file) {
            $this->cache_file = 'jobs.cache';
        }
        $obj = [];
        foreach ($this->register_jobs as $name => $register_job) {
            $obj[$name] = serialize($register_job);
        }
        return file_put_contents($this->cache_file, json_encode($obj));
    }

    /**
     * @return bool
     */
    protected function loadCache()
    {
        if (!$this->cache_file) {
            $this->cache_file = 'jobs.cache';
        }
        if (file_exists($this->cache_file)) {
            $cont = file_get_contents($this->cache_file);
            $obj = json_decode($cont, true);
            foreach ($obj as $name => $job) {
                $this->register_jobs[$name] = unserialize($job);
            }
            return true;
        }
        return false;
    }

}