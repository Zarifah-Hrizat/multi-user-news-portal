<?php

require_once 'DBConnection.php';

$conn = DBConnection::connect();

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("
    SELECT n.*, c.name as category_name, u.name as author_name 
    FROM news n
    JOIN category c ON n.category_id = c.id
    JOIN user u ON n.author_id = u.id
    WHERE n.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();

if (!$news) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title']) ?> - News System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">News System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
        </div>
    </nav>

    <div class="container mt-4">
        <div class="card mb-4">
            <div class="card-body">
                <h1 class="card-title"><?= htmlspecialchars($news['title']) ?></h1>
                <div class="text-muted mb-3">
                    <small>
                        Category: <?= htmlspecialchars($news['category_name']) ?> | 
                        By: <?= htmlspecialchars($news['author_name']) ?> | 
                        Posted: <?= date('M d, Y', strtotime($news['dateposted'])) ?> |
                        Status: 
                        <?php if ($news['status'] == 'approved'): ?>
                            <span class="badge bg-success">Approved</span>
                        <?php elseif ($news['status'] == 'denied'): ?>
                            <span class="badge bg-danger">Denied</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php endif; ?>
                    </small>
                </div>
                
                <?php if (!empty($news['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($news['image']) ?>" class="img-fluid mb-3" alt="News Image" style="max-height: 400px;">
                <?php endif; ?>
                
                <div class="card-text">
                    <?= nl2br(htmlspecialchars($news['body'])) ?>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-between">
            <a href="javascript:history.back()" class="btn btn-secondary">Back</a>
            
            <?php if (strpos($_SERVER['HTTP_REFERER'] ?? '', 'editor_dashboard.php') !== false): ?>
                <div>
                    <form action="editor_dashboard.php" method="post" class="d-inline">
                        <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success">Approve</button>
                    </form>
                    <form action="editor_dashboard.php" method="post" class="d-inline">
                        <input type="hidden" name="news_id" value="<?= $news['id'] ?>">
                        <input type="hidden" name="action" value="deny">
                        <button type="submit" class="btn btn-danger">Deny</button>
                    </form>
                </div>
            <?php endif; ?>
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
