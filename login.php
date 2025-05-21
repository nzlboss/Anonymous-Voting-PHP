<?php
session_start();
require_once 'config.php';

// 如果已登录，重定向到管理页面
if (isset($_SESSION['admin'])) {
    header('Location: admin.php');
    exit;
}

// 处理登录请求
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // 这里应该从数据库或配置文件中获取管理员凭据
    // 为简化示例，我们直接硬编码一个管理员账号
    $adminUsername = 'admin';
    $adminPassword = 'password'; // 实际应用中应使用哈希密码
    
    if ($username === $adminUsername && $password === $adminPassword) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = '用户名或密码错误';
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录</title>
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
            .card-hover {
                transition: all 0.3s ease;
            }
            .card-hover:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
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
            </div>
            <button class="md:hidden text-gray-600 focus:outline-none" id="menuBtn">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
        </div>
        <!-- 移动端菜单 -->
        <div class="md:hidden hidden bg-white border-t" id="mobileMenu">
            <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
                <a href="index.php" class="text-gray-600 hover:text-primary transition-colors py-2">首页</a>
            </div>
        </div>
    </nav>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-16">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-md mx-auto card-hover">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">管理员登录</h2>
            
            <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <strong class="font-bold">错误!</strong>
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
            <?php endif; ?>
            
            <form method="post" class="space-y-6">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">用户名</label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-colors"
                        placeholder="请输入用户名">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">密码</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary transition-colors"
                        placeholder="请输入密码">
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-primary hover:bg-primary/90 text-white font-medium py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-primary/50 shadow-lg flex items-center justify-center">
                        <i class="fa-solid fa-sign-in-alt mr-2"></i> 登录
                    </button>
                </div>
            </form>
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