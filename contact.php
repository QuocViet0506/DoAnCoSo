<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Liên hệ</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        body {
            background: url('assets/images/anh_lienhe.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .overlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 0;
        }

        .contact-card {
            position: relative;
            z-index: 1;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 1rem;
            padding: 2rem 2.5rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(6px);
        }

        .form-label i {
            color: #0d6efd;
            margin-right: 8px;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>
<body>
    <div class="overlay"></div>

    <div class="contact-card">
        <h3 class="text-center text-primary mb-4">
            <i class="bi bi-envelope-paper-heart-fill"></i> Liên hệ với chúng tôi
        </h3>
        <form action="actions/submit_contact.php" method="POST">
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-person-circle"></i>Họ tên</label>
                <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-envelope"></i>Email</label>
                <input type="email" name="email" class="form-control" placeholder="example@gmail.com" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-tag-fill"></i>Chủ đề</label>
                <input type="text" name="subject" class="form-control" placeholder="Bạn cần hỗ trợ gì?" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-chat-dots-fill"></i>Nội dung</label>
                <textarea name="message" class="form-control" rows="4" placeholder="Nhập nội dung liên hệ..." required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Gửi liên hệ</button>
        </form>
    </div>
</body>
</html>
