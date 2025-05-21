
# PHP匿名投票系统


## 项目概述
本系统是一个基于PHP和MySQL的投票系统管理界面，主要用于管理投票选项、查看投票记录、设置投票状态等功能。支持管理员登录、选项增删改查、投票数据统计及投票时间修复等功能。


## 功能列表
1. **管理员登录**：验证身份后访问管理功能。  
2. **选项管理**：  
   - 添加、编辑、删除投票选项（支持选项编号和名称维护）。  
3. **投票状态管理**：开启/关闭投票功能，实时更新状态。  
4. **投票统计**：显示总投票人数、各选项得票数及占比。  
5. **详细投票记录**：按用户分组查看投票时间和选择的选项（仅显示选项编号）。  
6. **时间修复**：自动处理数据库时间格式，正确显示投票时间（兼容时间戳和日期字符串）。


## 安装步骤

### 1. 环境要求
- PHP 7.0+  
- MySQL 5.6+  
- Apache/Nginx 服务器  


### 2. 部署步骤
#### （1）克隆或下载代码
```bash
git clone https://github.com/nzlboss/Anonymous-Voting-PHP.git
cd your-project-directory
```

#### （2）配置数据库连接
修改 `config.php` 文件，填写数据库信息：
```php
<?php
$pdo = new PDO('mysql:host=localhost;dbname=vote_system;charset=utf8mb4', 'your_username', 'your_password');
?>
```

#### （3）创建数据库表
执行以下SQL语句创建所需表结构：

**`options` 表（投票选项）**：
```sql
CREATE TABLE `options` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(10) NOT NULL COMMENT '选项编号（数字）',
    `name` VARCHAR(255) NOT NULL COMMENT '选项名称'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**`votes` 表（投票记录）**：
```sql
CREATE TABLE `votes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `voter_id` VARCHAR(50) NOT NULL COMMENT '投票者唯一标识',
    `created_at` INT NOT NULL COMMENT '投票时间（UNIX时间戳或DATETIME格式）',
    `option_code` VARCHAR(10) NOT NULL COMMENT '选择的选项编号'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**`settings` 表（投票设置）**：
```sql
CREATE TABLE `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `status` TINYINT DEFAULT 0 COMMENT '投票状态（0=关闭，1=开启）',
    `notice_text` TEXT COMMENT '投票公告内容',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '最后更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

#### （4）设置管理员账号
- 直接在 `login.php` 中修改默认账号密码（示例代码中为 `admin/admin`），或通过数据库添加管理员验证逻辑。


## 使用说明

### 1. 登录管理后台
访问 `http://your-domain.com/admin.php`，使用管理员账号密码登录。


### 2. 管理投票选项
- **添加选项**：在“选项管理”页面填写编号和名称，点击“添加选项”。  
- **编辑选项**：点击选项列表中的“编辑”按钮，修改后保存。  
- **删除选项**：点击“删除”按钮，确认后永久删除（注意：已关联的投票记录将保留选项编号）。


### 3. 投票状态设置
- 在“投票状态”模块选择“开启”或“关闭”，点击“保存设置”实时生效。


### 4. 查看投票统计
- 在“投票统计”模块查看总投票人数和各选项得票数。  
- “详细投票记录”显示每个用户的投票时间和选择的选项编号（以“→”分隔）。

### 4. 如何嵌入投票系统
要在您的网站中嵌入我们的投票系统，只需添加以下代码：

```<script src="https://your-vote-system-domain.com/embed.js"></script>
<script>
    // 初始化投票系统
    initVoteSystem({
        baseUrl: 'https://your-vote-system-domain.com' // 替换为您的投票系统域名
    });
</script>
```

## 数据库结构说明
| 表名       | 主要字段                     | 说明                          |
|------------|------------------------------|-------------------------------|
| `options`  | `id`, `code`, `name`         | 存储投票选项，`code`为显示的数字编号 |
| `votes`    | `voter_id`, `created_at`, `option_code` | 投票记录，`created_at`支持时间戳或字符串 |
| `settings` | `status`, `notice_text`      | 投票状态和公告内容              |


## 贡献与反馈
- 如需贡献代码或反馈问题，请通过GitHub Issues提交。  
- 联系邮箱：qeaf@163.com  


## 注意事项
1. 生产环境建议：  
   - 对管理员密码进行加密存储（如使用 `password_hash`）。  
   - 限制管理员IP访问，增强安全性。  
2. 时间显示异常时，请检查数据库 `votes.created_at` 字段格式是否为有效时间戳或日期字符串。
