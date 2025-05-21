<?php
// 数据库配置
$host = 'localhost';
$dbname = 'dbname';
$username = 'username';
$password = 'password';

try {
    // 创建PDO连接
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // 设置PDO错误模式为异常
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 设置默认字符集
    $pdo->exec("set names utf8");
    
    // 检测MySQL版本，调整SQL模式
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    if (version_compare($version, '5.7.0', '>=')) {
        // 禁用严格模式以兼容DATETIME默认值
        $pdo->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'STRICT_TRANS_TABLES',''))");
    }
} catch(PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

// 初始化数据库结构（如果不存在）
try {
    // 创建选项表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `options` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `code` varchar(10) NOT NULL,
            `name` varchar(255) NOT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `code` (`code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // 创建投票记录表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `votes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `voter_id` varchar(36) NOT NULL,
            `option_code` varchar(10) NOT NULL,
            `created_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `voter_id` (`voter_id`),
            KEY `option_code` (`option_code`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // 创建系统设置表
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=关闭，1=开启',
            `notice_text` text,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // 插入默认设置（如果不存在）
    $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("
            INSERT INTO `settings` (`id`, `status`, `notice_text`, `updated_at`) 
            VALUES (1, 0, '欢迎参与我们的公众投票，请选择您支持的选项。', NOW())
        ");
    }
    
    // 插入默认选项（如果不存在）
    $stmt = $pdo->query("SELECT COUNT(*) FROM options");
    if ($stmt->fetchColumn() == 0) {
        $options = [
            ['code' => '0', 'name' => '选项0'],
            ['code' => '1', 'name' => '选项1'],
            ['code' => '2', 'name' => '选项2'],
            ['code' => '3', 'name' => '选项3'],
            ['code' => '4', 'name' => '选项4'],
            ['code' => '5', 'name' => '选项5'],
            ['code' => '6', 'name' => '选项6'],
            ['code' => '7', 'name' => '选项7'],
            ['code' => '8', 'name' => '选项8'],
            ['code' => '9', 'name' => '选项9']
        ];
        
        $stmt = $pdo->prepare("INSERT INTO options (code, name, created_at) VALUES (:code, :name, NOW())");
        foreach ($options as $option) {
            $stmt->execute($option);
        }
    }
} catch(PDOException $e) {
    // 尝试以兼容模式重新创建表
    try {
        // 创建选项表（兼容模式）
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `options` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `code` varchar(10) NOT NULL,
                `name` varchar(255) NOT NULL,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `code` (`code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // 创建投票记录表（兼容模式）
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `votes` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `voter_id` varchar(36) NOT NULL,
                `option_code` varchar(10) NOT NULL,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `voter_id` (`voter_id`),
                KEY `option_code` (`option_code`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // 创建系统设置表（兼容模式）
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS `settings` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=关闭，1=开启',
                `notice_text` text,
                `updated_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        
        // 重新尝试插入默认数据
        $stmt = $pdo->query("SELECT COUNT(*) FROM settings");
        if ($stmt->fetchColumn() == 0) {
            $pdo->exec("
                INSERT INTO `settings` (`id`, `status`, `notice_text`, `updated_at`) 
                VALUES (1, 0, '欢迎参与我们的公众投票，请选择您支持的选项。', NOW())
            ");
        }
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM options");
        if ($stmt->fetchColumn() == 0) {
            $options = [
                ['code' => '0', 'name' => '选项0'],
                ['code' => '1', 'name' => '选项1'],
                ['code' => '2', 'name' => '选项2'],
                ['code' => '3', 'name' => '选项3'],
                ['code' => '4', 'name' => '选项4'],
                ['code' => '5', 'name' => '选项5'],
                ['code' => '6', 'name' => '选项6'],
                ['code' => '7', 'name' => '选项7'],
                ['code' => '8', 'name' => '选项8'],
                ['code' => '9', 'name' => '选项9']
            ];
            
            $stmt = $pdo->prepare("INSERT INTO options (code, name, created_at) VALUES (:code, :name, NOW())");
            foreach ($options as $option) {
                $stmt->execute($option);
            }
        }
    } catch(PDOException $e) {
        die("初始化数据库结构失败: " . $e->getMessage());
    }
}    