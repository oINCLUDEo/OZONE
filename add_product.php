<?php
session_start();
$pdo = new PDO("pgsql:host=localhost;dbname=marketplace", "postgres", "1");

// Проверяем, администратор ли пользователь
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Добавление товара
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $main_image = $_POST['main_image'];
    $gallery = $_POST['gallery'];
    $category_id = $_POST['category_id'];

    $stmt = $pdo->prepare("INSERT INTO products (name, description, main_image, gallery, category_id) VALUES (:name, :description, :main_image, :gallery, :category_id)");
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'main_image' => $main_image,
        'gallery' => explode(",", $gallery),
        'category_id' => $category_id
    ]);

    header("Location: index.php");
    exit();
}

// Получаем категории
$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить товар</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Добавить товар</h1>
<form method="post">
    <input type="text" name="name" placeholder="Название" required>
    <textarea name="description" placeholder="Описание" required></textarea>
    <input type="text" name="main_image" placeholder="Основное изображение" required>
    <input type="text" name="gallery" placeholder="Галерея (через запятую)" required>
    <select name="category_id" required>
        <?php foreach ($categories as $category): ?>
            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Добавить</button>
</form>
</body>
</html>
