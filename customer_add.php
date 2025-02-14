<?php
// 第一步：包含配置文件和启动会话
require 'config.php'; // 包含数据库连接和CSRF函数

// 第二步：检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // 未登录跳转到登录页
    exit;
}

// 第三步：初始化变量
$errors = [];          // 存储错误信息
$defaultValues = [     // 表单默认值
    'name'          => '',
    'project'       => '',
    'room_number'   => '',
    'phone'         => '',
    'total_amount'  => '',
    'paid_amount'   => '',
    'payment_channel' => '支付宝' // 默认付款方式
];

// 第四步：处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 4.1 验证CSRF令牌防止跨站请求伪造
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("安全验证失败，请刷新页面重试");
    }

    // 4.2 接收并清理表单数据
    $formData = [
        'name'          => trim($_POST['name'] ?? ''),
        'project'       => trim($_POST['project'] ?? ''),
        'room_number'   => trim($_POST['room_number'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'total_amount'  => trim($_POST['total_amount'] ?? ''),
        'paid_amount'   => trim($_POST['paid_amount'] ?? '0'),
        'payment_channel' => $_POST['payment_channel'] ?? '支付宝'
    ];

    // 4.3 验证必填字段
    $requiredFields = ['name', 'project', 'room_number', 'phone', 'total_amount'];
    foreach ($requiredFields as $field) {
        if (empty($formData[$field])) {
            $errors[$field] = "请填写此项内容";
        }
    }

    // 4.4 验证手机号格式
    if (!preg_match('/^1[3-9]\d{9}$/', $formData['phone'])) {
        $errors['phone'] = "请输入正确的手机号码（11位数字）";
    }

    // 4.5 验证金额格式
    if (!is_numeric($formData['total_amount']) || $formData['total_amount'] <= 0) {
        $errors['total_amount'] = "总金额必须大于0";
    }
    if ($formData['paid_amount'] !== '' && (!is_numeric($formData['paid_amount']) || $formData['paid_amount'] < 0)) {
        $errors['paid_amount'] = "已付金额格式不正确";
    }

    // 4.6 如果没有错误，保存到数据库
    if (empty($errors)) {
        try {
            // 开始数据库事务
            $pdo->beginTransaction();

            // 准备SQL语句
            $sql = "INSERT INTO customers (
                        user_id, name, project, room_number, phone, 
                        total_amount, paid_amount, payment_channel, 
                        created_by, updated_by
                    ) VALUES (
                        ?, ?, ?, ?, ?, 
                        ?, ?, ?, 
                        ?, ?
                    )";
            $stmt = $pdo->prepare($sql);
            
            // 执行插入操作
            $stmt->execute([
                $_SESSION['user_id'], // 当前用户ID
                $formData['name'],
                $formData['project'],
                $formData['room_number'],
                $formData['phone'],
                $formData['total_amount'],
                $formData['paid_amount'],
                $formData['payment_channel'],
                $_SESSION['user_id'], // 创建人
                $_SESSION['user_id']  // 更新人
            ]);

            // 提交事务
            $pdo->commit();

            // 成功后跳转并显示成功提示
            $_SESSION['success'] = "客户添加成功！";
            header("Location: customers.php");
            exit;

        } catch (PDOException $e) {
            // 出错时回滚事务
            $pdo->rollBack();
            $errors[] = "保存失败，请稍后重试。错误代码：" . $e->getCode();
        }
    }
}

// 第五步：包含网页头部
require 'header.php';
?>

<!-- 第六步：显示网页内容 -->
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="text-center mb-4">
                <i class="bi bi-person-plus"></i> 添加新客户
            </h2>

            <!-- 错误提示 -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- 客户表单 -->
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="row g-3">
                    <!-- 客户姓名 -->
                    <div class="col-md-6">
                        <label class="form-label">客户姓名 <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                               name="name" 
                               value="<?= htmlspecialchars($formData['name'] ?? '') ?>"
                               required
                               placeholder="请输入客户全名">
                        <?php if(isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= $errors['name'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- 联系电话 -->
                    <div class="col-md-6">
                        <label class="form-label">联系电话 <span class="text-danger">*</span></label>
                        <input type="tel" 
                               class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                               name="phone" 
                               value="<?= htmlspecialchars($formData['phone'] ?? '') ?>"
                               required
                               placeholder="11位手机号码">
                        <?php if(isset($errors['phone'])): ?>
                        <div class="invalid-feedback"><?= $errors['phone'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- 楼盘信息 -->
                    <div class="col-md-6">
                        <label class="form-label">楼盘名称 <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= isset($errors['project']) ? 'is-invalid' : '' ?>" 
                               name="project" 
                               value="<?= htmlspecialchars($formData['project'] ?? '') ?>"
                               required
                               placeholder="例如：万科城市花园">
                    </div>

                    <!-- 房间号 -->
                    <div class="col-md-6">
                        <label class="form-label">房间号 <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= isset($errors['room_number']) ? 'is-invalid' : '' ?>" 
                               name="room_number" 
                               value="<?= htmlspecialchars($formData['room_number'] ?? '') ?>"
                               required
                               placeholder="例如：8栋202室">
                    </div>

                    <!-- 总金额 -->
                    <div class="col-md-6">
                        <label class="form-label">总金额（元） <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" 
                                   step="0.01"
                                   class="form-control <?= isset($errors['total_amount']) ? 'is-invalid' : '' ?>" 
                                   name="total_amount" 
                                   value="<?= htmlspecialchars($formData['total_amount'] ?? '') ?>"
                                   required
                                   placeholder="请输入数字">
                        </div>
                        <?php if(isset($errors['total_amount'])): ?>
                        <div class="invalid-feedback"><?= $errors['total_amount'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- 已付金额 -->
                    <div class="col-md-6">
                        <label class="form-label">已付金额（元）</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" 
                                   step="0.01"
                                   class="form-control <?= isset($errors['paid_amount']) ? 'is-invalid' : '' ?>" 
                                   name="paid_amount" 
                                   value="<?= htmlspecialchars($formData['paid_amount'] ?? '') ?>"
                                   placeholder="留空表示未付款">
                        </div>
                        <?php if(isset($errors['paid_amount'])): ?>
                        <div class="invalid-feedback"><?= $errors['paid_amount'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- 付款方式 -->
                    <div class="col-12">
                        <label class="form-label">付款方式</label>
                        <select class="form-select" name="payment_channel">
                            <option value="支付宝" <?= ($formData['payment_channel'] ?? '') == '支付宝' ? 'selected' : '' ?>>支付宝</option>
                            <option value="微信" <?= ($formData['payment_channel'] ?? '') == '微信' ? 'selected' : '' ?>>微信</option>
                            <option value="银行转账" <?= ($formData['payment_channel'] ?? '') == '银行转账' ? 'selected' : '' ?>>银行转账</option>
                            <option value="现金" <?= ($formData['payment_channel'] ?? '') == '现金' ? 'selected' : '' ?>>现金</option>
                        </select>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-check2-circle"></i> 确认添加
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// 第七步：包含网页尾部
require 'footer.php'; 
?>