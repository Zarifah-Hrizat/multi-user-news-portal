<?php
require_once 'DBConnection.php';
require_once 'auth.php';

requireRole('admin');

// معالجة حذف المستخدم
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    $user_id = $_POST['user_id'] ?? 0;
    
    if ($user_id != $_SESSION['user_id']) {
        $conn = DBConnection::connect();
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        
        // إعادة التوجيه لتحديث الصفحة
        header('Location: admin_dashboard.php?deleted=success');
        exit;
    } else {
        header('Location: admin_dashboard.php?error=self_delete');
        exit;
    }
}

$conn = DBConnection::connect();
$stmt = $conn->prepare("SELECT * FROM user ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// الحصول على إحصائيات النظام
$total_users = count($users);

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM news");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_news = $row['count'];
$stmt->close();

$stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM news GROUP BY status");
$stmt->execute();
$result = $stmt->get_result();
$news_by_status = [];
while ($row = $result->fetch_assoc()) {
    $news_by_status[$row['status']] = $row['count'];
}
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as count FROM category");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_categories = $row['count'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة تحكم المدير - نظام إدارة الأخبار</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stats-card {
            transition: all 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
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
                        <a class="nav-link active" href="admin_dashboard.php">لوحة تحكم المدير</a>
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
            <h1>لوحة تحكم المدير</h1>
            <a href="add_user.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> إضافة مستخدم جديد
            </a>
        </div>
        
        <?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> تم حذف المستخدم بنجاح.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'self_delete'): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> لا يمكنك حذف حسابك الخاص.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <!-- إحصائيات النظام -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">إجمالي المستخدمين</h6>
                                <h2 class="mb-0"><?= $total_users ?></h2>
                            </div>
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
                <div class="col-md-3 mb-3">
                <div class="card stats-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">إجمالي المقالات</h6>
                                <h2 class="mb-0"><?= $total_news ?></h2>
                            </div>
                            <i class="fas fa-newspaper fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">المقالات المعتمدة</h6>
                                <h2 class="mb-0"><?= $news_by_status['approved'] ?? 0 ?></h2>
                            </div>
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 mb-3">
                <div class="card stats-card bg-warning text-dark">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">التصنيفات</h6>
                                <h2 class="mb-0"><?= $total_categories ?></h2>
                            </div>
                            <i class="fas fa-tags fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
          
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">جميع المستخدمين</h5>
            </div>
            <div class="card-body">
                
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>الرقم</th>
                                    <th>الاسم</th>
                                    <th>البريد الإلكتروني</th>
                                    <th>الدور</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= $user['id'] ?></td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td>
                                            <?php if ($user['role'] == 'admin'): ?>
                                                <span class="badge bg-danger">مدير</span>
                                            <?php elseif ($user['role'] == 'editor'): ?>
                                                <span class="badge bg-primary">محرر</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">كاتب</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i> تعديل
                                            </a>
                                            
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $user['id'] ?>">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                                
                                                <!-- Modal for Delete Confirmation -->
                                                <div class="modal fade" id="deleteModal<?= $user['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $user['id'] ?>" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel<?= $user['id'] ?>">تأكيد الحذف</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                هل أنت متأكد من رغبتك في حذف المستخدم <strong><?= htmlspecialchars($user['name']) ?></strong>؟
                                                                <p class="text-danger mt-2">تحذير: هذا الإجراء لا يمكن التراجع عنه.</p>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                                <form action="admin_dashboard.php" method="post">
                                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                                    <input type="hidden" name="action" value="delete_user">
                                                                    <button type="submit" class="btn btn-danger">حذف</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <button type="button" class="btn btn-sm btn-danger" disabled title="لا يمكنك حذف حسابك الخاص">
                                                    <i class="fas fa-trash"></i> حذف
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                
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
