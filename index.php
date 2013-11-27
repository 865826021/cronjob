<?php

/**
 * @计划任务队列系统入口文件
 * @author: Ghostry <ghostry@ghostry.cn>
 * @time: 2013/11
 * @Copyright: blog.ghostry.cn
 */
require 'config.inc.php';
//选择
$action = isset($_GET['act']) ? $_GET['act'] : '';
switch ($action) {
    case 'add':
	//如果是添加
	$post = $_POST;
	$url = isset($post['url']) ? trim($post['url']) : exit;
	if (!preg_match('/^http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $url)) {
	    exit('网址错误');
	}
	unset($post['url']);
	$method = isset($post['method']) ? (strtoupper($post['method']) === 'POST' ? 1 : 0) : 0;
	unset($post['method']);
	$runtime = isset($post['runtime']) ? $post['runtime'] : date('YmdHis');
	unset($post['runtime']);
	$params = $Services_JSON->encode($post);
	$last_user_id = $database->insert("jobs", [
	    "url" => $url,
	    "method" => $method,
	    "params" => $params,
	    'runtime' => $runtime
	]);
	print_r($last_user_id);
	break;
    case 'run':
	//开启程序
	if (@$_GET['auth'] !== md5(date('YmdH') . RunPass)) {
	    exit('NoAccess');
	}
	if (runStatus()) {
	    exit('Runing');
	}
	ini_set("max_execution_time", 0);
	while (true) {
	    $url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	    $ch = curl_init();
	    $curl_opt = array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_TIMEOUT => 1,
	    );
	    curl_setopt_array($ch, $curl_opt);
	    curl_exec($ch);
	    curl_close($ch);
	    //修改状态
	    $database->update("status", ["time" => time()], ["id" => 1]);
	    sleep(SleepTime);
	}
	break;
    case 'status':
	//状态查询
	if (runStatus()) {
	    echo 'yes';
	} else {
	    echo 'no';
	}
	break;
    default:
	//默认是执行
	//取出数据
	$arr = $database->select("jobs", "*", [
	    'runtime[<]' => date('YmdHis'),
	    "ORDER" => "id",
	    "LIMIT" => [0, 1]
	]);
	if (!$arr) {
	    echo '没有任务';
	    exit;
	}
	$arr = $arr[0];
	//检查执行状态
	if ($arr['status']) {
	    exit;
	}
	if ($arr['num'] >= ExecutionNum) {
	    //如果超次数删除记录并记录日志
	    $database->delete("jobs", [
		"id" => $arr['id']
	    ]);
	    log_write(print_r(array('job' => $arr, 'return' => '超出次数,抛弃任务。'), TRUE), 'jobserror', $arr['id']);
	    exit;
	}
	//开始执行修改执行状态
	$database->update("jobs", [
	    "status" => 1,
		], [
	    "id" => $arr['id']
	]);
	$params = $arr['params'] ? $Services_JSON->decode($arr['params']) : array('time' => time());
	$method = $arr['method'] ? 'POST' : 'GET';
	$data = http($arr['url'], $params, $method);
	if ($data !== 'ok') {
	    //如果失败了增加次数
	    $database->update("jobs", [
		"num[+]" => 1,
		"status" => 0
		    ], [
		"id" => $arr['id']
	    ]);
	    log_write(print_r(array('job' => $arr, 'return' => $data), TRUE), 'jobserror', $arr['id']);
	} else {
	    //如果成功了删除记录并记录日志
	    $database->delete("jobs", [
		"id" => $arr['id']
	    ]);
	    log_write(print_r(array('job' => $arr, 'return' => $data), TRUE), 'jobs', $arr['id']);
	}
	break;
}
