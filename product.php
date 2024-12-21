<?php
session_start();
$pdo = new PDO("pgsql:host=localhost;dbname=marketplace", "postgres", "1");

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Получаем данные о продукте
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Товар не найден.";
    exit();
}

// Добавление комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO comments (product_id, user_id, content) VALUES (:product_id, :user_id, :content)");
    $stmt->execute([
        'product_id' => $product_id,
        'user_id' => $user_id,
        'content' => $content
    ]);
    header("Location: product.php?id=" . $product_id);
    exit();
}

// Получение комментариев
$stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE product_id = :product_id ORDER BY created_at DESC");
$stmt->execute(['product_id' => $product_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1><?= htmlspecialchars($product['name']) ?></h1>
<img src="images/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
<p><?= htmlspecialchars($product['description']) ?></p>

<h2>Галерея</h2>
<div>
    <?php foreach ($product['gallery'] as $image): ?>
        <img src="images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
    <?php endforeach; ?>
</div>

<h2>Комментарии</h2>
<div>
    <?php foreach ($comments as $comment): ?>
        <p><strong><?= htmlspecialchars($comment['username']) ?>:</strong> <?= htmlspecialchars($comment['content']) ?></p>
    <?php endforeach; ?>
</div>

<?php if (isset($_SESSION['user_id'])): ?>
    <form method="post">
        <textarea name="content" required></textarea>
        <button type="submit">Добавить комментарий</button>
    </form>
<?php else: ?>
    <p><a href="login.php">Войдите</a>, чтобы оставить комментарий.</p>
<?php endif; ?>
</body>
</html>
