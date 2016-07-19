<?php
/**
 * Created by PhpStorm.
 * User: wwt
 * Date: 2016/7/10 0010
 * Time: 下午 1:43
 */

namespace Schedule\Job;


class PgDumpJob extends CmdJob
{

    /**
     * @var array
     */
    protected $cmd_arr = [];

    /**
     * Function to register the job.
     * Return false if won't be registered.
     *
     * @return mixed
     */
    public function register()
    {
        if (!isset($this->config['database'])) {
            echo 'no database provided';
            return false;
        }
        $db = $this->config['database'];
        if (isset($db['host']) && $db['host']) {
            $this->cmd_arr['host'] = trim($db['host']);
        }
        if (isset($db['dbname']) && $db['dbname']) {
            $this->cmd_arr['dbname'] = trim($db['dbname']);
        } else {
            echo 'no dbname provided in database';
            return false;
        }
        if (isset($db['port']) && $db['port']) {
            $this->cmd_arr['port'] = trim($db['port']);
        }
        if (isset($db['username']) && $db['username']) {
            $this->cmd_arr['username'] = trim($db['username']);
        }
        if (isset($db['file_format']) && $db['file_format']) {
            $file_format = trim($db['file_format']);
        } else {
            $file_format = '%Y-%m-%d-%H-%M-%S';
        }
        $this->cmd_arr['file_format'] = $file_format;
        if (isset($db['out']) && $db['out']) {
            $this->cmd_arr['out'] = rtrim(trim($db['out']), DIRECTORY_SEPARATOR);
        } else {
            echo 'no out directory provided in database';
            return false;
        }
        if (isset($db['file_type']) && $db['file_type']) {
            $file_type = trim($db['file_type']);
        } else {
            $file_type = 'd';
        }
        $this->cmd_arr['file_type'] = $file_type;
        if (isset($db['jobs']) && $db['jobs']) {
            $this->cmd_arr['jobs'] = trim($db['jobs']);
        }
        if (isset($db['params']) && $db['params']) {
            $this->cmd_arr['params'] =  trim($db['params']);
        }
        $this->cmd = $this->createCmd();
        return true;
    }

    /**
     * Function to run the job, return status code, 0 for success.
     *
     * @return int
     */
    public function run()
    {
        $re = parent::run();
        if ($re === 0) {
            $this->cmd = $this->createCmd();
        }
    }

    /**
     * @return string
     */
    private function createCmd()
    {
        $cmd = ['pg_dump'];
        if (isset($this->cmd_arr['host'])) {
            array_push($cmd, '-h');
            array_push($cmd, $this->cmd_arr['host']);
        }
        if (isset($this->cmd_arr['dbname'])) {
            array_push($cmd, '-d');
            array_push($cmd, $this->cmd_arr['dbname']);
        }
        if (isset($this->cmd_arr['port'])) {
            array_push($cmd, '-p');
            array_push($cmd, $this->cmd_arr['port']);
        }
        if (isset($this->cmd_arr['username'])) {
            array_push($cmd, '-U');
            array_push($cmd, $this->cmd_arr['username']);
        }
        if (isset($this->cmd_arr['out'])) {
            $file_format = $this->cmd_arr['file_format'];
            array_push($cmd, '-f');
            array_push($cmd, $this->cmd_arr['out'] . DIRECTORY_SEPARATOR . strftime($file_format));
        }
        $file_type = $this->cmd_arr['file_type'];
        array_push($cmd, "-F$file_type");
        if (isset($this->cmd_arr['jobs'])) {
            array_push($cmd, '-j');
            array_push($cmd, $this->cmd_arr['jobs']);
        }
        if (isset($this->cmd_arr['params'])) {
            array_push($cmd, $this->cmd_arr['params']);
        }
        return implode(' ', $cmd);
    }

}