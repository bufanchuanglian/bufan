<?php
require 'config.php';

// 已登录用户跳转
if (isset($_SESSION['user_id'])) {
    header("Location: calculator.php");
    exit;
}

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("无效请求");
    }

    // 获取输入数据
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // 验证用户名
    if (empty($username)) {
        $errors['username'] = '请输入用户名';
    } elseif (strlen($username) < 4) {
        $errors['username'] = '用户名至少4个字符';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors['username'] = '只能包含字母、数字和下划线';
    } else {
        // 检查用户名是否已存在
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors['username'] = '该用户名已被注册';
        }
    }

    // 验证密码
    if (empty($password)) {
        $errors['password'] = '请输入密码';
    } elseif (strlen($password) < 6) {
        $errors['password'] = '密码至少6个字符';
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = '两次输入的密码不一致';
    }

    // 无错误时创建用户
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);
            
            $_SESSION['registration_success'] = true;
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "注册失败：" . $e->getMessage();
        }
    }
}

require 'header.php';
?>

<div class="container mt-4 min-vh-100">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-person-plus"></i> 用户注册
                    </h2>

                    <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <?php foreach ($errors as $error): ?>
                        <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <form method="post" id="registrationForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <!-- 用户名输入 -->
                        <div class="mb-3">
                            <label class="form-label">用户名</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <input type="text" 
                                       class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                                       name="username" 
                                       value="<?= htmlspecialchars($username) ?>" 
                                       required
                                       minlength="4"
                                       pattern="[a-zA-Z0-9_]+">
                            </div>
                            <?php if(isset($errors['username'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['username'] ?></div>
                            <?php else: ?>
                                <small class="form-text text-muted">4-20位字母、数字或下划线</small>
                            <?php endif; ?>
                        </div>

                        <!-- 密码输入 -->
                        <div class="mb-3">
                            <label class="form-label">密码</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" 
                                       class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                                       name="password" 
                                       required
                                       minlength="6">
                            </div>
                            <?php if(isset($errors['password'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['password'] ?></div>
                            <?php else: ?>
                                <small class="form-text text-muted">至少6位字符</small>
                            <?php endif; ?>
                        </div>

                        <!-- 确认密码 -->
                        <div class="mb-4">
                            <label class="form-label">确认密码</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bi bi-lock-fill"></i>
                                </span>
                                <input type="password" 
                                       class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                                       name="password_confirm" 
                                       required>
                            </div>
                            <?php if(isset($errors['password_confirm'])): ?>
                                <div class="invalid-feedback d-block"><?= $errors['password_confirm'] ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check2-circle"></i> 立即注册
                            </button>
                        </div>

                        <div class="text-center mt-3">
                            已有账号？<a href="login.php" class="text-decoration-none">立即登录</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 客户端实时验证
document.getElementById('registrationForm').addEventListener('input', function(e) {
    const target = e.target;
    
    // 用户名过滤
    if (target.name === 'username') {
        target.value = target.value.replace(/[^a-zA-Z0-9_]/g, '');
    }
    
    // 密码长度提示
    if (target.name === 'password') {
        const feedback = document.querySelector('[name="password"] + .invalid-feedback');
        if (target.value.length < 6) {
            feedback.textContent = '密码至少6个字符';
        }
    }
});
</script>

<?php require 'footer.php'; ?>