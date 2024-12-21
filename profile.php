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
<h1>Профиль пользователя</h1>
<div class="profile">
    <img src="<?= $user['avatar'] ? htmlspecialchars($user['avatar']) : 'default_avatar.png' ?>" alt="Аватар" class="avatar">
    <h2><?= htmlspecialchars($user['username']) ?></h2>

    <form method="post" enctype="multipart/form-data">
        <label for="avatar">Обновить аватар:</label>
        <input type="file" name="avatar" id="avatar" accept="image/*" required>
        <button type="submit">Загрузить</button>
    </form>
</div>
</body>
</html>
