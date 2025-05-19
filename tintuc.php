<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//Hà Lê Quốc Việt 2280603661
require_once("config/config.php"); // Kết nối CSDL

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: Dangnhap/login.php");
    exit();
}

// Lấy thông tin người dùng
$user_id = $_SESSION['user_id'] ?? null; // Gán mặc định là null nếu không tồn tại
if ($user_id === null) {
    die("Không tìm thấy user_id trong session. Vui lòng đăng nhập lại.");
}

$stmt = $pdo->prepare("SELECT full_name, role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    die("Người dùng không tồn tại.");
}
$full_name = htmlspecialchars($user['full_name']);
$role = $user['role'];

$message = '';

// Đăng tin tức
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_news'])) {
    $title = trim($_POST['title']);
    $content = $_POST['content']; // Nội dung từ TinyMCE (bao gồm ảnh Base64)
    $image_path = null;

    // Xử lý upload ảnh (nếu có)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = './Uploads/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = $dest_path;
            } else {
                $message = "Lỗi khi tải ảnh lên.";
            }
        } else {
            $message = "Chỉ chấp nhận các định dạng ảnh: jpg, jpeg, png, gif.";
        }
    }

    // Thêm tin vào bảng news
    if (empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO news (user_id, title, content, image, posted_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$user_id, $title, $content, $image_path]);
        $message = "Tin tức đã được đăng thành công!";
    }
}

// Gửi bình luận
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    $news_id = intval($_POST['news_id']);
    $comment = trim($_POST['comment']);
    if ($comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO news_comments (news_id, user_id, comment, commented_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$news_id, $user_id, $comment]);
    }
}

// Like / Dislike
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rating'])) {
    $news_id = intval($_POST['news_id']);
    $vote = ($_POST['submit_rating'] === 'like') ? 1 : 0;

    // Kiểm tra user đã vote chưa
    $stmt = $pdo->prepare("SELECT * FROM news_votes WHERE news_id = ? AND user_id = ?");
    $stmt->execute([$news_id, $user_id]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        // Cập nhật vote dựa trên vote_id
        $stmt = $pdo->prepare("UPDATE news_votes SET vote = ?, voted_at = NOW() WHERE vote_id = ?");
        $stmt->execute([$vote, $exists['vote_id']]);
    } else {
        // Thêm mới vote
        $stmt = $pdo->prepare("INSERT INTO news_votes (news_id, user_id, vote, voted_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$news_id, $user_id, $vote]);
    }
}

