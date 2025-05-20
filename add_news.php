<?php
require_once 'DBConnection.php';
require_once 'auth.php';
session_start();
requireRole('author');

// Get all categories for the dropdown
$conn = DBConnection::connect();
$stmt = $conn->prepare("SELECT id, name FROM category ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title'] ?? '');
    $body = htmlspecialchars($_POST['body'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $image = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        
    
        
        $tmp_name = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $image = uniqid() . '_' . $image_name;
        
        move_uploaded_file($tmp_name, $upload_dir . $image);
    }
    
    $conn = DBConnection::connect();
    $status = 'pending';
    $stmt = $conn->prepare("
        INSERT INTO news (title, body, image, category_id, author_id, status)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssiss", $title, $body, $image, $category_id, $author_id, $status);
    $stmt->execute();
    $stmt->close();
    
    // إعادة التوجيه إلى لوحة تحكم الكاتب
    header('Location: author_dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة مقال جديد</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">نظام إدارة الأخبار</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="author_dashboard.php">لوحة تحكم الكاتب</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            مرحباً، <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="profile.php">الملف الشخصي</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">تسجيل الخروج</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">إضافة مقال جديد</h1>
        
        <div class="card">
            <div class="card-body">
                <form action="add_news.php" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">العنوان</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">التصنيف</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">اختر تصنيفاً</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">الصورة</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label for="body" class="form-label">المحتوى</label>
                        <textarea class="form-control" id="body" name="body" rows="10" required></textarea>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="author_dashboard.php" class="btn btn-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary">إرسال المقال</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
     <!-- Footer -->
  <footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4">
          <h5>روابط</h5>
          <ul class="list-unstyled">
            <li class="mb-2">
              <a href="index.html" class="text-white text-decoration-none">الرئيسية</a>
            </li>
            <li class="mb-2">
              <a href="category.html" class="text-white text-decoration-none">سياسة</a>
            </li>
            <li class="mb-2">
              <a href="category.html" class="text-white text-decoration-none">اقتصاد</a>
            </li>
            <li class="mb-2">
              <a href="category.html" class="text-white text-decoration-none">رياضة</a>
            </li>
            <li class="mb-2">
              <a href="category.html" class="text-white text-decoration-none">صحة</a>
            </li>
          </ul>
        </div>
        <div class="col-md-4">
          <h5>عن الموقع</h5>
          <ul class="list-unstyled">
            <li class="mb-2">
              <a href="#" class="text-white text-decoration-none">من نحن</a>
            </li>
            <li class="mb-2">
              <a href="#" class="text-white text-decoration-none">اتصل بنا</a>
            </li>
            <li class="mb-2">
              <a href="#" class="text-white text-decoration-none">سياسة الخصوصية</a>
            </li>
          </ul>
        </div>
        <div class="col-md-4">
          <h5>تابعنا</h5>
          <div class="d-flex gap-3 mb-3">
            <a href="#" class="text-white">
              <i class="bi bi-facebook fs-4"></i>
            </a>
            <a href="#" class="text-white">
              <i class="bi bi-twitter fs-4"></i>
            </a>
            <a href="#" class="text-white">
              <i class="bi bi-instagram fs-4"></i>
            </a>
            <a href="#" class="text-white">
              <i class="bi bi-youtube fs-4"></i>
            </a>
          </div>
        </div>
      </div>
      <div class="border-top border-secondary pt-3 mt-3 text-center">
        <p class="m-0">© 2025 جميع الحقوق محفوظة</p>
      </div>
    </div>
  </footer>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
