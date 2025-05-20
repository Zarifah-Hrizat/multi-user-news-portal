<?php
// استيراد ملف الاتصال بقاعدة البيانات
require_once 'DBConnection.php';
require_once 'auth.php';

requireRole('admin');

// تهيئة متغيرات الخطأ والنجاح
$errors = [];
$success = false;

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // الحصول على بيانات النموذج
    $name =htmlspecialchars($_POST['name'] ?? '');
    $email =htmlspecialchars( $_POST['email'] ?? '');
    $password = htmlspecialchars($_POST['password'] ?? '');
    $role = htmlspecialchars($_POST['role'] ?? '');
    
    // التحقق من البيانات
    if (empty($name)) {
        $errors[] = "الرجاء إدخال اسم المستخدم";
    }
    
    if (empty($email)) {
        $errors[] = "الرجاء إدخال البريد الإلكتروني";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "الرجاء إدخال بريد إلكتروني صحيح";
    }
    
    if (empty($password)) {
        $errors[] = "الرجاء إدخال كلمة المرور";
    }
    
    if (!in_array($role, ['admin', 'editor', 'author'])) {
        $errors[] = "الرجاء اختيار دور صحيح";
    }
    
    // التحقق من وجود البريد الإلكتروني مسبقًا
    if (empty($errors)) {
        $conn = DBConnection::connect();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        if ($row['count'] > 0) {
            $errors[] = "البريد الإلكتروني مستخدم بالفعل";
        }
    }
    
    // إذا لم تكن هناك أخطاء، قم بإضافة المستخدم
    if (empty($errors)) {
        // تشفير كلمة المرور
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // إضافة المستخدم إلى قاعدة البيانات
        $conn = DBConnection::connect();
        $stmt = $conn->prepare("INSERT INTO user (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);
        
        if ($stmt->execute()) {
            $success = true;
            // إعادة تعيين النموذج
            $name = $email = $password = $role = '';
        } else {
            $errors[] = "حدث خطأ أثناء إضافة المستخدم: " . $conn->error;
        }
        
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مستخدم جديد</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .error {
            color: red;
            margin-bottom: 10px;
        }
        .success {
            color: green;
            margin-bottom: 10px;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, select {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
        }
        button {
            padding: 5px 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>إضافة مستخدم جديد</h1>
    
    <a href="admin_dashboard.php">العودة إلى لوحة التحكم</a>
    
    <?php if ($success): ?>
        <p class="success">تم إضافة المستخدم بنجاح.</p>
    <?php endif; ?>
    
    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form action="add_user.php" method="post">
        <label for="name">اسم المستخدم:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
        
        <label for="email">البريد الإلكتروني:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
        
        <label for="password">كلمة المرور:</label>
        <input type="password" id="password" name="password" required>
        
        <label for="role">الدور:</label>
        <select id="role" name="role" required>
            <option value="">اختر دور المستخدم</option>
            <option value="author" <?= (isset($role) && $role === 'author') ? 'selected' : '' ?>>كاتب</option>
            <option value="editor" <?= (isset($role) && $role === 'editor') ? 'selected' : '' ?>>محرر</option>
            <option value="admin" <?= (isset($role) && $role === 'admin') ? 'selected' : '' ?>>مدير</option>
        </select>
        
        <button type="submit">إضافة المستخدم</button>
        <button type="button" onclick="window.location.href='admin_dashboard.php'">إلغاء</button>
    </form>
</body>
</html>
