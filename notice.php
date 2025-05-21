<?php
session_start();
require_once 'config.php';

// 如果用户已经同意，则重定向到投票页面
if (isset($_SESSION['voter_agreed'])) {
    header('Location: index.php');
    exit;
}

// 获取投票状态和须知文本
$stmt = $pdo->query("SELECT status, notice_text FROM settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
$voteStatus = $settings['status'] ?? 0;
$noticeText = $settings['notice_text'] ?? '请参与我们的投票';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>投票须知</title>
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
            .btn-hover {
                transition: all 0.3s ease;
            }
            .btn-hover:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(22, 93, 255, 0.25);
            }
        }
    </style>
</head>
<body class="font-inter bg-neutral min-h-screen flex flex-col">
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

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6 max-w-3xl mx-auto transform transition-all duration-500 opacity-0 translate-y-4" id="noticeCard">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-shadow">投票须知</h2>
            
            <div class="prose max-w-none mb-8">
                <?php echo nl2br(htmlspecialchars($noticeText)); ?>
            </div>
            
            <div class="mt-8">
                <form id="agreeForm" method="post" action="process_agreement.php">
                    <div class="flex items-start mb-6">
                        <input type="checkbox" id="agreeCheckbox" name="agree" class="h-5 w-5 text-primary focus:ring-primary border-gray-300 rounded mt-1">
                        <label for="agreeCheckbox" class="ml-2 text-gray-700">
                            我已阅读并同意上述条款，愿意参与此次投票
                        </label>
                    </div>
                    
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button type="submit" id="agreeBtn" class="btn-hover bg-primary hover:bg-primary/90 text-white font-medium py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-primary/50 shadow-lg flex items-center justify-center">
                            <i class="fa-solid fa-check mr-2"></i> 我同意，开始投票
                        </button>
                        <a href="index.php?skip=1" class="btn-hover bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-gray-400/50 shadow flex items-center justify-center">
                            <i class="fa-solid fa-times mr-2"></i> 暂不参与
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
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

    <!-- 加载弹窗 -->
    <div id="loadingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 flex flex-col items-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mb-4"></div>
            <p class="text-gray-700 text-center">正在处理您的请求，请稍候...</p>
        </div>
    </div>

    <!-- 错误提示弹窗 -->
    <div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 transform transition-all duration-300 scale-95 opacity-0" id="errorModalContent">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <i class="fa-solid fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">操作失败</h3>
                <p id="errorMessage" class="text-gray-500 mb-6">请检查您的操作并重试</p>
                <button id="closeErrorModal" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm transition-colors">
                    确定
                </button>
            </div>
        </div>
    </div>

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

        // 表单提交处理
        document.addEventListener('DOMContentLoaded', function() {
            // 页面加载动画
            setTimeout(() => {
                document.getElementById('noticeCard').classList.remove('opacity-0', 'translate-y-4');
                document.getElementById('noticeCard').classList.add('opacity-100', 'translate-y-0');
            }, 300);
            
            // 同意表单提交
            const agreeForm = document.getElementById('agreeForm');
            const agreeBtn = document.getElementById('agreeBtn');
            const agreeCheckbox = document.getElementById('agreeCheckbox');
            const loadingModal = document.getElementById('loadingModal');
            const errorModal = document.getElementById('errorModal');
            const errorModalContent = document.getElementById('errorModalContent');
            const errorMessage = document.getElementById('errorMessage');
            const closeErrorModal = document.getElementById('closeErrorModal');
            
            agreeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // 检查是否勾选同意
                if (!agreeCheckbox.checked) {
                    showError('请先勾选同意条款');
                    return;
                }
                
                // 显示加载状态
                loadingModal.classList.remove('hidden');
                agreeBtn.disabled = true;
                agreeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> 处理中...';
                
                // 发送表单数据
                const formData = new FormData(agreeForm);
                
                fetch('process_agreement.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // 隐藏加载状态
                    loadingModal.classList.add('hidden');
                    
                    if (data.success) {
                        // 成功则重定向到投票页面
                        window.location.href = 'index.php';
                    } else {
                        // 显示错误信息
                        showError(data.message || '处理请求时出错，请重试');
                        agreeBtn.disabled = false;
                        agreeBtn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> 我同意，开始投票';
                    }
                })
                .catch(error => {
                    // 隐藏加载状态
                    loadingModal.classList.add('hidden');
                    
                    // 显示错误信息
                    console.error('Error:', error);
                    showError('网络错误，请检查您的连接并重试');
                    agreeBtn.disabled = false;
                    agreeBtn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> 我同意，开始投票';
                });
            });
            
            // 关闭错误弹窗
            closeErrorModal.addEventListener('click', function() {
                errorModalContent.classList.remove('scale-100', 'opacity-100');
                errorModalContent.classList.add('scale-95', 'opacity-0');
                
                setTimeout(() => {
                    errorModal.classList.add('hidden');
                }, 300);
            });
            
            // 显示错误信息
            function showError(message) {
                errorMessage.textContent = message;
                errorModal.classList.remove('hidden');
                
                setTimeout(() => {
                    errorModalContent.classList.remove('scale-95', 'opacity-0');
                    errorModalContent.classList.add('scale-100', 'opacity-100');
                }, 10);
            }
        });
    </script>
</body>
</html>    