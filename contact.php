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
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Liên hệ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: url('../assets/images/anh_giaotiep.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Overlay làm mờ ảnh nền */
        .overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 0;
        }

        .contact-card {
            position: relative;
            z-index: 1;
            max-width: 600px;
            width: 100%;
            padding: 2rem;
            border-radius: 1rem;
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(5px);
        }

        .form-label i {
            color: #0d6efd;
            margin-right: 6px;
        }
    </style>
</head>
<body>
    <div class="overlay"></div> <!-- Overlay mờ -->
    
    <div class="contact-card">
        <h3 class="text-center mb-4 text-primary">📬 Liên hệ với chúng tôi</h3>
        <form action="actions/submit_contact.php" method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-person-circle"></i>Họ tên</label>
                <input type="text" name="name" class="form-control" placeholder="Nhập họ tên..." required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-envelope"></i>Email</label>
                <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-tag"></i>Chủ đề</label>
                <input type="text" name="subject" class="form-control" placeholder="Vấn đề bạn gặp phải..." required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-chat-dots"></i>Nội dung</label>
                <textarea name="message" class="form-control" rows="5" placeholder="Nhập nội dung liên hệ..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Gửi liên hệ</button>
        </form>
    </div>
</body>
</html>