// Lấy danh sách tin tức cùng tên người đăng
$news_query = $pdo->prepare("
    SELECT n.*, u.full_name 
    FROM news n 
    JOIN users u ON n.user_id = u.user_id 
    ORDER BY n.posted_at DESC
");
$news_query->execute();
$news_list = $news_query->fetchAll(PDO::FETCH_ASSOC);

// Hàm lấy bình luận của tin tức
function get_comments($pdo, $news_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.full_name 
        FROM news_comments c 
        JOIN users u ON c.user_id = u.user_id 
        WHERE c.news_id = ? 
        ORDER BY c.commented_at ASC
    ");
    $stmt->execute([$news_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Hàm đếm like/dislike
function count_ratings($pdo, $news_id) {
    $stmt = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN vote = 1 THEN 1 ELSE 0 END) AS likes,
            SUM(CASE WHEN vote = 0 THEN 1 ELSE 0 END) AS dislikes
        FROM news_votes 
        WHERE news_id = ?
    ");
    $stmt->execute([$news_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tin tức</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- TinyMCE CDN -->
    <script src="https://cdn.tinymce.com/4/tinymce.min.js"></script>
    <script>
        tinymce.init({
            selector: 'textarea[name="content"]',
            plugins: 'image paste',
            toolbar: 'undo redo | bold italic | alignleft aligncenter alignright | image',
            paste_data_images: true, // Cho phép dán ảnh trực tiếp
            height: 300,
            content_style: "body { font-family: Arial; font-size: 16px; }"
        });
    </script>
    <style>
        body {
            background: url('assets/images/Tp_HoChiMinh.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: rgba(0, 0, 0, 0.5);
            background-blend-mode: overlay;
        }
        .content-container {
            background-color: transparent; /* Khung trong suốt */
            backdrop-filter: blur(15px); /* Hiệu ứng blur */
        }
        .text-large-white {
            font-size: 2.5rem; /* Kích thước chữ lớn */
            color: white; /* Màu trắng */
        }
    </style>
</head>
<body class="min-vh-100 d-flex align-items-center justify-content-center text-dark">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 bg-transparent rounded-lg shadow-lg p-5 content-container">
                <!-- Nút quay lại trang chính -->
                <a href="index.php" class="btn btn-primary mb-4">
                    Quay lại trang chính
                </a>

                <!-- Thông tin người dùng -->
                <h2 class="mb-4 text-large-white">
                    Xin chào, <?= $full_name ?> (<?= ucfirst($role) ?>)
                </h2>

                <!-- Form đăng tin -->
                <div class="card mb-5">
                    <div class="card-body">
                        <h3 class="card-title mb-4 text-large-white">Đăng tin mới</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <input 
                                    type="text" 
                                    name="title" 
                                    class="form-control" 
                                    placeholder="Tiêu đề" 
                                    required
                                >
                            </div>
                            <div class="mb-3">
                                <textarea 
                                    name="content" 
                                    class="form-control" 
                                    placeholder="Nội dung" 
                                    rows="4" 
                                    required
                                ></textarea>
                            </div>
                            <div class="mb-3">
                                <input 
                                    type="file" 
                                    name="image" 
                                    class="form-control"
                                >
                            </div>
                            <button 
                                type="submit" 
                                name="submit_news" 
                                class="btn btn-success"
                            >
                                Đăng
                            </button>
                        </form>
                        <?php if ($message): ?>
                            <p class="mt-3 text-success"><?= $message ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Danh sách tin tức -->
                <h3 class="mb-4 text-large-white">Tin tức gần đây</h3>
                <?php foreach ($news_list as $news): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title text-large-white"><?= htmlspecialchars($news['title']) ?></h4>
                            <div class="card-text"><?php echo $news['content']; // Hiển thị nội dung HTML (bao gồm ảnh Base64) ?></div>
                            <?php if ($news['image']): ?>
                                <img 
                                    src="<?= htmlspecialchars($news['image']) ?>" 
                                    alt="Ảnh tin tức" 
                                    class="img-fluid rounded my-3" 
                                    style="max-width: 200px;"
                                >
                            <?php endif; ?>
                            <p class="text-muted">
                                <strong>Người đăng:</strong> <?= htmlspecialchars($news['full_name']) ?>
                            </p>

                            <!-- Like/Dislike -->
                            <?php $ratings = count_ratings($pdo, $news['news_id']); ?>
                            <p class="text-muted">
                                👍 <?= $ratings['likes'] ?? 0 ?> | 👎 <?= $ratings['dislikes'] ?? 0 ?>
                            </p>
                            <form method="POST" class="mb-3">
                                <input type="hidden" name="news_id" value="<?= $news['news_id'] ?>">
                                <button 
                                    type="submit" 
                                    name="submit_rating" 
                                    value="like" 
                                    class="btn btn-outline-primary me-2"
                                >
                                    👍 Thích
                                </button>
                                <button 
                                    type="submit" 
                                    name="submit_rating" 
                                    value="dislike" 
                                    class="btn btn-outline-danger"
                                >
                                    👎 Không thích
                                </button>
                            </form>

                            <!-- Bình luận -->
                            <div>
                                <strong class="text-large-white">Bình luận:</strong>
                                <?php
                                $comments = get_comments($pdo, $news['news_id']);
                                foreach ($comments as $c):
                                ?>
                                    <p class="mt-2">
                                        <strong><?= htmlspecialchars($c['full_name']) ?>:</strong> 
                                        <?= htmlspecialchars($c['comment']) ?>
                                    </p>
                                <?php endforeach; ?>
                                <form method="POST" class="mt-3 d-flex gap-2">
                                    <input 
                                        type="hidden" 
                                        name="news_id" 
                                        value="<?= $news['news_id'] ?>"
                                    >
                                    <input 
                                        type="text" 
                                        name="comment" 
                                        class="form-control" 
                                        placeholder="Viết bình luận..." 
                                        required
                                    >
                                    <button 
                                        type="submit" 
                                        name="submit_comment" 
                                        class="btn btn-primary"
                                    >
                                        Gửi
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>