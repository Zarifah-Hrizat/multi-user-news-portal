<?php
require_once 'DBConnection.php';
// Start session
session_start();

// Get approved news items for the front page
$conn = DBConnection::connect();
$stmt = $conn->prepare("
    SELECT n.*, c.name as category_name, u.name as author_name 
    FROM news n
    JOIN category c ON n.category_id = c.id
    JOIN user u ON n.author_id = u.id
    WHERE n.status = 'approved'
    ORDER BY n.dateposted DESC
");
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نظام إدارة الأخبار - الصفحة الرئيسية</title>
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
                        <a class="nav-link active" href="index.php">الرئيسية</a>
                    </li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin_dashboard.php">لوحة تحكم المدير</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['user_role'] === 'editor'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="editor_dashboard.php">لوحة تحكم المحرر</a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if ($_SESSION['user_role'] === 'author'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="author_dashboard.php">لوحة تحكم الكاتب</a>
                            </li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
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
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">تسجيل الدخول</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 class="mb-4">أحدث الأخبار</h1>
        
        <div class="row">
            <?php foreach ($news as $item): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if ($item['image']): ?>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="صورة الخبر" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light text-center py-5">لا توجد صورة</div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($item['title']) ?></h5>
                            <p class="card-text text-muted">
                                <small>
                                    التصنيف: <?= htmlspecialchars($item['category_name']) ?> | 
                                    بواسطة: <?= htmlspecialchars($item['author_name']) ?> | 
                                    نُشر في: <?= date('d M Y', strtotime($item['dateposted'])) ?>
                                </small>
                            </p>
                            <p class="card-text"><?= substr(htmlspecialchars($item['body']), 0, 150) ?>...</p>
                            <a href="view_news.php?id=<?= $item['id'] ?>" class="btn btn-primary">اقرأ المزيد</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
        
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
