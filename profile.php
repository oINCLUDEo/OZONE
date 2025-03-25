<?php
session_start();
require_once 'config.php';

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

// Обновление email
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Проверка формата email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE id = :id");
        $stmt->execute([
            'email' => $email,
            'id' => $user_id
        ]);
        $user['email'] = $email;
    } else {
        $email_error = "Некорректный формат email.";
    }
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
<body class="profile-page">
<!-- Стрелка для возврата на главную -->
<a href="index.php" id="back-arrow">← Назад</a>

<!-- Логотип магазина -->
<div id="logo">
    <a href="index.php" id="logo">
        <span style="color: #090315">OZONE</span> <!-- Логотип как текст -->
    </a>
</div>

<h1 id="profile-header">Профиль пользователя</h1>

<!-- Каркас профиля -->
<div class="profile-container">
    <!-- Левый блок: аватар -->
    <div class="profile-avatar-container">
        <form method="post" enctype="multipart/form-data" id="avatar-form">
            <label for="avatar">
                <img src="<?= htmlspecialchars($user['avatar'] ?? 'default_avatar.jpg') ?>"
                     alt="Аватар"
                     class="avatar">
            </label>
            <input type="file" name="avatar" id="avatar" accept="image/*" style="display: none;" required>
            <button type="submit" id="avatar-submit" style="display: none;">Загрузить</button>
        </form>
    </div>

    <!-- Правый блок: информация -->
    <div class="profile-info">
        <h2><?= htmlspecialchars($user['username'] ?? 'Неизвестный пользователь') ?></h2>

        <!-- Email -->
        <form method="post" class="info-block">
            <label for="email">Email:</label>
            <input type="text" name="email" id="email" value="<?= htmlspecialchars($user['email'] ?? 'Не указан') ?>">
            <button type="submit">Сохранить</button>
            <?php if (isset($email_error)): ?>
                <p class="error"><?= htmlspecialchars($email_error) ?></p>
            <?php endif; ?>
        </form>

        <!-- Контактная информация -->
        <form method="post" class="info-block">
            <label for="contact">Контактная информация:</label>
            <textarea name="contact" id="contact"><?= htmlspecialchars($user['contact_info'] ?? '') ?></textarea>
            <button type="submit">Сохранить</button>
        </form>

        <!-- Роль пользователя -->
        <div class="info-block">
            <label for="role">Роль:</label>
            <input type="text" id="role" value="<?= htmlspecialchars($user['role'] ?? 'Пользователь') ?>" disabled>
        </div>

        <!-- Админ-панель -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="add_product.php" id="admin-panel">Перейти в панель администратора</a>
        <?php endif; ?>
    </div>
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
