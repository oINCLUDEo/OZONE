<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = new PDO("pgsql:host=localhost;dbname=marketplace", "postgres", "1");

    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Проверка на совпадение паролей
    if ($password !== $password_confirm) {
        $_SESSION['error_message'] = "Пароли не совпадают.";
        header("Location: register.php");
        exit();
    }

    // Хэшируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Вставляем данные в базу данных
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
    $stmt->execute([
        'username' => $username,
        'password' => $hashed_password
    ]);

    $_SESSION['success_message'] = "Регистрация прошла успешно. Теперь вы можете войти.";
    header("Location: login.php");
    exit();
}
?>
