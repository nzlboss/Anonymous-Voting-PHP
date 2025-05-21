<?php
session_start();
require_once 'config.php';

// 设置响应头
header('Content-Type: application/json');

// 检查是否已同意参与投票
if (!isset($_SESSION['voter_agreed'])) {
    echo json_encode([
        'success' => false,
        'message' => '请先同意参与投票'
    ]);
    exit;
}

// 检查投票是否开启
$stmt = $pdo->query("SELECT status FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$voteStatus = $settings['status'] ?? 0;

if ($voteStatus == 0) {
    echo json_encode([
        'success' => false,
        'message' => '投票已结束'
    ]);
    exit;
}

// 获取投票数据
$data = json_decode(file_get_contents('php://input'), true);
$selectedOptions = $data['options'] ?? [];

// 验证选项
if (empty($selectedOptions)) {
    echo json_encode([
        'success' => false,
        'message' => '请至少选择一个选项'
    ]);
    exit;
}

// 生成唯一的投票者ID（匿名）
$voterId = uniqid();

try {
    // 开始事务
    $pdo->beginTransaction();
    
    // 插入投票记录
    $stmt = $pdo->prepare("INSERT INTO votes (voter_id, option_code) VALUES (:voter_id, :option_code)");
    
    foreach ($selectedOptions as $optionCode) {
        $stmt->execute([
            'voter_id' => $voterId,
            'option_code' => $optionCode
        ]);
    }
    
    // 提交事务
    $pdo->commit();
    
    // 记录用户已投票
    $_SESSION['has_voted'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => '投票提交成功'
    ]);
} catch (PDOException $e) {
    // 回滚事务
    $pdo->rollBack();
    
    echo json_encode([
        'success' => false,
        'message' => '投票失败，请重试'
    ]);
}    