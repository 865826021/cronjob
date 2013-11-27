<?php

/**
 * @计划任务队列系统配置文件
 * @author: Ghostry <ghostry@ghostry.cn>
 * @time: 2013/11
 * @Copyright: blog.ghostry.cn
 */
//可编辑
$mysql_db_config['server'] = 'localhost';
$mysql_db_config['username'] = 'username';
$mysql_db_config['password'] = 'password';
$mysql_db_config['database_name'] = 'cronjob';
$mysql_db_config['charset'] = 'utf8';
//任务最大重试次数范围1-99
define('ExecutionNum', 1);
//日志目录
define('LogsDir', 'logs');
//任务执行间隔
define('SleepTime', 5);
// 开启任务密码
define('RunPass', 'passWord');


//可编辑结束


ini_set('date.timezone', 'Asia/Shanghai');
header('Content-type: text/html; charset=UTF8');
require 'class/medoo.php';
$database = new medoo($mysql_db_config);
require 'class/function.php';
require 'class/Services_JSON.class.php';
$Services_JSON = new Services_JSON();
