<?php
// 第一步：包含必要的文件
require 'config.php';  // 数据库连接和通用配置
require 'header.php';   // 网页头部内容

// 第二步：检查用户是否登录
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // 跳转到登录页
    exit;
}

// 第三步：初始化变量
$errors = [];        // 存储错误信息
$customer = [];      // 存储客户数据
$current_user_id = $_SESSION['user_id']; // 当前登录用户的ID

// 第四步：获取要编辑的客户ID
$customer_id = $_GET['id'] ?? 0; // 从URL获取客户ID

// 第五步：验证客户数据归属
if ($customer_id) {
    // 准备查询语句（确保只能访问自己的客户）
    $stmt = $pdo->prepare("
        SELECT * FROM customers 
        WHERE id = ?     -- 客户ID条件
        AND user_id = ?  -- 用户ID条件
    ");
    $stmt->execute([$customer_id, $current_user_id]);
    $customer = $stmt->fetch();

    // 如果查询不到数据，显示错误
    if (!$customer) {
        $_SESSION['error'] = "找不到该客户或无权编辑";
        header("Location: customers.php");
        exit;
    }
} else {
    // 没有传入ID时返回列表页
    header("Location: customers.php");
    exit;
}

// 第六步：处理表单提交（更新数据）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 6.1 验证CSRF令牌
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("安全验证失败，请刷新页面重试");
    }

    // 6.2 接收并清理表单数据
    $formData = [
        'name'          => trim($_POST['name'] ?? ''),
        'project'       => trim($_POST['project'] ?? ''),
        'room_number'   => trim($_POST['room_number'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'total_amount'  => trim($_POST['total_amount'] ?? ''),
        'paid_amount'   => trim($_POST['paid_amount'] ?? '0'),
        'payment_channel' => $_POST['payment_channel'] ?? '支付宝'
    ];

    // 6.3 数据验证
    // 必填字段验证
    $requiredFields = ['name', 'project', 'room_number', 'phone', 'total_amount'];
    foreach ($requiredFields as $field) {
        if (empty($formData[$field])) {
            $errors[$field] = "此项不能为空";
        }
    }

    // 手机号格式验证
    if (!preg_match('/^1[3-9]\d{9}$/', $formData['phone'])) {
        $errors['phone'] = "请输入11位有效手机号";
    }

    // 金额验证
    if (!is_numeric($formData['total_amount']) || $formData['total_amount'] <= 0) {
        $errors['total_amount'] = "总金额必须大于0";
    }
    if (!is_numeric($formData['paid_amount']) || $formData['paid_amount'] < 0) {
        $errors['paid_amount'] = "已付金额无效";
    }

    // 6.4 如果没有错误，更新数据库
    if (empty($errors)) {
        try {
            // 开始数据库事务
            $pdo->beginTransaction();

            // 准备更新语句
            $stmt = $pdo->prepare("
                UPDATE customers 
                SET 
                    name = ?,          -- 客户姓名
                    project = ?,       -- 楼盘名称
                    room_number = ?,   -- 房间号
                    phone = ?,         -- 联系电话
                    total_amount = ?,  -- 总金额
                    paid_amount = ?,   -- 已付金额
                    payment_channel = ?, -- 付款方式
                    updated_by = ?,    -- 最后修改人
                    updated_at = NOW() -- 最后修改时间
                WHERE 
                    id = ?            -- 客户ID条件
                    AND user_id = ?   -- 用户ID条件
            ");

            // 执行更新（参数顺序必须与SQL中的?顺序一致）
            $stmt->execute([
                $formData['name'],
                $formData['project'],
                $formData['room_number'],
                $formData['phone'],
                $formData['total_amount'],
                $formData['paid_amount'],
                $formData['payment_channel'],
                $current_user_id,  // 更新人
                $customer_id,      // WHERE条件：客户ID
                $current_user_id   // WHERE条件：用户ID
            ]);

            // 提交事务
            $pdo->commit();

            // 设置成功提示并跳转
            $_SESSION['success'] = "客户信息更新成功！";
            header("Location: customers.php");
            exit;

        } catch (PDOException $e) {
            // 出错时回滚
            $pdo->rollBack();
            $errors[] = "保存失败，错误代码：" . $e->getCode();
        }
    }
}

// 第七步：显示网页内容
?>
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-body">
            <h2 class="text-center mb-4">
                <i class="bi bi-pencil-square"></i> 编辑客户信息
            </h2>

            <!-- 错误提示 -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- 编辑表单 -->
            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="row g-3">
                    <!-- 客户姓名 -->
                    <div class="col-md-6">
                        <label class="form-label">客户姓名 <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" 
                               name="name" 
                               value="<?= htmlspecialchars($formData['name'] ?? $customer['name']) ?>" 
                               required>
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
                               value="<?= htmlspecialchars($formData['phone'] ?? $customer['phone']) ?>" 
                               required>
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
                               value="<?= htmlspecialchars($formData['project'] ?? $customer['project']) ?>" 
                               required>
                    </div>

                    <!-- 房间号 -->
                    <div class="col-md-6">
                        <label class="form-label">房间号 <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= isset($errors['room_number']) ? 'is-invalid' : '' ?>" 
                               name="room_number" 
                               value="<?= htmlspecialchars($formData['room_number'] ?? $customer['room_number']) ?>" 
                               required>
                    </div>

                    <!-- 金额信息 -->
                    <div class="col-md-6">
                        <label class="form-label">总金额（元） <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" 
                                   step="0.01"
                                   class="form-control <?= isset($errors['total_amount']) ? 'is-invalid' : '' ?>" 
                                   name="total_amount" 
                                   value="<?= htmlspecialchars($formData['total_amount'] ?? $customer['total_amount']) ?>" 
                                   required>
                        </div>
                        <?php if(isset($errors['total_amount'])): ?>
                        <div class="invalid-feedback"><?= $errors['total_amount'] ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">已付金额（元）</label>
                        <div class="input-group">
                            <span class="input-group-text">¥</span>
                            <input type="number" 
                                   step="0.01"
                                   class="form-control <?= isset($errors['paid_amount']) ? 'is-invalid' : '' ?>" 
                                   name="paid_amount" 
                                   value="<?= htmlspecialchars($formData['paid_amount'] ?? $customer['paid_amount']) ?>">
                        </div>
                        <?php if(isset($errors['paid_amount'])): ?>
                        <div class="invalid-feedback"><?= $errors['paid_amount'] ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- 付款方式 -->
                    <div class="col-12">
                        <label class="form-label">付款方式</label>
                        <select class="form-select" name="payment_channel">
                            <option value="支付宝" <?= ($formData['payment_channel'] ?? $customer['payment_channel']) == '支付宝' ? 'selected' : '' ?>>支付宝</option>
                            <option value="微信" <?= ($formData['payment_channel'] ?? $customer['payment_channel']) == '微信' ? 'selected' : '' ?>>微信</option>
                            <option value="银行转账" <?= ($formData['payment_channel'] ?? $customer['payment_channel']) == '银行转账' ? 'selected' : '' ?>>银行转账</option>
                            <option value="现金" <?= ($formData['payment_channel'] ?? $customer['payment_channel']) == '现金' ? 'selected' : '' ?>>现金</option>
                        </select>
                    </div>

                    <!-- 提交按钮 -->
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary w-100 btn-lg">
                            <i class="bi bi-check2-circle"></i> 确认修改
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// 第八步：包含网页尾部
require 'footer.php'; 
?>