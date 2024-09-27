<?php
include 'functions.php';

// 获取请求参数
$jsonData = file_get_contents("php://input");
// 解析JSON数据
$jsonObj = json_decode($jsonData);
// 现在可以使用$jsonObj访问传递的JSON数据中的属性或方法
// 获取token，通过token获取用户名
$token = $jsonObj->token;
if(empty($token)) {
  echo json_encode(array(
    'err' => 1,
    'msg' => 'Token is empty'
  ));
  return;
}
session_id($token);
// 强制禁止浏览器的隐式cookie中的sessionId
$_COOKIE = [ 'PHPSESSID' => '' ];
session_start([ // php7
    'cookie_lifetime' => 2000000000,
    'read_and_close'  => false,
]);
// 获取用户名
$userId = isset($_SESSION['uid']) && is_string($_SESSION['uid']) ? $_SESSION['uid'] : $_SESSION['username'];
if(!isset($userId)) {
  echo json_encode(array(
    'err' => 1,
    'msg' => 'User information not obtained'
  ));
  return;
}
// 获取要进行的操作
$action = $jsonObj->action;

if($action == "listAllCronJobs") {
  // 获取所有crontab任务
  $output = [];
  // 通过token获取用户名，使用crontab -u 用户名 -l来查看当前用户的定时任务
  exec("sudo crontab -u {$userId} -l", $output);
  
  // 返回crontab中的任务
  echo json_encode($output);
} else if($action == "addCronJob" || $action == "updateCronJob" || $action == "deleteCronJob") {
  // 构建命令
  $command = "sudo crontab -u {$userId} -l"; // 列出当前cron任务
  $command2 = "sudo crontab -u {$userId} -"; // 通过标准输入写入任务

  // 执行命令，查询现有的定时任务
  exec($command, $crontab);
  // 获取要添加的任务
  $cronJob = $jsonObj->cronjob;
  
  if($action == "addCronJob") {
    // 添加crontab任务
    // 判断脚本文件是否存在并且具有可执行权限
    $scriptErr = checkScriptFile($cronJob);
    if($scriptErr != "") {
      echo json_encode(array(
        'err' => 1,
        'msg' => $scriptErr
      ));
      return;
    }
    // 检查任务是否已经存在
    $jobExist = false;
    foreach ($crontab as $key => $value) {
      if ($value == $cronJob) {
        $jobExist = true;
        break;
      }
    }
    if($jobExist) {
      // 如果任务已经存在，则不进行处理
      echo json_encode(array(
        'err' => 1,
        'msg' => 'Cron job already exists'
      ));
      return;
    }
    // 将新任务追加到数组中
    $crontab[] = $cronJob;
  } else if($action == "updateCronJob") {
    // 更新crontab任务
    // 获取要更新为的新任务
    $newCronJob = $jsonObj->newCronjob;
    // 判断脚本文件是否存在并且具有可执行权限
    $scriptErr = checkScriptFile($newCronJob);
    if($scriptErr != "") {
      echo json_encode(array(
        'err' => 1,
        'msg' => $scriptErr
      ));
      return;
    }
    // 判断任务是否进行了修改，没修改直接返回操作成功
    if($cronJob == $newCronJob) {
      echo json_encode(array(
        'err' => 0
      ));
      return;
    }
    $jobExist = false;
    // 更新指定的任务，同时检查更新为的任务是否已经存在
    foreach ($crontab as $key => $value) {
      if ($value == $cronJob) {
        $crontab[$key] = $newCronJob;
      } else if ($value == $newCronJob) {
        $jobExist = true;
      }
    }
    if($jobExist) {
      // 如果更新为的任务已经存在，则不进行处理，返回信息
      echo json_encode(array(
        'err' => 1,
        'msg' => 'Cron job already exists'
      ));
      return;
    }
  } else if($action == "deleteCronJob") {
    // 删除crontab任务
    // 获取要删除的任务
    $cronJob = $jsonObj->cronjob;
    // 删除指定的任务
    foreach ($crontab as $key => $value) {
      if ($value == $cronJob) {
          unset($crontab[$key]);
          break;
      }
    }
  }
  // 将数组转换为字符串，并添加换行符
  $crontab = implode("\n", $crontab) . "\n";
 
  // 打开一个管道用于写入
  $pipe = popen($command2, 'w');
  // 将新的cron任务写入管道
  $result = fwrite($pipe, $crontab);
  // 关闭管道
  pclose($pipe);

  // 返回添加任务的结果
  if ($result == false) {
    echo json_encode(array(
      'err' => 1,
      'msg' => 'Failed to save cron job'
    ));
  } else {
    echo json_encode(array(
      'err' => 0
    ));
  }
} else {
  echo json_encode(array(
    'err' => 1,
    'msg' => 'Invalid action'
  ));
}
?>