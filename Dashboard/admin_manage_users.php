<?php
session_start();
require_once '../config/config.php';

// ✅ Chỉ cho phép admin truy cập
if ($_SESSION["role"] !== "admin") {
    exit("Truy cập bị từ chối.");
}

// ✅ Xử lý cập nhật trạng thái người dùng
if (isset($_GET['action']) && isset($_GET['id'])) {
    $new_status = $_GET['action'];
    $user_id = $_GET['id'];

    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE user_id = ?");
    if ($stmt->execute([$new_status, $user_id])) {
        header("Location: admin_manage_users.php");
        exit();
    } else {
        echo "<p>Lỗi cập nhật trạng thái người dùng.</p>";
    }
}

// ✅ Lấy danh sách người dùng
$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý người dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body style="background-color: #f8f9fa">
    <div class="container my-5">
        <h2 class="text-center mb-4"><i class="fas fa-users-cog me-2"></i>Quản lý người dùng</h2>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle table-bordered bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): 
                        $status = $u['status'] ?? 'active';
                        $is_active = $status === 'active';
                        $status_badge = $is_active 
                            ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Đã xác minh</span>' 
                            : '<span class="badge bg-danger"><i class="fas fa-lock me-1"></i> Bị khóa</span>';

                        $action_label = $is_active ? 'Khóa' : 'Mở khóa';
                        $toggle_status = $is_active ? 'inactive' : 'active';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td><span class="badge bg-info text-dark"><?= $u['auth_provider'] ?></span></td>
                        <td><?= $status_badge ?></td>
                        <td>
                            <a href="?action=<?= $toggle_status ?>&id=<?= $u['user_id'] ?>" class="btn btn-sm <?= $is_active ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                <i class="fas <?= $is_active ? 'fa-lock' : 'fa-unlock' ?>"></i> <?= $action_label ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
