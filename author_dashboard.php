<?php
require_once 'DBConnection.php';
require_once 'auth.php';

// Require author role
requireRole('author');
$author_id = $_SESSION['user_id'];
// Get all news items created by this author
$conn = DBConnection::connect();
$stmt = $conn->prepare("
    SELECT n.*, c.name as category_name 
    FROM news n
    JOIN category c ON n.category_id = c.id
    WHERE n.author_id = ?
    ORDER BY n.dateposted DESC
");
$stmt->bind_param("i", $author_id);
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
    <title>لوحة تحكم الكاتب</title>
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
                        <a class="nav-link active" href="author_dashboard.php">لوحة تحكم الكاتب</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>لوحة تحكم الكاتب - <?= htmlspecialchars($_SESSION['user_name']) ?></h1>
            <a href="add_news.php" class="btn btn-primary">إضافة مقال جديد</a>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">مقالاتي</h5>
            </div>
            <div class="card-body">
                <?php if (empty($news)): ?>
                    <div class="alert alert-info">لم تقم بإنشاء أي مقالات إخبارية بعد.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>التصنيف</th>
                                    <th>تاريخ النشر</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($news as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['title']) ?></td>
                                        <td><?= htmlspecialchars($item['category_name']) ?></td>
                                        <td><?= date('d M Y', strtotime($item['dateposted'])) ?></td>
                                        <td>
                                            <?php if ($item['status'] == 'approved'): ?>
                                                <span class="badge bg-success">معتمد</span>
                                            <?php elseif ($item['status'] == 'denied'): ?>
                                                <span class="badge bg-danger">مرفوض</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_news.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-primary">تعديل</a>
                                            <a href="view_news.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-info">عرض</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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
