<?php
session_start();
require_once 'config.php';

// 验证管理员身份
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

// 获取所有投票记录
$stmt = $pdo->query("
    SELECT v.voter_id, v.created_at, o.code
    FROM votes v
    JOIN options o ON v.option_code = o.code
    ORDER BY v.voter_id, v.id ASC
");
$votes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 按投票者ID分组（仅保留时间和选项Code）
$groupedVotes = [];
foreach ($votes as $vote) {
    $voterId = $vote['voter_id'];
    if (!isset($groupedVotes[$voterId])) {
        $groupedVotes[$voterId] = [
            'created_at' => $vote['created_at'],
            'selections' => []
        ];
    }
    $groupedVotes[$voterId]['selections'][] = $vote['code'];
}

// 获取投票状态
$stmt = $pdo->query("SELECT status, notice_text FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$voteStatus = $settings['status'] ?? 0;
$noticeText = $settings['notice_text'] ?? '请参与我们的投票';

// 处理投票状态更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vote_status'])) {
    $newStatus = (int)$_POST['vote_status'];
    $stmt = $pdo->prepare("UPDATE settings SET status = ?, updated_at = NOW() WHERE id = 1");
    $stmt->execute([$newStatus]);
    
    // 刷新页面
    header('Location: admin.php');
    exit;
}

// === 新增选项管理功能 ===
// 处理添加选项
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_option'])) {
    $code = $_POST['code'];
    $name = $_POST['name'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO options (code, name) VALUES (?, ?)");
        $stmt->execute([$code, $name]);
        $optionMessage = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">选项添加成功</div>';
    } catch (PDOException $e) {
        $optionMessage = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">选项添加失败: ' . $e->getMessage() . '</div>';
    }
}

// 处理编辑选项
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_option'])) {
    $id = $_POST['id'];
    $code = $_POST['code'];
    $name = $_POST['name'];
    
    try {
        $stmt = $pdo->prepare("UPDATE options SET code = ?, name = ? WHERE id = ?");
        $stmt->execute([$code, $name, $id]);
        $optionMessage = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">选项更新成功</div>';
    } catch (PDOException $e) {
        $optionMessage = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">选项更新失败: ' . $e->getMessage() . '</div>';
    }
}

// 处理删除选项
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM options WHERE id = ?");
        $stmt->execute([$id]);
        $optionMessage = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">选项删除成功</div>';
    } catch (PDOException $e) {
        $optionMessage = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">选项删除失败: ' . $e->getMessage() . '</div>';
    }
}

// 获取单个选项用于编辑
$editOption = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM options WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editOption = $stmt->fetch(PDO::FETCH_ASSOC);
}

// 获取所有选项
$stmt = $pdo->query("SELECT * FROM options ORDER BY id ASC");
$options = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投票管理后台</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#165DFF',
                        secondary: '#36CFC9',
                        neutral: '#F5F7FA',
                        success: '#52C41A',
                        danger: '#FF4D4F',
                        admin: '#2C3E50',
                    },
                    fontFamily: {
                        inter: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .text-shadow {
                text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .table-hover {
                transition: all 0.2s ease;
            }
            .table-hover:hover {
                background-color: rgba(22, 93, 255, 0.05);
            }
            .card-hover {
                transition: all 0.2s ease;
            }
            .card-hover:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            }
        }
    </style>
