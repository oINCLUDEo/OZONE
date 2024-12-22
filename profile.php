<?php
session_start();
$pdo = new PDO("pgsql:host=localhost;dbname=marketplace", "postgres", "1");

// Проверка, вошел ли пользователь
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Получаем информацию о пользователе
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Обновление аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $avatar_name = $_FILES['avatar']['name'];
    $avatar_tmp = $_FILES['avatar']['tmp_name'];
    $avatar_path = "avatars/" . $avatar_name;

    // Сохраняем файл
    if (move_uploaded_file($avatar_tmp, $avatar_path)) {
        $stmt = $pdo->prepare("UPDATE users SET avatar = :avatar WHERE id = :id");
        $stmt->execute([
            'avatar' => $avatar_path,
            'id' => $user_id
        ]);
        $_SESSION['avatar'] = $avatar_path;
        header("Location: profile.php");
        exit();
    }
}

// Добавление/обновление контактов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact'])) {
    $contact = $_POST['contact'];
    $stmt = $pdo->prepare("UPDATE users SET contact_info = :contact WHERE id = :id");
    $stmt->execute([
        'contact' => $contact,
        'id' => $user_id
    ]);
    $user['contact_info'] = $contact;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профиль</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- Стрелка для возврата на главную -->
<a href="index.php" id="back-arrow">← Назад</a>

<!-- Логотип магазина -->
<div id="logo">
    <a href="index.php" id="logo">
        <span style="color: #090315">OZONE</span> <!-- Логотип как текст -->
    </a>
</div>

<h1 id="profile-header">Профиль пользователя</h1>
<div class="profile">
    <!-- Изменение аватара по клику -->
    <form method="post" enctype="multipart/form-data" id="avatar-form">
        <label for="avatar">
            <img src="<?= $user['avatar'] ? htmlspecialchars($user['avatar']) : 'default_avatar.jpg' ?>"
                 alt="Аватар"
                 class="avatar"
                 id="profile-avatar">
        </label>
        <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" required>
        <button type="submit" id="avatar-submit" style="display: none;">Загрузить</button>
    </form>

    <h2><?= htmlspecialchars($user['username']) ?></h2>

    <!-- Контактная информация -->
    <form method="post">
        <label for="contact">Контактная информация:</label>
        <textarea name="contact" id="contact" placeholder="Введите ваши контактные данные"><?= htmlspecialchars($user['contact_info'] ?? '') ?></textarea>
        <button type="submit">Сохранить</button>
    </form>

    <!-- Переход в панель администратора -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="add_product.php" id="admin-panel">Перейти в панель администратора</a>
    <?php endif; ?>
</div>

<script>
    // Обработка клика по аватару
    const avatarInput = document.getElementById('avatar');
    const avatarSubmit = document.getElementById('avatar-submit');

    avatarInput.addEventListener('change', () => {
        avatarSubmit.click(); // Автоматически отправляет форму после выбора файла
    });
</script>
</body>
</html>
