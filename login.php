<?php
session_start();

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: admin_dashboard.php');
            break;
        case 'editor':
            header('Location: editor_dashboard.php');
            break;
        case 'author':
            header('Location: author_dashboard.php');
            break;
        default:
            header('Location: index.php');
    }
    exit;
}

require_once 'DBConnection.php';



$error = '';

// معالجة نموذج تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ;
    $password = $_POST['password'] ;
    

    
        $conn = DBConnection::connect();
        $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            switch ($user['role']) {
                case 'admin':
                    header('Location: admin_dashboard.php');
                    break;
                case 'editor':
                    header('Location: editor_dashboard.php');
                    break;
                case 'author':
                    header('Location: author_dashboard.php');
                    break;
                default:
                    header('Location: index.php');
            }
            exit;
        } else {
            $error = 'البريد الإلكتروني أو كلمة المرور غير صحيحة';
        }
    }

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - نظام إدارة الأخبار</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>تسجيل الدخول</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">تسجيل الدخول</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
