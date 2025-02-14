
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>窗帘管理系统</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.bootcdn.net/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.bootcdn.net/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        .navbar-brand {
            font-weight: 600;
            letter-spacing: 1px;
        }
        .dropdown-menu {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .nav-link {
            transition: all 0.2s ease;
        }
        .navbar-nav .nav-item:hover .nav-link {
            color: var(--bs-primary) !important;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <!-- 品牌LOGO -->
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-house-door me-2"></i>窗帘管理系统
        </a>
        
        <!-- 移动端菜单按钮 -->
        <button class="navbar-toggler" type="button" 
                data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- 导航菜单 -->
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                <!-- 计算器 -->
                <li class="nav-item">
                    <a class="nav-link" href="calculator.php">
                        <i class="bi bi-calculator me-1"></i>韩折计算
                    </a>
                </li>
                
                <!-- 客户管理下拉菜单 -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-people me-1"></i>客户管理
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" href="customers.php">
                                <i class="bi bi-list-ul me-2"></i>客户列表
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="customer_add.php">
                                <i class="bi bi-person-add me-2"></i>新增客户
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
            
            <!-- 右侧菜单 -->
            <ul class="navbar-nav">
                <?php if(isset($_SESSION['user_id'])): ?>
                <!-- 用户菜单 -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" 
                       role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i><?= htmlspecialchars($_SESSION['username'] ?? '用户') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-gear me-2"></i>账户设置
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i>退出登录
                            </a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <!-- 登录/注册 -->
                <li class="nav-item">
                    <a class="nav-link" href="login.php">登录</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">注册</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- 主要内容容器 -->
<div class="container mt-4 min-vh-100">