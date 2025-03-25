<?php
session_start();

require_once 'config.php';

// Проверка, были ли отправлены данные
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка, что поля не пустые
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Пожалуйста, заполните все поля.";
        header("Location: login.php");
        exit();
    }

    // Проверка пользователя в базе данных
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    // Если пользователь найден и пароли совпадают
    if ($user && password_verify($password, $user['password'])) {
        // Устанавливаем сессию
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];  // Для разделения доступа (например, admin)

        // Перенаправляем на главную страницу
        header("Location: index.php");
        exit();
    } else {
        // Если неверный логин или пароль
        $_SESSION['error_message'] = "Неверное имя пользователя или пароль.";
        header("Location: login.php");
        exit();
    }
}
?>
