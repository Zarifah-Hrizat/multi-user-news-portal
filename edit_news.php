<?php
// استيراد ملف الاتصال بقاعدة البيانات
require_once 'DBConnection.php';
require_once 'auth.php';

requireRole('author');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: author_dashboard.php');
    exit;
}

$news_id = $_GET['id'];
$author_id = $_SESSION['user_id'];

// التحقق من أن المقال ينتمي للكاتب الحالي
$conn = DBConnection::connect();
$stmt = $conn->prepare("
    SELECT n.*, c.name as category_name 
    FROM news n
    JOIN category c ON n.category_id = c.id
    WHERE n.id = ? AND n.author_id = ?
");
$stmt->bind_param("ii", $news_id, $author_id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();
$stmt->close();

if (!$news) {
    header('Location: author_dashboard.php');
    exit;
}

// الحصول على جميع التصنيفات للقائمة المنسدلة
$stmt = $conn->prepare("SELECT id, name FROM category ORDER BY name");
$stmt->execute();
$result = $stmt->get_result();
$categories = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// معالجة إرسال النموذج
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = htmlspecialchars($_POST['title'] ?? '');
    $body = htmlspecialchars($_POST['body'] ?? '');
    $category_id =htmlspecialchars( $_POST['category_id'] ?? '');
    $image = $news['image']; // الاحتفاظ بالصورة الحالية إذا لم يتم تحميل صورة جديدة
    
    // معالجة رفع الصورة الجديدة
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        

        
        $tmp_name = $_FILES['image']['tmp_name'];
        $image_name = basename($_FILES['image']['name']);
        $new_image = uniqid() . '_' . $image_name;
        
        // حذف الصورة القديمة إذا كانت موجودة
        if (!empty($news['image']) && file_exists($upload_dir . $news['image'])) {
            unlink($upload_dir . $news['image']);
        }
        
        move_uploaded_file($tmp_name, $upload_dir . $new_image);
        $image = $new_image;
    }
    
    // تحديث المقال
    $conn = DBConnection::connect();
    $stmt = $conn->prepare("
        UPDATE news 
        SET title = ?, body = ?, image = ?, category_id = ?, status = 'pending'
        WHERE id = ? AND author_id = ?
    ");
    $status = 'pending'; // إعادة المقال إلى حالة قيد المراجعة بعد التعديل
    $stmt->bind_param("sssiii", $title, $body, $image, $category_id, $news_id, $author_id);
    $stmt->execute();
    $stmt->close();
    
    // إعادة التوجيه إلى لوحة تحكم الكاتب مع رسالة نجاح
    header('Location: author_dashboard.php?edit=success');
    exit;
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل المقال - نظام إدارة الأخبار</title>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>تعديل المقال</h1>
            <a href="author_dashboard.php" class="btn btn-secondary">العودة إلى لوحة التحكم</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <form action="edit_news.php?id=<?= $news_id ?>" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="title" class="form-label">العنوان</label>
                        <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($news['title']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="category_id" class="form-label">التصنيف</label>
                        <select class="form-select" id="category_id" name="category_id" required>
                            <option value="">اختر تصنيفاً</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= ($category['id'] == $news['category_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="image" class="form-label">الصورة</label>
                        <?php if ($news['image']): ?>
                            <div class="mb-2">
                                <img src="uploads/<?= htmlspecialchars($news['image']) ?>" alt="صورة المقال الحالية" class="img-thumbnail" style="max-height: 200px;">
                                <p class="text-muted">الصورة الحالية. قم بتحميل صورة جديدة لتغييرها.</p>
                            </div>
                        <?php endif; ?>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    </div>
                    
                    <div class="mb-3">
                        <label for="body" class="form-label">المحتوى</label>
                        <textarea class="form-control" id="body" name="body" rows="10" required><?= htmlspecialchars($news['body']) ?></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> ملاحظة: بعد تعديل المقال، سيتم إعادته إلى حالة "قيد المراجعة" وسيحتاج إلى موافقة المحرر مرة أخرى.
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="author_dashboard.php" class="btn btn-secondary">إلغاء</a>
                        <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
