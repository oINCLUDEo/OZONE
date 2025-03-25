<?php
session_start();
require_once 'config.php';

// Проверяем, администратор ли пользователь
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Добавление товара
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $category_id = $_POST['category_id'];

    // Обработка загрузки основного изображения
    $main_image_path = null;
    if (!empty($_FILES['main_image']['name'])) {
        $main_image_path =  uniqid() . '-' . basename($_FILES['main_image']['name']);
        move_uploaded_file($_FILES['main_image']['tmp_name'], 'images/' . $main_image_path);
    }

    // Обработка загрузки галереи
    $gallery_paths = [];
    if (!empty($_FILES['gallery']['name'][0])) {
        foreach ($_FILES['gallery']['name'] as $key => $file_name) {
            $gallery_path = uniqid() . '-' . basename($file_name);
            move_uploaded_file($_FILES['gallery']['tmp_name'][$key], 'images/' . $gallery_path);
            $gallery_paths[] = $gallery_path;
        }
    }

    // Преобразуем галерею в массив PostgreSQL
    $gallery_array = '{' . implode(',', array_map(fn($path) => '"' . addslashes($path) . '"', $gallery_paths)) . '}';

    // Сохранение в БД
    $stmt = $pdo->prepare("INSERT INTO products (name, description, main_image, gallery, category_id) VALUES (:name, :description, :main_image, :gallery, :category_id)");
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'main_image' => $main_image_path,
        'gallery' => $gallery_array, // Преобразованный массив
        'category_id' => $category_id,
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
<header>
    <a id="back-arrow" href="index.php">← Назад</a>
    <h1>Добавить товар</h1>
</header>
<main>
    <form method="post" enctype="multipart/form-data" class="product-form">
        <label for="name">Название</label>
        <input type="text" name="name" id="name" placeholder="Введите название" required>

        <label for="description">Описание</label>
        <textarea name="description" id="description" placeholder="Введите описание" required></textarea>

        <label for="main_image">Основное изображение</label>
        <input type="file" name="main_image" id="main_image" required>

        <label for="gallery">Галерея (можно выбрать несколько изображений)</label>
        <input type="file" name="gallery[]" id="gallery" multiple>

        <label for="category_id">Категория</label>
        <select name="category_id" id="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?> - <?= htmlspecialchars($category['description']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="submit-btn">Добавить</button>
    </form>
</main>
</body>
</html>
