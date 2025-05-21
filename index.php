<?php
// 强制显示所有PHP错误
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


session_start();
require_once 'config.php';

// 检查嵌入模式
$isEmbedded = isset($_GET['embed']) && $_GET['embed'] == 1;

// 在嵌入模式下跳过会话检查（如果需要匿名投票）
if (!$isEmbedded && !isset($_SESSION['voter_agreed']) && !isset($_GET['skip'])) {
    header('Location: notice.php');
    exit;
}

// 获取投票状态
$stmt = $pdo->query("SELECT status, notice_text FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$voteStatus = $settings['status'] ?? 0;
$noticeText = $settings['notice_text'] ?? '请参与我们的投票';

// 如果投票已关闭且不是管理员或嵌入模式
if ($voteStatus == 0 && !isset($_SESSION['admin']) && !$isEmbedded) {
    $message = "投票已结束，感谢参与！";
}

// 嵌入模式下禁用缓存
if ($isEmbedded) {
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEmbedded ? '投票系统' : '公开投票系统'; ?></title>
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
            .card-hover {
                transition: all 0.3s ease;
            }
            .card-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            }
            .keyboard-button {
                transition: all 0.2s ease;
            }
            .keyboard-button:active {
                transform: scale(0.95);
            }
        }
    </style>
    <?php if ($isEmbedded): ?>
    <style>
        body {
            background-color: white;
            margin: 0;
            padding: 0;
        }
    </style>
    <?php endif; ?>
