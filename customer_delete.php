<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("仅允许POST请求");
}

// 验证CSRF令牌
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("无效请求");
}

$customer_id = $_POST['id'] ?? null;

if (!$customer_id) {
    $_SESSION['error'] = "无效的客户ID";
    header("Location: customers.php");
    exit;
}

try {
    $stmt = $pdo->prepare("
        DELETE FROM customers 
        WHERE id = ? AND user_id = ?  -- 添加用户ID验证
    ");
    $stmt->execute([$customer_id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "客户删除成功";
    } else {
        $_SESSION['error'] = "删除失败或无权操作";
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "删除失败：" . $e->getMessage();
}

header("Location: customers.php");
exit;
?>