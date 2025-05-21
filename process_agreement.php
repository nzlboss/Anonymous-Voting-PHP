<?php
session_start();
require_once 'config.php';

// 验证是否通过POST请求提交
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    die(json_encode(['success' => false, 'message' => '请求方法不允许']));
}

// 验证是否勾选同意
if (!isset($_POST['agree']) || $_POST['agree'] !== 'on') {
    die(json_encode(['success' => false, 'message' => '请先阅读并同意投票须知']));
}

// 记录用户已同意
$_SESSION['voter_agreed'] = true;
$_SESSION['voter_id'] = uniqid(); // 生成唯一投票者ID

// 返回成功响应
die(json_encode(['success' => true]));    