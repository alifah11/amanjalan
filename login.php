<?php
session_start();
require_once 'koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare(
        $conn,
        "SELECT id, username, password FROM admin WHERE username = ? LIMIT 1"
    );

    mysqli_stmt_bind_param($stmt, 's', $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);

    if ($admin && password_verify($password, $admin['password'])) {
        session_regenerate_id(true);

        $_SESSION['admin_login'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];

        mysqli_stmt_close($stmt);

        header('Location: admin/dashboard.php');
        exit;
    }

    mysqli_stmt_close($stmt);
    $error = 'Username atau password salah.';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin | AmanJalan</title>
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        :root {
            --primary: #145c43;
            --secondary: #21815c;
            --light: #eef6f1;
            --dark: #173e30;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #253d32;
            background:
                radial-gradient(circle at 10% 15%, #dceee3 0, transparent 28%),
                radial-gradient(circle at 90% 85%, #dcebe1 0, transparent 30%),
                #f4f7f5;
        }

        .login-container {
            display: grid;
            grid-template-columns: 1.02fr 1fr;
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            overflow: hidden;
            background: #fff;
            border: 1px solid rgba(20, 92, 67, .08);
            border-radius: 28px;
            box-shadow: 0 25px 70px rgba(23, 62, 48, .12);
        }

        /* PANEL HIJAU */
        .login-banner {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 44px;
            color: #fff;
            background: linear-gradient(145deg, #176b4c, #104632);
            border-radius: 0 70px 70px 0;
        }

        .login-banner::before,
        .login-banner::after {
            content: "";
            position: absolute;
            z-index: -1;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-banner::before {
            width: 300px;
            height: 300px;
            top: -125px;
            right: -100px;
        }

        .login-banner::after {
            width: 230px;
            height: 230px;
            bottom: -115px;
            left: -85px;
        }

        .brand-icon {
            width: 66px;
            height: 66px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 26px;
            border: 1px solid rgba(255, 255, 255, .22);
            border-radius: 22px;
            background: rgba(255, 255, 255, .13);
            box-shadow: 0 8px 25px rgba(0, 0, 0, .08);
            font-size: 30px;
        }

        .login-banner h1 {
            margin: 0 0 15px;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 1.8px;
        }

.login-banner p {
    margin: 0;
    color: #e0f0e6;
    font-size: 14px;
    line-height: 1.9;
}

.banner-note {
    margin-top: 32px;
    padding: 17px 19px;
    border: 1px solid rgba(255, 255, 255, .18);
    border-radius: 17px;
    background: rgba(255, 255, 255, .09);
    color: #f3faf5;
    font-size: 13px;
    line-height: 1.8;
}

/* FORM LOGIN */
.login-form-area {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 54px 48px;
}

.login-form-area h2 {
    margin: 0 0 10px;
    color: var(--dark);
    font-size: 29px;
    font-weight: 800;
    letter-spacing: -.5px;
}

.subtitle {
    margin: 0 0 34px;
    color: #7b8b80;
    font-size: 14px;
    line-height: 1.8;
}

.form-group {
    margin-bottom: 22px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    color: #294a38;
    font-size: 13px;
    font-weight: 700;
}

.form-group input {
    width: 100%;
    padding: 15px 17px;
    border: 1px solid #e0e9e2;
    border-radius: 13px;
    outline: none;
    background: #f8fbf8;
    color: #263d30;
    font-family: inherit;
    font-size: 14px;
    transition: border-color .2s, box-shadow .2s, background .2s;
}

.form-group input:focus {
    border-color: var(--secondary);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(33, 129, 92, .10);
}

.login-button {
    width: 100%;
    margin-top: 7px;
    padding: 15px 20px;
    border: none;
    border-radius: 13px;
    background: linear-gradient(110deg, #145c43, #21815c);
    box-shadow: 0 7px 16px rgba(20, 92, 67, .15);
    color: #fff;
    font-family: inherit;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: transform .2s, box-shadow .2s;
}

.login-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 11px 22px rgba(20, 92, 67, .22);
}

.error-message {
    margin-bottom: 20px;
    padding: 13px 15px;
    border: 1px solid #f4d4d1;
    border-radius: 12px;
    background: #fff4f2;
    color: #b42318;
    font-size: 13px;
}

.back-link {
    display: block;
    margin-top: 25px;
    color: var(--primary);
    text-align: center;
    text-decoration: none;
    font-size: 13px;
    transition: color .2s;
}

.back-link:hover {
    color: var(--secondary);
    text-decoration: underline;
}

.copyright {
    margin-top: 38px;
    color: #9aa99e;
    text-align: center;
    font-size: 11px;
}

@media (max-width: 700px) {
    body {
        padding: 16px;
    }

    .login-container {
        grid-template-columns: 1fr;
        max-width: 440px;
        border-radius: 24px;
    }

    .login-banner {
        padding: 30px;
        border-radius: 0 0 45px 45px;
    }

    .brand-icon {
        width: 55px;
        height: 55px;
        margin-bottom: 18px;
        border-radius: 18px;
    }

    .login-banner h1 {
        font-size: 27px;
    }

    .banner-note {
        margin-top: 20px;
    }

    .login-form-area {
        padding: 34px 27px;
    }

    .login-form-area h2 {
        font-size: 25px;
    }

    .copyright {
        margin-top: 28px;
    }
}

    </style>
</head>
<body>

<div class="login-container">
    <section class="login-banner">
        <div class="brand-icon">🛣️</div>
        <h1>AMANJALAN</h1>
        <p>
            Sistem Peringatan Tikungan Tajam dan Jalur Berbahaya.
            Membantu pengendara mengenali titik rawan perjalanan
            dengan lebih waspada.
        </p>

        <div class="banner-note">
            🛡️ <strong>Panel Administrator</strong><br>
            Kelola informasi titik rawan untuk mendukung perjalanan
            yang lebih aman.
        </div>
    </section>

    <section class="login-form-area">
        <h2>Selamat Datang!</h2>
        <p class="subtitle">
            Masuk ke panel admin untuk mengelola data AmanJalan.
        </p>

        <?php if ($error !== ""): ?>
            <div class="error-message">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Masukkan username"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan password"
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="login-button">
                Masuk ke Dashboard →
            </button>
        </form>

        <a href="index.php" class="back-link">
            ← Kembali ke halaman utama
        </a>

        <div class="copyright">
            © <?= date("Y") ?> AmanJalan · Sistem Informasi
        </div>
    </section>
</div>

</body>
</html>
