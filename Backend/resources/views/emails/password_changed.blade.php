<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mật khẩu đã được thay đổi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            background: #ffffff;
            margin: 40px auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .header {
            background: #007bff;
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }
        .header img {
            max-height: 50px;
            margin-bottom: 10px;
        }
        .content {
            padding: 30px;
            color: #333333;
        }
        .content h2 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .content p {
            line-height: 1.6;
            font-size: 15px;
        }
        .footer {
            background: #f4f6f8;
            padding: 15px;
            text-align: center;
            font-size: 13px;
            color: #777777;
        }
        .btn {
            display: inline-block;
            background: #007bff;
            color: #ffffff;
            padding: 12px 20px;
            margin-top: 20px;
            border-radius: 5px;
            text-decoration: none;
        }
        .btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Thông báo bảo mật</h1>
        </div>
        <div class="content">
            <h2>Xin chào {{ $user->name }},</h2>
            <p>Mật khẩu tài khoản của bạn vừa được thay đổi thành công vào lúc <strong>{{ now()->format('H:i d/m/Y') }}</strong>.</p>
            <p>Nếu bạn là người thực hiện, bạn có thể bỏ qua email này.</p>
            <p>Nếu bạn KHÔNG thực hiện thay đổi này, vui lòng <strong>ngay lập tức</strong> liên hệ với bộ phận hỗ trợ để bảo vệ tài khoản.</p>
            <a href="https://yourdomain.com/contact" class="btn">Liên hệ hỗ trợ</a>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Công ty của bạn. Mọi quyền được bảo lưu.
        </div>
    </div>
</body>
</html>