</head>
<body class="font-inter bg-neutral min-h-screen flex flex-col">
    <!-- 导航栏 -->
    <nav class="bg-admin text-white shadow-md sticky top-0 z-50 transition-all duration-300" id="navbar">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-cogs text-primary text-2xl"></i>
                <h1 class="text-xl font-bold">投票系统管理</h1>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="admin.php" class="text-white hover:text-primary transition-colors">管理面板</a>
                <a href="index.php" class="text-white hover:text-primary transition-colors">查看投票</a>
                <a href="logout.php" class="text-white hover:text-primary transition-colors">退出登录</a>
            </div>
            <button class="md:hidden text-white focus:outline-none" id="menuBtn">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
        <!-- 移动端菜单 -->
        <div class="md:hidden hidden bg-admin border-t" id="mobileMenu">
            <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
                <a href="admin.php" class="text-white hover:text-primary transition-colors py-2">管理面板</a>
                <a href="index.php" class="text-white hover:text-primary transition-colors py-2">查看投票</a>
                <a href="logout.php" class="text-white hover:text-primary transition-colors py-2">退出登录</a>
            </div>
        </div>
    </nav>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-5xl mx-auto mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-shadow">投票管理面板</h2>
            
            <!-- === 选项管理卡片 === -->
            <div class="mb-8">
                <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fa-solid fa-list-alt text-primary mr-2"></i> 选项管理
                </h3>
                
                <?php if (isset($optionMessage)): ?>
                    <?php echo $optionMessage; ?>
                <?php endif; ?>
                
                <!-- 编辑/添加选项表单 -->
                <div class="bg-neutral rounded-lg p-6 mb-6">
                    <h4 class="text-lg font-medium text-gray-800 mb-4"><?= $editOption ? '编辑选项' : '添加新选项' ?></h4>
                    <form method="POST">
                        <?php if ($editOption): ?>
                            <input type="hidden" name="id" value="<?= $editOption['id'] ?>">
                        <?php endif; ?>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label for="code" class="block text-gray-700 font-medium mb-2">选项编号</label>
                                <input 
                                    type="text" 
                                    id="code" 
                                    name="code" 
                                    required 
                                    value="<?= htmlspecialchars($editOption['code'] ?? '') ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/50"
                                >
                            </div>
                            <div>
                                <label for="name" class="block text-gray-700 font-medium mb-2">选项名称</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    required 
                                    value="<?= htmlspecialchars($editOption['name'] ?? '') ?>"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/50"
                                >
                            </div>
                            <div class="flex items-end">
                                <button 
                                    type="submit" 
                                    name="<?= $editOption ? 'edit_option' : 'add_option' ?>" 
                                    class="bg-primary hover:bg-primary/90 text-white font-medium py-2 px-4 rounded-md transition-colors flex items-center"
                                >
                                    <i class="fa-solid fa-<?= $editOption ? 'save' : 'plus' ?> mr-1"></i> 
                                    <?= $editOption ? '保存修改' : '添加选项' ?>
                                </button>
                                <?php if ($editOption): ?>
                                    <a href="admin.php" class="ml-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-md transition-colors">
                                        取消
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- 选项列表 -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">选项编号</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">选项名称</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($options as $option): ?>
                            <tr class="table-hover">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $option['id'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $option['code'] ?></td>
                                <td class="px-6 py-4 text-sm text-gray-700"><?= $option['name'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="admin.php?edit=<?= $option['id'] ?>" class="text-primary hover:text-primary/80 transition-colors mr-3">
                                        <i class="fa-solid fa-pencil mr-1"></i> 编辑
                                    </a>
                                    <a href="admin.php?delete=<?= $option['id'] ?>" class="text-danger hover:text-danger/80 transition-colors" onclick="return confirm('确定要删除此选项吗？')">
                                        <i class="fa-solid fa-trash mr-1"></i> 删除
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- === 投票状态和统计 === -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-neutral rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fa-solid fa-sliders text-primary mr-2"></i> 投票状态
                    </h3>
                    
                    <form method="post" class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input type="radio" id="status_open" name="vote_status" value="1" 
                                    <?php echo ($voteStatus == 1) ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <label for="status_open" class="ml-2 text-gray-700">开启投票</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="status_closed" name="vote_status" value="0" 
                                    <?php echo ($voteStatus == 0) ? 'checked' : ''; ?>
                                    class="h-4 w-4 text-primary focus:ring-primary border-gray-300">
                                <label for="status_closed" class="ml-2 text-gray-700">关闭投票</label>
                            </div>
                        </div>
                        
                        <button type="submit" class="bg-primary hover:bg-primary/90 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center">
                            <i class="fa-solid fa-save mr-2"></i> 保存设置
                        </button>
                    </form>
                </div>
                
                <div class="bg-neutral rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <i class="fa-solid fa-chart-pie text-primary mr-2"></i> 投票统计
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">总投票人数:</span>
                            <span class="font-semibold text-gray-800"><?php echo count($groupedVotes); ?></span>
                        </div>
                        
                        <?php
                        $stmt = $pdo->query("
                            SELECT o.code, COUNT(v.option_code) as votes
                            FROM options o
                            LEFT JOIN votes v ON o.code = v.option_code
                            GROUP BY o.code
                            ORDER BY votes DESC
                        ");
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        $totalVotes = array_sum(array_column($results, 'votes'));
                        
                        foreach ($results as $result):
                        ?>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">选项 <?php echo $result['code']; ?>:</span>
                            <span class="font-semibold text-gray-800"><?php echo $result['votes']; ?> 票</span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fa-solid fa-users text-primary mr-2"></i> 详细投票记录
                <span class="ml-2 text-sm font-normal text-gray-500">(共 <?php echo count($groupedVotes); ?> 条记录)</span>
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">投票ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">投票时间</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">选择选项</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($groupedVotes as $voterId => $voteData): ?>
                        <tr class="table-hover">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo substr($voterId, 0, 8); ?>...
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php 
                                // 修复时间显示问题
                                if (is_numeric($voteData['created_at'])) {
                                    // 如果是时间戳（整数）
                                    echo date('Y-m-d H:i:s', $voteData['created_at']);
                                } else {
                                    // 如果是日期字符串
                                    echo date('Y-m-d H:i:s', strtotime($voteData['created_at']));
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <?php
                                // 只显示选项编号（数字）
                                echo implode(' → ', $voteData['selections']);
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center space-x-2">
                        <i class="fa-solid fa-vote-yea text-primary text-xl"></i>
                        <span class="font-bold text-lg">公众投票系统</span>
                    </div>
                    <p class="text-gray-400 mt-2 text-sm">匿名参与，真实表达</p>
                </div>
                <div class="flex space-x-6">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fa-brands fa-weibo text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fa-brands fa-wechat text-xl"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i class="fa-brands fa-twitter text-xl"></i>
                    </a>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-6 pt-6 text-center text-gray-400 text-sm">
                <p>© 2025 公众投票系统 - 保留所有权利</p>
            </div>
        </div>
    </footer>

    <script>
        // 导航栏滚动效果
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 10) {
                navbar.classList.add('py-2', 'shadow-lg');
                navbar.classList.remove('py-3', 'shadow-md');
            } else {
                navbar.classList.add('py-3', 'shadow-md');
                navbar.classList.remove('py-2', 'shadow-lg');
            }
        });

        // 移动端菜单切换
        document.getElementById('menuBtn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>