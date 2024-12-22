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
    <title>OZONE - Регистрация</title>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- Форма регистрации -->
<div class="register-container">
    <div class="logo-container">
        <h1 class="logo">OZONE</h1>
    </div>

    <form action="register_process.php" method="post" class="register-form">
        <div class="form-group">
            <label for="username">Имя пользователя</label>
            <input type="text" id="username" name="username" required placeholder="Введите ваше имя">
        </div>
        <div class="form-group">
            <label for="password">Пароль</label>
            <input type="password" id="password" name="password" required placeholder="Введите пароль">
        </div>
        <div class="form-group">
            <label for="password_confirm">Подтвердите пароль</label>
            <input type="password" id="password_confirm" name="password_confirm" required placeholder="Подтвердите пароль">
        </div>
        <!-- Установка роли (по умолчанию 'user') -->
        <input type="hidden" name="role" value="user">
        <button type="submit" class="register-btn">Зарегистрироваться</button>

        <?php
        // Если есть сообщение об ошибке, выводим его
        if (isset($_SESSION['error_message'])) {
            echo "<p class='error-message'>{$_SESSION['error_message']}</p>";
            unset($_SESSION['error_message']);
        }

        // Если регистрация прошла успешно
        if (isset($_SESSION['success_message'])) {
            echo "<p class='success-message'>{$_SESSION['success_message']}</p>";
            unset($_SESSION['success_message']);
        }
        ?>

        <div class="login-link">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </form>
</div>

</body>
</html>
