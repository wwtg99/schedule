<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 17:03
 */

date_default_timezone_set('Asia/Shanghai');
require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';
$loader = new \ClassLoader\Loader(__DIR__ . DIRECTORY_SEPARATOR . '..', [['Schedule', 'src', true]]);
$loader->autoload();

$job_config = 'jobs.json';
$cache_file = 'jobs.cache';

$opt = getopt('hv', ['jobs::', 'cache::', 'version', 'help', 'register', 'unregister', 'run', 'list', 'add-job::', 'remove-job::']);

function showVersion()
{
    $version = 'Scheduler version 0.1.0';
    echo $version . "\n";
}

function showHelp()
{
    showVersion();
    echo "\n";
    $help = "--register    register all jobs loaded and start schedule\n--unregister    remove schedule\n--run run schedule once\n--list    list jobs\n--add-job=name;type;time;config    add job, config should be json format\n--remove-job=name    remove job\n--jobs=config_file    jobs json config file path(default jobs.json)\n--cache=cache_file    cache file path(default jobs.cache)\n-v  --version    show version\n-h  --help    show help";
    echo $help . "\n";
}

/**
 * @param \Schedule\Common\IExecutor $executor
 */
function register($executor)
{
    $re = $executor->register();
    echo "Scheduler register $re";
}

/**
 * @param \Schedule\Common\IExecutor $executor
 */
function unregister($executor)
{
    $re = $executor->unregister();
    echo "Scheduler unregister $re";
}

/**
 * @param \Schedule\Common\IExecutor $executor
 */
function listJobs($executor)
{
    $jobs = $executor->listJobs();
    foreach ($jobs as $name => $reg) {
        $r = $reg ? 'registered' : 'unregistered';
        echo "Job  $name    $r\n";
    }
}

/**
 * @param \Schedule\Common\IExecutor $executor
 * @param string $job
 */
function addJob($executor, $job)
{
    if (is_array($job)) {
        foreach ($job as $j) {
            addJob($executor, $j);
        }
    } else {
        $js = explode(';', $job);
        $name = $js[0];
        $type = $js[1];
        $time = $js[2];
        $conf = isset($js[3]) ? json_decode($js[3], true) : [];
        $re = $executor->addJob($name, $type, $time, $conf);
        if ($re) {
            echo "Add Job $name success\n";
        }
    }
}

/**
 * @param \Schedule\Common\IExecutor $executor
 * @param string $name
 */
function removeJob($executor, $name)
{
    if (is_array($name)) {
        foreach ($name as $n) {
            $re = $executor->removeJob($n);
            if ($re) {
                echo "Remove job $n";
            }
        }
    } else {
        $re = $executor->removeJob($name);
        if ($re) {
            echo "Remove job $name";
        }
    }
}

/**
 * @param \Schedule\Common\IExecutor $executor
 * @return int
 */
function run($executor)
{
    $re = $executor->execute();
    return $re;
}

$code = 0;
try {
    if (isset($opt['v']) || isset($opt['version'])) {
        showVersion();
        exit(0);
    } elseif (isset($opt['h']) || isset($opt['help'])) {
        showVersion();
        showHelp();
        exit(0);
    } elseif (isset($opt['jobs'])) {
        $job_config = $opt['jobs'];
    } elseif (isset($opt['cache'])) {
        $cache_file = $opt['cache'];
    }
    $executor = new \Schedule\Executor\CronExecutor();
    $config = file_get_contents($job_config);
    $config = json_decode($config, true);
    $config['cache_file'] = $cache_file;
    $config['runner'] = realpath(__FILE__);
    $executor->init($config);
    if (isset($opt['register'])) {
        register($executor);
    } elseif (isset($opt['unregister'])) {
        unregister($executor);
    } elseif (isset($opt['list'])) {
        listJobs($executor);
    } elseif (isset($opt['add-job'])) {
        addJob($executor, $opt['add-job']);
        register($executor);
    } elseif (isset($opt['remove-job'])) {
        removeJob($executor, $opt['remove-job']);
    } elseif (isset($opt['run'])) {
        $code = run($executor);
    } else {
        showHelp();
    }
} catch (Exception $e) {
    showHelp();
    $code = 1;
}
exit($code);
