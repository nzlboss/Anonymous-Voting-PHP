-- 创建选项表
CREATE TABLE IF NOT EXISTS `options` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(10) NOT NULL,
    `name` varchar(255) NOT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建投票记录表
CREATE TABLE IF NOT EXISTS `votes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `voter_id` varchar(36) NOT NULL,
    `option_code` varchar(10) NOT NULL,
    `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `voter_id` (`voter_id`),
    KEY `option_code` (`option_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建系统设置表
CREATE TABLE IF NOT EXISTS `settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=关闭，1=开启',
    `notice_text` text,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入默认设置
INSERT INTO `settings` (`id`, `status`, `notice_text`, `updated_at`) 
VALUES (1, 0, '欢迎参与我们的公众投票，请选择您支持的选项。', NOW());

-- 插入默认选项（使用数字0-9）
INSERT INTO `options` (`code`, `name`) VALUES
('0', '选项0'),
('1', '选项1'),
('2', '选项2'),
('3', '选项3'),
('4', '选项4'),
('5', '选项5'),
('6', '选项6'),
('7', '选项7'),
('8', '选项8'),
('9', '选项9');    