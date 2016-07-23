<?php

/**
 * Created by PhpStorm.
 * User: wuwentao
 * Date: 2016/7/7
 * Time: 18:23
 */
class TestSchedule extends PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        date_default_timezone_set('Asia/Shanghai');
        require_once '../vendor/autoload.php';
        $loader = new \ClassLoader\Loader(__DIR__ . DIRECTORY_SEPARATOR . '..', [['Wwtg99\Schedule', 'src', true]]);
        $loader->autoload();
    }

    public function testJob()
    {
        $job_config = ['name'=>'test1', 'cmd'=>'id', 'type'=>'cmd', 'time'=>'I3s'];
        $job = \Wwtg99\Schedule\Common\JobFactory::getJob('cmd', $job_config);
        $this->assertInstanceOf('Wwtg99\Schedule\Job\CmdJob', $job);
        $this->assertTrue($job->register());
        $this->assertEquals('I3s', $job->getTime());
        $this->assertNull($job->getLastTime());
        $this->assertTrue($job->shouldRun());
        $this->assertEquals(0, $job->run());
        $this->assertNotNull($job->getLastTime());
        $this->assertFalse($job->shouldRun());
        sleep(3);
        $this->assertTrue($job->shouldRun());
    }

    public function testExecutor()
    {
        $executor = new \Wwtg99\Schedule\Executor\CronExecutor();
        $executor->init(['runner'=>__FILE__]);
        $this->assertTrue($executor->addJob('test1', 'cmd', 'I3s', ['cmd'=>'dir']));
        $this->assertTrue($executor->register() !== false);
        $this->assertEquals(['test1'=>['Immediately', 'dir']], $executor->listJobs());
        $this->assertTrue($executor->addJob('test2', 'cmd', 'I2s', ['cmd'=>'dir']));
        $this->assertEquals(['test1'=>['Immediately', 'dir']], $executor->listJobs());
        $this->assertFalse($executor->addJob('test2', 'cmd', 'I2s', ['cmd'=>'dir']));
        $this->assertTrue($executor->removeJob('test2'));
        $this->assertEquals(['test1'=>['Immediately', 'dir']], $executor->listJobs());
        $this->assertTrue($executor->removeJob('test1'));
        $this->assertEquals([], $executor->listJobs());
        $this->assertTrue($executor->addJob('test1', 'cmd', 'I3s', ['cmd'=>'id']));
        $this->assertTrue($executor->register() !== false);
        $this->assertTrue($executor->addJob('test3', 'cmd', 'I2s', ['cmd'=>'whoami']));
        $this->assertTrue($executor->unregister() !== false);
        $this->assertEquals([], $executor->listJobs());
        $this->assertTrue($executor->register() !== false);
        $this->assertEquals(['test1'=>['Immediately', 'id'], 'test3'=>['Immediately', 'whoami']], $executor->listJobs());
        echo '===run id and whoami' . "\n";
        $this->assertEquals(0, $executor->execute());
        echo '===run none' . "\n";
        $this->assertEquals(0, $executor->execute());
        sleep(2);
        echo '===run whoami' . "\n";
        $this->assertEquals(0, $executor->execute());
        sleep(1);
        echo '===run id' . "\n";
        $this->assertEquals(0, $executor->execute());
    }

    public function testUtils()
    {
        $check_inv = [
            '5s'=>['s'=>5],
            '20i'=>['i'=>20],
            '1h'=>['h'=>1],
            '3d'=>['d'=>3],
            '6m'=>['m'=>6],
            '8y'=>['y'=>8],
            '2h10i30s'=>['h'=>2, 'i'=>10, 's'=>30],
            '1y2m20d'=>['y'=>1, 'm'=>2, 'd'=>20],
            'I3d12h'=>['d'=>3, 'h'=>12]
        ];
        foreach ($check_inv as $inv => $exp) {
            $this->assertEquals($exp, \Wwtg99\Schedule\Common\Utils::parseInterval($inv), "Interval $inv");
        }
        $lastTime = \Carbon\Carbon::create(2016, 7, 9, 10, 10, 2);
        $last = $lastTime->getTimestamp();
//        $last = DateTime::createFromFormat('Y-m-d H:i:s', '2016-07-09 10:10:02')->getTimestamp();
        //check interval
        $check_next = [
            'I10s'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-07-09 10:10:12')->getTimestamp(),
            'I1h2i'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-07-09 11:12:02')->getTimestamp(),
            'I3d'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-07-12 10:10:02')->getTimestamp(),
            'I2m'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-09-09 10:10:02')->getTimestamp(),
            'I1y'=>DateTime::createFromFormat('Y-m-d H:i:s', '2017-07-09 10:10:02')->getTimestamp(),
            'I5d10h'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-07-14 20:10:02')->getTimestamp(),
            'I100s'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-07-09 10:11:42')->getTimestamp(),
            'I20h'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-07-10 06:10:02')->getTimestamp(),
        ];
        foreach ($check_next as $inv => $exp) {
            $next = \Wwtg99\Schedule\Common\Utils::calNextTime($inv, $last);
            $this->assertEquals($exp, $next, "Interval $inv");
        }
        //check crontab
        $check_next = [
            '1 2 3 1 *'=>DateTime::createFromFormat('Y-m-d H:i:s', '2016-01-03 02:01:00')->getTimestamp(),
            '* * * * *'=>\Carbon\Carbon::createFromTimestamp($last)->addMinute(1)->second(0)->getTimestamp(),
            '* 2 * * *'=>\Carbon\Carbon::createFromTimestamp($last)->hour(2)->addDay(1)->second(0)->getTimestamp(),
            '0 12 * * 5'=>\Carbon\Carbon::createFromTimestamp($last)->day(15)->hour(12)->minute(0)->second(0)->getTimestamp(),
            '0 */1 * * *'=>\Carbon\Carbon::createFromTimestamp($last)->addHour(1)->minute(0)->second(0)->getTimestamp(),
            '0 9 */1 * *'=>\Carbon\Carbon::createFromTimestamp($last)->addDay(1)->hour(9)->minute(0)->second(0)->getTimestamp(),
            '0 12 */3 * *'=>\Carbon\Carbon::createFromTimestamp($last)->addDay(3)->hour(12)->minute(0)->second(0)->getTimestamp(),
            '0 12 1 */1 *'=>\Carbon\Carbon::createFromTimestamp($last)->addMonth(1)->day(1)->hour(12)->minute(0)->second(0)->getTimestamp(),
            '*/55 * * * *'=>\Carbon\Carbon::createFromTimestamp($last)->addHour(1)->minute(5)->second(0)->getTimestamp(),
            '0 */15 * * *'=>\Carbon\Carbon::createFromTimestamp($last)->addDay(1)->hour(1)->minute(0)->second(0)->getTimestamp(),
            '0 5 */25 * *'=>\Carbon\Carbon::createFromTimestamp($last)->addMonth(1)->day(3)->hour(5)->minute(0)->second(0)->getTimestamp(),
            '0 12 10 */6 *'=>\Carbon\Carbon::createFromTimestamp($last)->addYear(1)->month(1)->day(10)->hour(12)->minute(0)->second(0)->getTimestamp(),
        ];
        foreach ($check_next as $inv => $exp) {
            $next = \Wwtg99\Schedule\Common\Utils::calNextTime($inv, $last);
            $this->assertEquals($exp, $next, "Interval $inv");
        }
    }
}
