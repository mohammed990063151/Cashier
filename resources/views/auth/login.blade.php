<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>Obiy | تسجيل الدخول</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Cairo:400,700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Cairo', sans-serif; }
        body { height: 100vh; display: flex; justify-content: center; align-items: center; background: #f5f8ff; }
        .container {
            display: flex;
            width: 95%;
            max-width: 1000px;
            min-height: 600px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border-radius: 15px;
            overflow: hidden;
            background: #fff;
        }
        .login-left, .login-right {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 2rem;
        }
        .login-left {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            text-align: center;
        }
        .login-left h2 { font-size: 2.2rem; margin-bottom: 1rem; font-weight: 700; }
        .login-left p { font-size: 1.1rem; margin-bottom: 2rem; }
        .login-left img { width: 200px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); background: #fff; padding: 10px; }

        .login-right { background: #fff; }
        .login-right h2 { margin-bottom: 2rem; color: #1e3c72; font-weight: 700; }
        .login-right form { width: 100%; max-width: 350px; }
        .form-group { margin-bottom: 1.5rem; position: relative; }
        .form-group input {
            width: 100%;
            padding: 12px 45px 12px 15px;
            border: 1px solid #ccc;
            border-radius: 30px;
            outline: none;
            transition: all 0.3s;
        }
        .form-group input:focus { border-color: #2a5298; box-shadow: 0 0 8px rgba(30,60,114,0.3); }
        .form-group i {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            color: #2a5298;
        }
        .btn {
            width: 100%;
            padding: 12px;
            border-radius: 30px;
            border: none;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: #fff;
            font-size: 1.1rem;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn:hover { background: linear-gradient(135deg, #2a5298, #1e3c72); }
        @media screen and (max-width: 900px) {
            .container { flex-direction: column; min-height: auto; }
            .login-left, .login-right { width: 100%; padding: 2rem 1rem; }
            .login-left img { width: 150px; margin: 1rem 0; }
        }
         .whatsapp-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #25d366;
        color: #fff;
        padding: 12px 20px;
        border-radius: 30px;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        transition: 0.3s;
        z-index: 999;
    }
    .whatsapp-btn:hover {
        background: #1ebd5a;
        transform: scale(1.05);
    }
    .whatsapp-btn i {
        font-size: 20px;
    }
    </style>
</head>
<body>

<div class="container">
    <!-- الجهة اليسرى -->
    <div class="login-left">
        <h2>مرحباً بك! {{ $setting->name }}</h2>
        <p>قم بتسجيل الدخول للوصول إلى لوحة التحكم الخاصة بك.</p>
   <img src="{{ asset($setting->logo) }}" alt="POS Image">


    </div>

    <!-- الجهة اليمنى -->
    <div class="login-right">
        <h2>تسجيل الدخول</h2>
        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="البريد الإلكتروني" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="كلمة المرور" required>
            </div>
            <div class="form-group" style="display: flex; align-items: center;">
                <input type="checkbox" name="remember" id="remember" style="margin-left: 10px;">
                <label for="remember">تذكرني</label>
            </div>
            <button type="submit" class="btn">دخول</button>
        </form>
    </div>
</div>
<a href="https://wa.me/+249990063151" class="whatsapp-btn" target="_blank">
    <i class="fab fa-whatsapp"></i>
</a>
</body>
</html>