</head>
<body class="font-inter bg-neutral min-h-screen flex flex-col <?php echo $isEmbedded ? 'p-0 m-0' : ''; ?>">
    <?php if (!$isEmbedded): ?>
    <!-- 导航栏 -->
    <nav class="bg-white shadow-md sticky top-0 z-50 transition-all duration-300" id="navbar">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fa-solid fa-vote-yea text-primary text-2xl"></i>
                <h1 class="text-xl font-bold text-gray-800">公众投票系统</h1>
            </div>
            <div class="hidden md:flex items-center space-x-6">
                <a href="index.php" class="text-gray-600 hover:text-primary transition-colors">首页</a>
                <?php if (isset($_SESSION['admin'])): ?>
                    <a href="admin.php" class="text-gray-600 hover:text-primary transition-colors">管理后台</a>
                    <a href="logout.php" class="text-gray-600 hover:text-primary transition-colors">退出登录</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-primary transition-colors">管理员登录</a>
                <?php endif; ?>
            </div>
            <button class="md:hidden text-gray-600 focus:outline-none" id="menuBtn">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
        <!-- 移动端菜单 -->
        <div class="md:hidden hidden bg-white border-t" id="mobileMenu">
            <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
                <a href="index.php" class="text-gray-600 hover:text-primary transition-colors py-2">首页</a>
                <?php if (isset($_SESSION['admin'])): ?>
                    <a href="admin.php" class="text-gray-600 hover:text-primary transition-colors py-2">管理后台</a>
                    <a href="logout.php" class="text-gray-600 hover:text-primary transition-colors py-2">退出登录</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-primary transition-colors py-2">管理员登录</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-8 <?php echo $isEmbedded ? 'max-w-none w-full p-0' : ''; ?>">
        <?php if (isset($message)): ?>
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-3xl mx-auto mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4"><?= $message ?></h2>
                <?php if (isset($_SESSION['admin'])): ?>
                    <a href="admin.php" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-md hover:bg-primary/90 transition-colors">
                        <i class="fa-solid fa-cog mr-2"></i> 管理投票
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-lg shadow-lg p-6 max-w-3xl mx-auto mb-8 transform transition-all duration-500 opacity-0 translate-y-4 <?php echo $isEmbedded ? 'max-w-none' : ''; ?>" id="voteCard">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 text-shadow">公众意见调查</h2>
                <p class="text-gray-600 mb-6">请选择您支持的选项（可多选），感谢您的参与！</p>
                
                <form id="voteForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM options ORDER BY id ASC LIMIT 10");
                        $options = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($options as $option) {
                            echo '<div class="flex items-center space-x-3 card-hover bg-neutral p-4 rounded-lg">';
                            echo '<input type="checkbox" id="option_' . $option['id'] . '" name="options[]" value="' . $option['code'] . '" class="h-5 w-5 text-primary focus:ring-primary border-gray-300 rounded option-checkbox">';
                            echo '<label for="option_' . $option['id'] . '" class="text-gray-700 cursor-pointer">' . $option['name'] . ' (' . $option['code'] . ')</label>';
                            echo '</div>';
                        }
                        ?>
                    </div>
                    
                    <!-- 数字键盘 -->
                    <div class="bg-neutral rounded-lg p-4 mb-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">数字键盘</h3>
                        <div class="grid grid-cols-3 gap-3" id="numericKeyboard">
                            <?php for ($i = 0; $i <= 9; $i++): ?>
                                <button type="button" class="keyboard-button bg-white hover:bg-gray-100 text-gray-800 font-medium py-3 px-4 rounded-lg shadow transition-all duration-200 flex items-center justify-center text-xl" data-code="<?= $i ?>">
                                    <?= $i ?>
                                </button>
                            <?php endfor; ?>
                            <button type="button" class="keyboard-button bg-white hover:bg-gray-100 text-gray-800 font-medium py-3 px-4 rounded-lg shadow transition-all duration-200 flex items-center justify-center" id="clearAll">
                                <i class="fa-solid fa-eraser"></i>
                            </button>
                        </div>
                        <p class="mt-3 text-sm text-gray-500">点击数字键选择对应选项，已选选项将高亮显示</p>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" id="voteBtn" class="w-full bg-primary hover:bg-primary/90 text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-primary/50 shadow-lg">
                            <i class="fa-solid fa-paper-plane mr-2"></i> 提交投票
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- 结果统计 -->
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-3xl mx-auto transform transition-all duration-500 opacity-0 translate-y-4 <?php echo $isEmbedded ? 'max-w-none' : ''; ?>" id="resultsCard">
            <h2 class="text-2xl font-bold text-gray-800 mb-4 text-shadow">投票结果统计</h2>
            
            <div class="space-y-6">
                <?php
                $stmt = $pdo->query("
                    SELECT o.code, o.name, COUNT(v.option_code) as votes
                    FROM options o
                    LEFT JOIN votes v ON o.code = v.option_code
                    GROUP BY o.code, o.name
                    ORDER BY votes DESC
                ");
                $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                $totalVotes = array_sum(array_column($results, 'votes'));
                
                foreach ($results as $result) {
                    $percentage = $totalVotes > 0 ? ($result['votes'] / $totalVotes) * 100 : 0;
                    $percentage = round($percentage, 1);
                    
                    echo '<div class="space-y-2">';
                    echo '<div class="flex justify-between items-center">';
                    echo '<span class="font-medium text-gray-700">' . $result['name'] . ' (' . $result['code'] . ')</span>';
                    echo '<span class="text-sm text-gray-500">' . $result['votes'] . ' 票 (' . $percentage . '%)</span>';
                    echo '</div>';
                    echo '<div class="w-full bg-gray-200 rounded-full h-2.5">';
                    echo '<div class="bg-primary h-2.5 rounded-full" style="width: ' . $percentage . '%"></div>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="font-medium text-gray-800 mb-3">总投票人数: <span class="text-primary font-bold"><?= $totalVotes ?></span></h3>
                </div>
            </div>
        </div>
    </main>

    <?php if (!$isEmbedded): ?>
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
    <?php endif; ?>

    <!-- 成功提交弹窗 -->
    <div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="modalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fa-solid fa-check text-green-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">投票提交成功</h3>
                <p class="text-gray-500 mb-6">感谢您的参与！您的投票已成功提交。</p>
                <button id="closeModal" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm transition-colors">
                    确定
                </button>
            </div>
        </div>
    </div>

    <script>
        // 导航栏滚动效果
        window.addEventListener('scroll', function() {
            if (<?php echo $isEmbedded ? 'true' : 'false'; ?>) {
                return; // 在嵌入模式下不应用导航栏效果
            }
            
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
            if (<?php echo $isEmbedded ? 'true' : 'false'; ?>) {
                return; // 在嵌入模式下不应用菜单效果
            }
            
            const mobileMenu = document.getElementById('mobileMenu');
            mobileMenu.classList.toggle('hidden');
        });

        // 数字键盘与选项关联
        document.addEventListener('DOMContentLoaded', function() {
            // 数字键点击事件
            document.querySelectorAll('#numericKeyboard button[data-code]').forEach(button => {
                button.addEventListener('click', function() {
                    const code = this.getAttribute('data-code');
                    const checkbox = document.querySelector(`input[name="options[]"][value="${code}"]`);
                    
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        updateButtonStyle(code, checkbox.checked);
                    }
                });
            });
            
            // 清除所有按钮
            document.getElementById('clearAll').addEventListener('click', function() {
                document.querySelectorAll('input[name="options[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                    updateButtonStyle(checkbox.value, false);
                });
            });
            
            // 初始化按钮样式
            document.querySelectorAll('input[name="options[]"]').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    updateButtonStyle(this.value, this.checked);
                });
            });
            
            // 页面加载动画
            setTimeout(() => {
                document.getElementById('voteCard').classList.remove('opacity-0', 'translate-y-4');
                document.getElementById('voteCard').classList.add('opacity-100', 'translate-y-0');
            }, 300);
            
            setTimeout(() => {
                document.getElementById('resultsCard').classList.remove('opacity-0', 'translate-y-4');
                document.getElementById('resultsCard').classList.add('opacity-100', 'translate-y-0');
            }, 600);
            
            // 通知父窗口iframe已加载完成
            if (window.parent !== window && window.postMessage) {
                window.postMessage({ type: 'iframeLoaded' }, '*');
            }
        });
        
        // 更新数字键盘按钮样式
        function updateButtonStyle(code, isChecked) {
            const button = document.querySelector(`#numericKeyboard button[data-code="${code}"]`);
            if (button) {
                if (isChecked) {
                    button.classList.add('bg-primary', 'text-white');
                    button.classList.remove('bg-white', 'hover:bg-gray-100', 'text-gray-800');
                } else {
                    button.classList.remove('bg-primary', 'text-white');
                    button.classList.add('bg-white', 'hover:bg-gray-100', 'text-gray-800');
                }
            }
        }

        // 表单提交处理
        document.getElementById('voteForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const selectedOptions = Array.from(document.querySelectorAll('input[name="options[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedOptions.length === 0) {
                alert('请至少选择一个选项');
                return;
            }
            
            // 显示加载状态
            const voteBtn = document.getElementById('voteBtn');
            voteBtn.disabled = true;
            voteBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> 提交中...';
            
            // 发送投票数据
            fetch('submit_vote.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ options: selectedOptions })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // 显示成功弹窗
                    const successModal = document.getElementById('successModal');
                    const modalContent = document.getElementById('modalContent');
                    
                    successModal.classList.remove('hidden');
                    setTimeout(() => {
                        modalContent.classList.remove('scale-95', 'opacity-0');
                        modalContent.classList.add('scale-100', 'opacity-100');
                    }, 10);
                    
                    // 重置按钮状态
                    voteBtn.disabled = false;
                    voteBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> 提交投票';
                    
                    // 如果是嵌入模式，通知父窗口
                    if (window.parent !== window && window.postMessage) {
                        window.postMessage({ type: 'voteSubmitted' }, '*');
                    }
                    
                    // 刷新结果
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    alert(data.message || '投票失败，请重试');
                    voteBtn.disabled = false;
                    voteBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> 提交投票';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('网络错误，请重试');
                voteBtn.disabled = false;
                voteBtn.innerHTML = '<i class="fa-solid fa-paper-plane mr-2"></i> 提交投票';
            });
        });

        // 关闭成功弹窗
        document.getElementById('closeModal').addEventListener('click', function() {
            const successModal = document.getElementById('successModal');
            const modalContent = document.getElementById('modalContent');
            
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            
            setTimeout(() => {
                successModal.classList.add('hidden');
            }, 300);
        });

        // 处理嵌入模式的消息
        window.addEventListener('message', function(event) {
            if (event.data.type === 'closeVoteModal') {
                window.VoteSystem && window.VoteSystem.hideVoteModal();
            }
        }, false);
    </script>
</body>
</html>    