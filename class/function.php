<?php

/**
 * @计划任务队列系统公用函数
 * @author: Ghostry <ghostry@ghostry.cn>
 * @time: 2013/11
 * @Copyright: blog.ghostry.cn
 */

/**
 * 发送HTTP请求方法，目前只支持CURL发送请求
 * @param  string $url    请求URL
 * @param  array  $params 请求参数
 * @param  string $method 请求方法GET/POST
 * @return array  $data   响应数据
 */
function http($url, $params, $method = 'GET', $header = array(), $multi = false) {
    $opts = array(
	CURLOPT_TIMEOUT => 5 * 60,
	CURLOPT_RETURNTRANSFER => 1,
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_SSL_VERIFYHOST => false,
	CURLOPT_HTTPHEADER => $header
    );
    /* 根据请求类型设置特定参数 */
    switch (strtoupper($method)) {
	case 'GET':
	    $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
	    break;
	case 'POST':
	    //判断是否传输文件
	    $params = $multi ? $params : http_build_query($params);
	    $opts[CURLOPT_URL] = $url;
	    $opts[CURLOPT_POST] = 1;
	    $opts[CURLOPT_POSTFIELDS] = $params;
	    break;
	default:
	    log_write('ERROR:不支持的请求方式 ' . $method, 'httperror');
	    return 'ERROR:不支持的请求方式 ' . $method;
	//throw new Exception('不支持的请求方式！');
    }
    log_write($opts[CURLOPT_URL], 'http');
    /* 初始化并执行curl请求 */
    $ch = curl_init();
    curl_setopt_array($ch, $opts);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) {
	log_write('ERROR:请求发生错误：' . $error, 'httperror');
	return 'ERROR:请求发生错误：' . $error;
	//throw new Exception('请求发生错误：' . $error);
    }
    return $data;
}

/**
 * 写日志
 */
function log_write($word, $path = '', $filename = '') {
    $filename.=$filename ? '.log' : microtime_float() . '.log';
    makeDir(LogsDir);
    if ($path) {
	$path = LogsDir . '/' . $path;
	makeDir($path);
	$filename = $path . '/' . $filename;
    } else {
	$filename = LogsDir . '/' . $filename;
    }
    $fh = fopen($filename, "a");
    $r = fwrite($fh, $word);
    fclose($fh);
    return $r;
}

/*
 *
 * 返回当前 Unix 时间戳和微秒数(用秒的小数表示)浮点数表示，常用来计算代码段执行时间
 */

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

/**
  +----------------------------------------------------------
 * 功能：检测一个目录是否存在，不存在则创建它
  +----------------------------------------------------------
 * @param string    $path      待检测的目录
  +----------------------------------------------------------
 * @return boolean
  +----------------------------------------------------------
 */
function makeDir($path) {
    return is_dir($path) or (makeDir(dirname($path)) and @mkdir($path, 0777));
}

/**
 * 查询服务状态
 */
function runStatus() {
    $database = $GLOBALS['database'];
    $arr = $database->select("status", "time", ['id' => 1]);
    if (!$arr) {
	$database->insert("status", [
	    "time" => time()
	]);
    }
    if (time() - $arr[0] <= SleepTime) {
	return TRUE;
    }
    return FALSE;
}
