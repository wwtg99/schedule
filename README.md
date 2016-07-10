# Schedule

### Description
Run schedule jobs.
Supported Executors:
  - CronExecutor  based on linux crontab
Supported Jobs:
  - CmdJob: type cmd, execute shell commands
  - PgDumpJob: type pg_dump, execute pg_dump

### Configuration
- min_round: the min interval to check jobs, 
can be set month, week, day, hour, minute, default minute
- cron_time: crontab time string
if specified, will replace min_round
- jobs: json array to define jobs
  - name: job name, required
  - type: job type, specified in JobFactory, required
  - time: job execute interval, required
    - interval format (start with I): `I1y1m1d1h1i1s` (`I3i` means every three minutes)
    - cron format: not supported yet
  - cmd: command to execute, used in CmdJob
  - database: database connection for PgDumpJob

### Usage
1. Define config in jobs.json
2. Register schedule
```
php bin/scheduler.php --register
```
3. Show jobs
```
php bin/scheduler.php --list
```
3. Jobs will be registered in crontab.
