</div> <!-- 关闭header中开启的main容器 -->

<footer class="footer mt-auto py-4 bg-light border-top">
    <div class="container">
        <div class="row g-4">
            <!-- 系统信息 -->
            <div class="col-md-4">
                <h5 class="mb-3">
                    <i class="bi bi-info-circle text-primary"></i> 系统信息
                </h5>
                <ul class="list-unstyled">
                    <li>版本：v2.1.0</li>
                    <li>当前用户：<?= htmlspecialchars($_SESSION['username'] ?? '未登录') ?></li>
                    <li>服务器时间：<?= date('Y-m-d H:i:s') ?></li>
                </ul>
            </div>
            
            <!-- 快速链接 -->
            <div class="col-md-4">
                <h5 class="mb-3">
                    <i class="bi bi-link-45deg text-success"></i> 快速链接
                </h5>
                <div class="row">
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li><a href="calculator.php" class="text-decoration-none">韩折计算器</a></li>
                            <li><a href="customers.php" class="text-decoration-none">客户列表</a></li>
                            <li><a href="index.php" class="text-decoration-none">系统首页</a></li>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="list-unstyled">
                            <li><a href="login.php" class="text-decoration-none">用户登录</a></li>
                            <li><a href="register.php" class="text-decoration-none">用户注册</a></li>
                            <li><a href="#" class="text-decoration-none">帮助文档</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- 联系方式 -->
            <div class="col-md-4">
                <h5 class="mb-3">
                    <i class="bi bi-envelope text-danger"></i> 联系我们
                </h5>
                <ul class="list-unstyled">
                    <li><i class="bi bi-telephone me-2"></i> 400-888-8888</li>
                    <li><i class="bi bi-whatsapp me-2"></i> 138-8888-8888</li>
                    <li><i class="bi bi-geo-alt me-2"></i> 上海市浦东新区</li>
                </ul>
            </div>
        </div>

        <div class="text-center mt-4 pt-3 border-top">
            <p class="mb-0">
                &copy; 2020-<?= date('Y') ?> 窗帘管理系统 
                <span class="text-muted mx-2">|</span>
                <a href="#" class="text-decoration-none">隐私政策</a>
                <span class="text-muted mx-2">|</span>
                <a href="#" class="text-decoration-none">服务条款</a>
            </p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.bootcdn.net/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<!-- 自定义脚本 -->
<script>
// 工具提示初始化
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
})
</script>

</body>
</html>