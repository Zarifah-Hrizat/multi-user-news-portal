<?php
require_once 'DBConnection.php';
require_once 'auth.php';

// Require editor role
requireRole('editor');

// Process status updates

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $news_id = $_POST['news_id'] ?? 0;
    $conn = DBConnection::connect();
    
    if ($_POST['action'] === 'approve') {
         $status = 'approved';
        $stmt = $conn->prepare("UPDATE news SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $news_id);
        $stmt->execute();
        $stmt->close();

    } elseif ($_POST['action'] === 'deny') {
      $status = 'denied';
        $stmt = $conn->prepare("UPDATE news SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $news_id);
        $stmt->execute();
        $stmt->close();   
    } elseif ($_POST['action'] === 'delete') {
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param("i", $news_id);
        $stmt->execute();
        $stmt->close();
    }

    
    // Redirect to refresh the page
    header('Location: editor_dashboard.php');
    exit;
}

// Get all news items ordered by date posted

$conn = DBConnection::connect();
$stmt = $conn->prepare("
    SELECT n.*, c.name as category_name, u.name as author_name 
    FROM news n
    JOIN category c ON n.category_id = c.id
    JOIN user u ON n.author_id = u.id
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
    <title>لوحة تحكم المحرر</title>
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
                        <a class="nav-link active" href="editor_dashboard.php">لوحة تحكم المحرر</a>
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
        <h1 class="mb-4">لوحة تحكم المحرر</h1>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">جميع المقالات الإخبارية</h5>
            </div>
            <div class="card-body">
                <?php if (empty($news)): ?>
                    <div class="alert alert-info">لا توجد مقالات إخبارية متاحة.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>العنوان</th>
                                    <th>الكاتب</th>
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
                                        <td><?= htmlspecialchars($item['author_name']) ?></td>
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
                                            <div class="btn-group">
                                                <a href="view_news.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-info">عرض</a>
                                                <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                                    الإجراءات
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <form action="editor_dashboard.php" method="post">
                                                            <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="dropdown-item">اعتماد</button>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form action="editor_dashboard.php" method="post">
                                                            <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                                            <input type="hidden" name="action" value="deny">
                                                            <button type="submit" class="dropdown-item">رفض</button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="editor_dashboard.php" method="post" onsubmit="return confirm('هل أنت متأكد من رغبتك في حذف هذا المقال؟');">
                                                            <input type="hidden" name="news_id" value="<?= $item['id'] ?>">
                                                            <input type="hidden" name="action" value="delete">
                                                            <button type="submit" class="dropdown-item text-danger">حذف</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
