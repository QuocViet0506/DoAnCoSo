<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Liên hệ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Liên hệ với chúng tôi</h2>
        <form action="actions/submit_contact.php" method="POST">
            <div class="mb-3">
                <label>Họ tên:</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Chủ đề:</label>
                <input type="text" name="subject" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Nội dung:</label>
                <textarea name="message" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Gửi liên hệ</button>
        </form>
    </div>
</body>
</html>
