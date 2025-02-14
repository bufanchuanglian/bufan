<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: calculator.php");
    exit;
}

require 'header.php';
?>

<div class="container">
    <div class="hero-section text-center py-5 mb-5">
        <div class="gradient-bg"></div>
        <h1 class="display-4 fw-bold text-white mb-3">窗帘韩折智能计算系统</h1>
        <p class="lead text-white opacity-75 mb-4">专业窗帘加工计算解决方案</p>
        <div class="cta-buttons">
            <a href="login.php" class="btn btn-light btn-lg px-5 me-3">
                <i class="bi bi-box-arrow-in-right"></i> 立即登录
            </a>
            <a href="register.php" class="btn btn-outline-light btn-lg px-5">
                <i class="bi bi-person-plus"></i> 新用户注册
            </a>
        </div>
    </div>

    <div class="features-section row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 shadow-lg hover-effect">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-primary mb-3">
                        <i class="bi bi-calculator text-white fs-2"></i>
                    </div>
                    <h3 class="card-title mb-3">智能计算</h3>
                    <p class="card-text text-muted">三种方案对比计算，自动优化参数组合</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 shadow-lg hover-effect">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-success mb-3">
                        <i class="bi bi-graph-up text-white fs-2"></i>
                    </div>
                    <h3 class="card-title mb-3">数据可视化</h3>
                    <p class="card-text text-muted">清晰图表展示计算结果与公式推导</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 shadow-lg hover-effect">
                <div class="card-body text-center">
                    <div class="icon-wrapper bg-info mb-3">
                        <i class="bi bi-shield-lock text-white fs-2"></i>
                    </div>
                    <h3 class="card-title mb-3">安全存储</h3>
                    <p class="card-text text-muted">加密存储计算记录，保障数据安全</p>
                </div>
            </div>
        </div>
    </div>

    <div class="guide-section bg-white rounded-4 shadow p-5 mb-5">
        <h2 class="text-center mb-5">三步轻松完成计算</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <h4 class="my-3">登录系统</h4>
                    <p class="text-muted">使用注册账号或立即注册新账号</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">2</div>
                    <h4 class="my-3">输入参数</h4>
                    <p class="text-muted">填写窗帘尺寸相关基础数据</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">3</div>
                    <h4 class="my-3">获取结果</h4>
                    <p class="text-muted">查看详细计算结果与优化方案</p>
                </div>
            </div>
        </div>
    </div>

    <div class="demo-section bg-light rounded-4 p-5 text-center">
        <h3 class="mb-4">立即体验计算演示</h3>
        <div class="ratio ratio-16x9 demo-video shadow-lg">
            <iframe src="https://www.youtube.com/embed/EXAMPLE_VIDEO" 
                    title="系统演示视频" 
                    allowfullscreen></iframe>
        </div>
    </div>
</div>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.hero-section {
    position: relative;
    background: var(--primary-gradient);
    border-radius: 1rem;
    padding: 4rem 2rem;
    overflow: hidden;
    margin-top: 2rem;
}

.gradient-bg {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: var(--primary-gradient);
    z-index: 0;
    animation: gradientRotate 20s linear infinite;
}

@keyframes gradientRotate {
    100% { transform: rotate(360deg); }
}

.hover-effect {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.hover-effect:hover {
    transform: translateY(-5px);
    box-shadow: 0 1rem 3rem rgba(0,0,0,0.15) !important;
}

.icon-wrapper {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.step-card {
    position: relative;
    padding: 2rem;
    background: white;
    border-radius: 1rem;
    text-align: center;
    height: 100%;
}

.step-number {
    width: 50px;
    height: 50px;
    background: var(--primary-gradient);
    color: white;
    border-radius: 50%;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 50px;
    margin: 0 auto 1rem;
}

.demo-video {
    border-radius: 1rem;
    overflow: hidden;
    background: #000;
}

@media (max-width: 768px) {
    .hero-section {
        padding: 2rem 1rem;
    }
    
    .cta-buttons .btn {
        width: 100%;
        margin-bottom: 1rem;
    }
    
    .features-section .col-md-4 {
        margin-bottom: 1.5rem;
    }
    
    .guide-section {
        padding: 2rem 1rem;
    }
}
</style>

<?php require 'footer.php'; ?>