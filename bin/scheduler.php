<?php
/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 17:03
 */

date_default_timezone_set('Asia/Shanghai');

//autoload
require_once __DIR__ . DIRECTORY_SEPARATOR . '../vendor/autoload.php';
$loader = new \ClassLoader\Loader(__DIR__ . DIRECTORY_SEPARATOR . '..', [['Schedule', 'src', true]]);
$loader->autoload();

//default config file
$job_config = 'jobs.json';

function showVersion()
{
    $version = 'Scheduler version 0.1.0';
    echo $version . "\n";
}

function showHelp()
{
    showVersion();
    echo "\n";
    $help = [
        "  --register    register all jobs loaded and start schedule",
        "  --unregister    remove schedule",
        "  --run run schedule once",
        "  --list    list jobs",
        "  --add-job=name;type;time;config    add job, config should be json format",
        "  --remove-job=name    remove job",
        "  --jobs=config_file    jobs json config file path(default jobs.json)",
        "  --cache=cache_file    cache file path(default jobs.cache)",
        "  -v  --version    show version",
        "  -h  --help    show help"
    ];
    echo implode("\n", $help) . "\n";
}

/**
 * @param \Schedule\Common\IExecutor $executor
 */
function register($executor)
{
    $re = $executor->register();
    echo "Scheduler register $re\n";
}

/**
 * @param \Schedule\Common\IExecutor $executor
 */
function unregister($executor)
{
    $re = $executor->unregister();
    echo "Scheduler unregister $re\n";
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
                echo "Remove job $n\n";
            }
        }
    } else {
        $re = $executor->removeJob($name);
        if ($re) {
            echo "Remove job $name\n";
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

/**
 * @param string|null $conf config file
 * @param array $opts
 * @return array
 */
function getConfig($conf = null, $opts = [])
{
    //default config
    $config = [
        'cache_file'=>'.jobs_cache'
    ];
    if ($conf) {
        $f = file_get_contents($conf);
        $c = json_decode($f, true);
        $config = array_merge($config, $c);
    }
    if ($opts) {
        $config = array_merge($config, $opts);
    }
    $config['runner'] = realpath(__FILE__);
    return $config;
}

$code = 0;
try {
    $executor = new \Schedule\Executor\CronExecutor();
    if ($argc > 1) {
        $cmd = $argv[1];
        if ($cmd == 'register') {
            $config = getConfig($job_config);
            $executor->init($config);
            register($executor);
        } elseif ($cmd == 'unregister') {
            $config = getConfig();
            $executor->init($config);
            unregister($executor);
        } elseif ($cmd == 'list') {
            $config = getConfig();
            $executor->init($config);
            listJobs($executor);
        } elseif ($cmd == 'run') {
            $config = getConfig();
            $executor->init($config);
            run($executor);
        } elseif ($cmd == 'version') {
            showVersion();
        } else {
            $opt = getopt('hv', ['jobs:', 'cache:', 'version', 'help', 'register', 'unregister', 'run', 'list', 'add-job:', 'remove-job:']);
            $p = [];
            if (isset($opt['jobs'])) {
                $job_config = $opt['jobs'];
            } elseif (isset($opt['cache'])) {
                $p['cache_file'] = $opt['cache'];
            }
            if (isset($opt['register'])) {
                $config = getConfig($job_config, $p);
                $executor->init($config);
                register($executor);
            } elseif (isset($opt['unregister'])) {
                $config = getConfig(null, $p);
                $executor->init($config);
                unregister($executor);
            } elseif (isset($opt['list'])) {
                $config = getConfig(null, $p);
                $executor->init($config);
                listJobs($executor);
            } elseif (isset($opt['add-job'])) {
                $config = getConfig(null, $p);
                $executor->init($config);
                addJob($executor, $opt['add-job']);
                register($executor);
            } elseif (isset($opt['remove-job'])) {
                $config = getConfig(null, $p);
                $executor->init($config);
                removeJob($executor, $opt['remove-job']);
            } elseif (isset($opt['run'])) {
                $config = getConfig(null, $p);
                $executor->init($config);
                $code = run($executor);
            } elseif (isset($opt['v']) || isset($opt['version'])) {
                showVersion();
                exit(0);
            } else {
                showHelp();
                exit(0);
            }
        }
    } else {
        showHelp();
        exit(0);
    }
} catch (Exception $e) {
    showHelp();
    $code = 1;
}
exit($code);
