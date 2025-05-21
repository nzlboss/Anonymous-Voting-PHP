// 投票系统嵌入脚本
(function() {
    // 避免重复加载
    if (window.VoteSystem) {
        return;
    }
    
    // 创建命名空间
    window.VoteSystem = {
        loaded: false,
        loading: false,
        config: {
            baseUrl: 'http://www.7ml.cn/1', // 替换为实际域名
            theme: 'light', // 可选: 'light', 'dark'
            animation: 'fade', // 可选: 'fade', 'slide'
            position: 'center', // 可选: 'center', 'right', 'left'
            width: '400px',
            height: '600px'
        },
        
        // 初始化投票系统
        init: function(options) {
            // 合并配置
            if (options) {
                for (var key in options) {
                    if (options.hasOwnProperty(key)) {
                        this.config[key] = options[key];
                    }
                }
            }
            
            // 创建投票按钮
            this.createLaunchButton();
        },
        
        // 创建启动按钮
        createLaunchButton: function() {
            // 检查按钮是否已存在
            if (document.getElementById('vote-system-launcher')) {
                return;
            }
            
            // 创建按钮元素
            var launcher = document.createElement('button');
            launcher.id = 'vote-system-launcher';
            launcher.className = 'vote-system-launcher';
            launcher.innerHTML = '<i class="fa-solid fa-vote-yea"></i> 参与投票';
            
            // 应用样式
            launcher.style.position = 'fixed';
            launcher.style.bottom = '20px';
            launcher.style.right = '20px';
            launcher.style.zIndex = '9999';
            launcher.style.padding = '12px 20px';
            launcher.style.background = '#165DFF';
            launcher.style.color = 'white';
            launcher.style.border = 'none';
            launcher.style.borderRadius = '50px';
            launcher.style.fontSize = '16px';
            launcher.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            launcher.style.cursor = 'pointer';
            launcher.style.transition = 'all 0.3s ease';
            launcher.style.display = 'flex';
            launcher.style.alignItems = 'center';
            launcher.style.justifyContent = 'center';
            
            // 添加按钮悬停效果
            launcher.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.05)';
                this.style.boxShadow = '0 6px 16px rgba(22, 93, 255, 0.25)';
            });
            
            launcher.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            });
            
            // 添加点击事件
            launcher.addEventListener('click', function() {
                window.VoteSystem.showVoteModal();
            });
            
            // 添加到页面
            document.body.appendChild(launcher);
            
            // 加载Font Awesome
            this.loadFontAwesome();
        },
        
        // 加载Font Awesome
        loadFontAwesome: function() {
            if (!document.querySelector('link[href*="font-awesome"]')) {
                var link = document.createElement('link');
                link.href = 'https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css';
                link.rel = 'stylesheet';
                document.head.appendChild(link);
            }
        },
        
        // 显示投票弹窗
        showVoteModal: function() {
            if (this.loading) {
                return;
            }
            
            if (!this.loaded) {
                this.loading = true;
                this.loadVoteContent();
            } else {
                this.displayModal();
            }
        },
        
        // 加载投票内容
        loadVoteContent: function() {
            var self = this;
            
            // 创建遮罩层
            var overlay = document.createElement('div');
            overlay.id = 'vote-system-overlay';
            overlay.className = 'vote-system-overlay';
            
            // 应用样式
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.6)';
            overlay.style.zIndex = '10000';
            overlay.style.display = 'none';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';
            
            // 添加关闭事件
            overlay.addEventListener('click', function(e) {
                if (e.target === this) {
                    self.hideVoteModal();
                }
            });
            
            // 创建弹窗容器
            var modal = document.createElement('div');
            modal.id = 'vote-system-modal';
            modal.className = 'vote-system-modal';
            
            // 应用样式
            modal.style.width = this.config.width;
            modal.style.maxWidth = '90%';
            modal.style.maxHeight = '90vh';
            modal.style.backgroundColor = 'white';
            modal.style.borderRadius = '12px';
            modal.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.2)';
            modal.style.overflow = 'hidden';
            modal.style.display = 'flex';
            modal.style.flexDirection = 'column';
            modal.style.transform = 'scale(0.95)';
            modal.style.opacity = '0';
            modal.style.transition = 'all 0.3s ease';
            
            // 创建关闭按钮
            var closeBtn = document.createElement('button');
            closeBtn.id = 'vote-system-close';
            closeBtn.className = 'vote-system-close';
            closeBtn.innerHTML = '<i class="fa-solid fa-times"></i>';
            
            // 应用样式
            closeBtn.style.position = 'absolute';
            closeBtn.style.top = '15px';
            closeBtn.style.right = '15px';
            closeBtn.style.zIndex = '10001';
            closeBtn.style.width = '30px';
            closeBtn.style.height = '30px';
            closeBtn.style.border = 'none';
            closeBtn.style.borderRadius = '50%';
            closeBtn.style.backgroundColor = 'rgba(0, 0, 0, 0.1)';
            closeBtn.style.color = 'white';
            closeBtn.style.display = 'flex';
            closeBtn.style.alignItems = 'center';
            closeBtn.style.justifyContent = 'center';
            closeBtn.style.cursor = 'pointer';
            closeBtn.style.transition = 'all 0.2s ease';
            
            // 添加关闭按钮悬停效果
            closeBtn.addEventListener('mouseenter', function() {
                this.style.backgroundColor = 'rgba(0, 0, 0, 0.2)';
            });
            
            closeBtn.addEventListener('mouseleave', function() {
                this.style.backgroundColor = 'rgba(0, 0, 0, 0.1)';
            });
            
            // 添加关闭事件
            closeBtn.addEventListener('click', function() {
                self.hideVoteModal();
            });
            
            // 创建内容容器
            var contentContainer = document.createElement('div');
            contentContainer.id = 'vote-system-content';
            contentContainer.className = 'vote-system-content';
            
            // 应用样式
            contentContainer.style.flexGrow = '1';
            contentContainer.style.overflowY = 'auto';
            
            // 添加元素到DOM
            modal.appendChild(closeBtn);
            modal.appendChild(contentContainer);
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            // 使用iframe加载投票内容
            var iframe = document.createElement('iframe');
            iframe.src = this.config.baseUrl + '/index.php?embed=1';
            iframe.style.width = '100%';
            iframe.style.minHeight = '500px';
            iframe.style.border = 'none';
            iframe.style.display = 'block';
            
            // 添加加载指示器
            var loader = document.createElement('div');
            loader.className = 'vote-system-loader';
            loader.style.position = 'absolute';
            loader.style.top = '0';
            loader.style.left = '0';
            loader.style.width = '100%';
            loader.style.height = '100%';
            loader.style.backgroundColor = 'white';
            loader.style.display = 'flex';
            loader.style.alignItems = 'center';
            loader.style.justifyContent = 'center';
            
            var spinner = document.createElement('div');
            spinner.className = 'vote-system-spinner';
            spinner.style.width = '40px';
            spinner.style.height = '40px';
            spinner.style.border = '3px solid #f3f3f3';
            spinner.style.borderTop = '3px solid #165DFF';
            spinner.style.borderRadius = '50%';
            spinner.style.animation = 'spin 1s linear infinite';
            
            loader.appendChild(spinner);
            contentContainer.appendChild(loader);
            
            // 添加加载完成事件
            iframe.onload = function() {
                loader.style.display = 'none';
                self.loaded = true;
                self.loading = false;
                self.displayModal();
            };
            
            // 添加到内容容器
            contentContainer.appendChild(iframe);
        },
        
        // 显示模态框
        displayModal: function() {
            var overlay = document.getElementById('vote-system-overlay');
            var modal = document.getElementById('vote-system-modal');
            
            if (overlay && modal) {
                overlay.style.display = 'flex';
                
                // 触发重排
                setTimeout(function() {
                    modal.style.transform = 'scale(1)';
                    modal.style.opacity = '1';
                }, 10);
            }
        },
        
        // 隐藏投票弹窗
        hideVoteModal: function() {
            var overlay = document.getElementById('vote-system-overlay');
            var modal = document.getElementById('vote-system-modal');
            
            if (overlay && modal) {
                modal.style.transform = 'scale(0.95)';
                modal.style.opacity = '0';
                
                setTimeout(function() {
                    overlay.style.display = 'none';
                }, 300);
            }
        }
    };
    
    // 添加CSS动画
    var style = document.createElement('style');
    style.type = 'text/tailwindcss';
    style.textContent = `
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
    
    // 添加初始化函数到全局作用域
    window.initVoteSystem = function(options) {
        window.VoteSystem.init(options);
    };
})();    