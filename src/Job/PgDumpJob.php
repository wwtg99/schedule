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
     * Function to register the job.
     * Return false if won't be registered.
     *
     * @return mixed
     */
    public function register()
    {
        $cmd = ['pg_dump'];
        if (!isset($this->config['database'])) {
            echo 'no database provided';
            return false;
        }
        $db = $this->config['database'];
        if (isset($db['host']) && $db['host']) {
            array_push($cmd, '-h');
            array_push($cmd, trim($db['host']));
        }
        if (isset($db['dbname']) && $db['dbname']) {
            array_push($cmd, '-d');
            array_push($cmd, trim($db['dbname']));
        } else {
            echo 'no dbname provided in database';
            return false;
        }
        if (isset($db['port']) && $db['port']) {
            array_push($cmd, '-p');
            array_push($cmd, trim($db['port']));
        }
        if (isset($db['username']) && $db['username']) {
            array_push($cmd, '-U');
            array_push($cmd, trim($db['username']));
        }
        if (isset($db['file_format']) && $db['file_format']) {
            $file_format = trim($db['file_format']);
        } else {
            $file_format = '%Y-%m-%d-%H-%M-%S';
        }
        if (isset($db['out']) && $db['out']) {
            array_push($cmd, '-f');
            array_push($cmd, rtrim(trim($db['out']), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . strftime($file_format));
        } else {
            echo 'no out directory provided in database';
            return false;
        }
        if (isset($db['file_type']) && $db['file_type']) {
            $file_type = trim($db['file_type']);
        } else {
            $file_type = 'd';
        }
        array_push($cmd, "-F$file_type");
        if (isset($db['jobs']) && $db['jobs']) {
            array_push($cmd, '-j');
            array_push($cmd, trim($db['jobs']));
        }
        if (isset($db['params']) && $db['params']) {
            array_push($cmd, trim($db['params']));
        }
        $this->cmd = implode(' ', $cmd);
        return true;
    }

}