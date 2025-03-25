<?php
session_start();

// Подключаемся к базе данных
require_once '../config.php';

// Проверка, были ли отправлены данные
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $username = $_POST['username'];
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    $role = $_POST['role']; // Роль по умолчанию 'user'

    // Проверка на пустые поля
    if (empty($username) || empty($password) || empty($password_confirm)) {
        $_SESSION['error_message'] = "Пожалуйста, заполните все поля.";
        header("Location: register.php");
        exit();
    }

    // Проверка, что пароли совпадают
    if ($password !== $password_confirm) {
        $_SESSION['error_message'] = "Пароли не совпадают.";
        header("Location: register.php");
        exit();
    }

    // Проверка на существование пользователя с таким именем
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        $_SESSION['error_message'] = "Пользователь с таким именем уже существует.";
        header("Location: register.php");
        exit();
    }

    // Хэшируем пароль
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Вставляем данные в базу данных
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
    $stmt->execute([
        'username' => $username,
        'password' => $hashed_password,
        'role' => $role
    ]);

    // Сообщение об успешной регистрации
    $_SESSION['success_message'] = "Регистрация прошла успешно. Теперь вы можете войти.";
    header("Location: login.php");
    exit();
}
?>
