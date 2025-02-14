<?php
require 'config.php';
require 'header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$input = [];
$errors = [];
$results = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("无效的请求");
    }

    // 输入验证
    $fields = [
        'finished_width' => ['name' => '成品宽', 'min' => 1],
        'fabric_width' => ['name' => '布料宽', 'min' => 15],
        'folds' => ['name' => '折个数', 'min' => 2],
        'extension' => ['name' => '扩展量', 'min' => 0]
    ];

    foreach ($fields as $field => $config) {
        $value = $_POST[$field] ?? '';
        if (!is_numeric($value)) {
            $errors[$field] = "{$config['name']}必须为数字";
        } elseif ($value < $config['min']) {
            $errors[$field] = "{$config['name']}不能小于{$config['min']}";
        } else {
            $input[$field] = (float)$value;
        }
    }

    if (empty($errors)) {
        $end_distance = 14;
        $possible_folds = array_unique([
            max(2, $input['folds'] - 1),
            $input['folds'],
            $input['folds'] + 1
        ]);

        foreach ($possible_folds as $f) {
            $available_material = $input['fabric_width'] - $end_distance;
            $actual_width = $input['finished_width'] + $input['extension'];
            $total_fold_usage = $available_material - $actual_width;

            if ($total_fold_usage <= 0) continue;

            $fold_size = $total_fold_usage / $f;
            $fold_spacing = ($f > 1) ? ($actual_width / ($f - 1)) : 0;

            $results[] = [
                'folds' => $f,
                'fold_size' => round($fold_size, 1),
                'fold_spacing' => round($fold_spacing, 1),
                'actual_width' => round($actual_width, 1),
                'available_material' => round($available_material, 1)
            ];
        }
    }
}
?>

<div class="container form-container">
    <div class="card shadow">
        <div class="card-body">
            <h3 class="mb-4">窗帘韩折计算器</h3>
            
            <form method="post" class="calculation-form">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">成品宽（cm）</label>
                        <input type="number" step="0.1" class="form-control <?= isset($errors['finished_width']) ? 'is-invalid' : '' ?>" 
                               name="finished_width" value="<?= htmlspecialchars($_POST['finished_width'] ?? '') ?>" required>
                        <?php if(isset($errors['finished_width'])): ?>
                        <div class="invalid-feedback"><?= $errors['finished_width'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">布料宽（cm）</label>
                        <input type="number" step="0.1" class="form-control <?= isset($errors['fabric_width']) ? 'is-invalid' : '' ?>" 
                               name="fabric_width" value="<?= htmlspecialchars($_POST['fabric_width'] ?? '') ?>" required>
                        <?php if(isset($errors['fabric_width'])): ?>
                        <div class="invalid-feedback"><?= $errors['fabric_width'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">折个数（≥2）</label>
                        <input type="number" class="form-control <?= isset($errors['folds']) ? 'is-invalid' : '' ?>" 
                               name="folds" value="<?= htmlspecialchars($_POST['folds'] ?? '') ?>" required>
                        <?php if(isset($errors['folds'])): ?>
                        <div class="invalid-feedback"><?= $errors['folds'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label">扩展量（cm）</label>
                        <input type="number" step="0.1" class="form-control <?= isset($errors['extension']) ? 'is-invalid' : '' ?>" 
                               name="extension" value="<?= htmlspecialchars($_POST['extension'] ?? '') ?>" required>
                        <?php if(isset($errors['extension'])): ?>
                        <div class="invalid-feedback"><?= $errors['extension'] ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-12">
                        <div class="alert alert-warning py-2">
                            <i class="bi bi-info-circle me-2"></i>
                            固定端距：<strong>14cm</strong>（已包含在计算中）
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="bi bi-calculator me-2"></i>开始计算
                </button>
            </form>

            <?php if (!empty($results)): ?>
            <div class="mt-5">
                <h4 class="mb-4">计算结果</h4>
                
                <!-- 竖排结果展示 -->
                <div class="calculation-results bg-light rounded-3 p-3">
                    <?php foreach ($results as $index => $result): ?>
                    <div class="result-item bg-white p-3 mb-3 rounded shadow-sm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center h-100">
                                    <span class="badge bg-primary fs-6">方案 <?= $index+1 ?></span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">折个数：</span>
                                            <span class="fw-bold text-primary"><?= $result['folds'] ?>个</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">单折用料：</span>
                                            <span class="fw-bold text-success"><?= $result['fold_size'] ?>cm</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">折间距：</span>
                                            <span class="fw-bold text-danger"><?= $result['fold_spacing'] ?>cm</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">总用料：</span>
                                            <span class="fw-bold"><?= $result['available_material'] ?>cm</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- 计算公式说明 -->
                <div class="formula-explain mt-4 p-4 bg-white rounded shadow">
                    <h5 class="mb-3">计算过程说明</h5>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="formula-step">
                                <div class="formula-header text-primary mb-2">基础参数</div>
                                <div class="d-flex justify-content-between border-bottom pb-2">
                                    <span>可用料 = 布料宽 - 端距</span>
                                    <span><?= $input['fabric_width'] ?> - 14 = <?= $input['fabric_width'] -14 ?>cm</span>
                                </div>
                                <div class="d-flex justify-content-between border-bottom pb-2 mt-2">
                                    <span>实际宽度 = 成品宽 + 扩展量</span>
                                    <span><?= $input['finished_width'] ?> + <?= $input['extension'] ?> = <?= $input['finished_width'] + $input['extension'] ?>cm</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="formula-step">
                                <div class="formula-header text-success mb-2">计算步骤</div>
                                <div class="step-item">
                                    <span class="step-number">1</span>
                                    总折用料 = 可用料 - 实际宽度 = <?= ($input['fabric_width'] -14) - ($input['finished_width'] + $input['extension']) ?>cm
                                </div>
                                <div class="step-item">
                                    <span class="step-number">2</span>
                                    单折用料 = 总折用料 / 折个数
                                </div>
                                <div class="step-item">
                                    <span class="step-number">3</span>
                                    折间距 = 实际宽度 / (折个数 - 1)
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.calculation-results {
    border: 1px solid #dee2e6;
}

.result-item {
    transition: transform 0.2s;
}

.result-item:hover {
    transform: translateY(-2px);
}

.formula-explain {
    border: 1px solid #dee2e6;
}

.formula-header {
    font-weight: 600;
    font-size: 1.1rem;
}

.step-item {
    position: relative;
    padding-left: 35px;
    margin-bottom: 1rem;
}

.step-number {
    position: absolute;
    left: 0;
    top: 0;
    width: 25px;
    height: 25px;
    background: #0d6efd;
    color: white;
    border-radius: 50%;
    text-align: center;
    line-height: 25px;
    font-size: 0.9rem;
}

@media (max-width: 768px) {
    .result-item {
        padding: 1rem;
    }
    
    .formula-step {
        margin-bottom: 1.5rem;
    }
}
</style>

<?php require 'footer.php'; ?>