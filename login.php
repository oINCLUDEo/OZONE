<?php
session_start();

// Если пользователь уже авторизован, перенаправляем на главную
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OZONE - Вход</title>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> <!-- Подключение основного стиля -->
</head>
<body id="login-body">

<!-- Форма входа -->
<div class="login-container">
    <div class="logo-container">
        <h1 class="logo">OZONE</h1>
    </div>

    <form action="login_process.php" method="post" class="login-form">
        <div class="form-group">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username" required placeholder="Введите ваше имя">
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required placeholder="Введите пароль">
        </div>
        <button type="submit" class="login-btn">Войти</button>
        <?php
        // Если есть сообщение об ошибке, отображаем его
        if (isset($_SESSION['error_message'])) {
            echo "<p class='error-message'>{$_SESSION['error_message']}</p>";
            unset($_SESSION['error_message']);
        }
        ?>
        <div class="signup-link">
            <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </div>
    </form>
</div>

</body>
</html>
