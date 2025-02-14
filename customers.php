<?php
require 'config.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 获取当前用户ID
$current_user_id = $_SESSION['user_id'];

// 处理会话消息
if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success">'.$_SESSION['success'].'</div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger">'.$_SESSION['error'].'</div>';
    unset($_SESSION['error']);
}

// 搜索处理
$search = $_GET['search'] ?? '';
$where = ["c.user_id = ?"];
$params = [$current_user_id];

if (!empty($search)) {
    $where[] = "(c.name LIKE ? OR c.phone LIKE ? OR c.project LIKE ?)";
    array_push($params, "%$search%", "%$search%", "%$search%");
}

$whereClause = implode(' AND ', $where);

// 分页处理
$perPage = 10;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $perPage;

// 获取总数
$countSql = "SELECT COUNT(*) FROM customers c WHERE $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$total = $stmt->fetchColumn();

// 获取数据
$sql = "SELECT 
            c.*, 
            u.username as created_name,
            u2.username as updated_name 
        FROM customers c
        LEFT JOIN users u ON c.created_by = u.id
        LEFT JOIN users u2 ON c.updated_by = u2.id
        WHERE $whereClause
        ORDER BY c.created_at DESC
        LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();
?>

<div class="container mt-4">
    <div class="card shadow">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>客户列表</h3>
                <a href="customer_add.php" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 新增客户
                </a>
            </div>

            <!-- 搜索表单 -->
            <form class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" 
                           name="search" 
                           placeholder="搜索客户..."
                           value="<?= htmlspecialchars($search) ?>">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>

            <!-- 客户表格 -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>客户名称</th>
                            <th>联系电话</th>
                            <th>所属楼盘</th>
                            <th>创建人</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['name']) ?></td>
                            <td><?= htmlspecialchars($c['phone']) ?></td>
                            <td><?= htmlspecialchars($c['project']) ?></td>
                            <td><?= htmlspecialchars($c['created_name']) ?></td>
                            <td>
                                <a href="customer_edit.php?id=<?= $c['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                   <i class="bi bi-pencil"></i>
                                </a>
                                <form method="post" 
                                      action="customer_delete.php" 
                                      class="d-inline"
                                      onsubmit="return confirm('确定删除？')">
                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <?php if ($total > $perPage): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= ceil($total / $perPage); $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                        <a class="page-link" 
                           href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require 'footer.php'; ?>