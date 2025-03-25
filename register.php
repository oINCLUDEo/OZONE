<?php
require_once 'config.php';

// Переменные для ошибок
$username_err = $password_err = $confirm_password_err = $avatar_err = "";

// Инициализация переменных
$username = $password = $confirm_password = "";

// Проверка отправки формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Валидация данных
    if (empty(trim($_POST["username"]))) {
        $username_err = "Пожалуйста, введите имя пользователя.";
    } else {
        $username = trim($_POST["username"]);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Пожалуйста, введите пароль.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Пароль должен быть не менее 6 символов.";
    } else {
        $password = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Пожалуйста, подтвердите пароль.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if ($password != $confirm_password) {
            $confirm_password_err = "Пароли не совпадают.";
        }
    }

    // Проверка изображения аватара
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            $avatar_err = "Неверный формат изображения.";
        } else {
            $avatar = 'avatars/' . basename($_FILES['avatar']['name']);
            move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar);
        }
    } else {
        $avatar = 'avatars/default-avatar.jpg'; // Путь к дефолтному изображению
    }

    // Если нет ошибок, то записываем в базу данных
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($avatar_err)) {
        // Хешируем пароль
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Запрос для вставки данных в базу
        $sql = "INSERT INTO users (username, password, role, avatar) VALUES (?, ?, 'user', ?)";

        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(1, $username, PDO::PARAM_STR);
            $stmt->bindParam(2, $hashed_password, PDO::PARAM_STR);
            $stmt->bindParam(3, $avatar, PDO::PARAM_STR);

            if ($stmt->execute()) {
                // Переход на страницу входа после успешной регистрации
                header("location: login.php");
            } else {
                echo "Что-то пошло не так. Пожалуйста, попробуйте позже.";
            }

            $stmt->closeCursor();
        }
    }

    $pdo = null; // Закрытие соединения
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body id="login-body">

<div class="login-container">
    <div class="logo-container">
        <h1 class="logo">OZONE</h1>
    </div>
    <h2 class="text-center">Регистрация</h2>

    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" class="login-form">
        <!-- Имя пользователя -->
        <div class="form-group">
            <label for="username">Имя пользователя:</label>
            <input type="text" name="username" id="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
            <span class="error"><?php echo $username_err; ?></span>
        </div>

        <!-- Пароль -->
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" name="password" id="password" required>
            <span class="error"><?php echo $password_err; ?></span>
        </div>

        <!-- Подтверждение пароля -->
        <div class="form-group">
            <label for="confirm_password">Подтверждение пароля:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <span class="error"><?php echo $confirm_password_err; ?></span>
        </div>

        <!-- Аватар -->
        <div class="form-group">
            <label for="avatar">Выберите аватар:</label>
            <input type="file" name="avatar" id="avatar">
            <span class="error"><?php echo $avatar_err; ?></span>
        </div>

        <!-- Кнопка регистрации -->
        <button type="submit">Зарегистрироваться</button>

        <div class="signup-link">
            Уже есть аккаунт? <a href="login.php">Войти</a>
        </div>
    </form>
</div>
</body>
</html>
