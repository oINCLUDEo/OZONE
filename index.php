<?php
session_start();
$pdo = new PDO("pgsql:host=localhost;dbname=marketplace", "postgres", "1");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Магазин</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<!-- Видео-анимация при загрузке страницы -->
<div id="loading-screen" style="display: none;">
    <video autoplay muted playsinline id="loading-video">
        <source src="animation.mp4" type="video/mp4">
        Ваш браузер не поддерживает видео.
    </video>
</div>

<!-- Верхняя часть страницы -->
<header>
    <!-- Логотип магазина -->
    <div id="logo">
        <a href="index.php" id="logo">
            <span>OZONE</span> <!-- Логотип как текст -->
        </a>
    </div>

    <!-- Кнопка каталога -->
    <button id="menu-btn">☰ Каталог</button>

    <!-- Иконка пользователя -->
    <div id="user-menu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">
                <img src="<?= $_SESSION['avatar'] ?? 'default_avatar.jpg' ?>" alt="Аватар" class="header-avatar">
            </a>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="login.php">🔒 Войти</a>
        <?php endif; ?>
    </div>
</header>

<!-- Выдвижной каталог -->
<nav id="sidebar">
    <ul>
        <?php
        $stmt = $pdo->query("SELECT * FROM categories");
        while ($category = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <li><a href="index.php?category=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
        <?php endwhile; ?>
    </ul>
</nav>

<!-- Основной контент -->
<main>
    <h1>Товары</h1>
    <div class="products">
        <?php
        $category = isset($_GET['category']) ? intval($_GET['category']) : null;
        $query = "SELECT * FROM products";
        if ($category) {
            $query .= " WHERE category_id = :category";
            $stmt = $pdo->prepare($query);
            $stmt->execute(['category' => $category]);
        } else {
            $stmt = $pdo->query($query);
        }

        while ($product = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="product">
                <img src="images/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <a href="product.php?id=<?= $product['id'] ?>">Подробнее</a>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<script>
    // Логика для выдвижного каталога
    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');

    let isMouseOverSidebar = false;

    menuBtn.addEventListener('mouseover', () => {
        sidebar.classList.add('active'); // Добавляем класс для активации
    });

    // Когда мышь входит в каталог (sidebar)
    sidebar.addEventListener('mouseenter', () => {
        sidebar.classList.add('active'); // Удерживаем каталог активным
    });

    // При уходе мыши с каталога
    sidebar.addEventListener('mouseleave', () => {
        sidebar.classList.remove('active'); // Убираем класс для скрытия
    });

    // При уходе мыши с кнопки меню
    menuBtn.addEventListener('mouseleave', () => {
        sidebar.classList.remove('active'); // Убираем класс для скрытия
    });

    // Логика для воспроизведения видео один раз в день
    document.addEventListener("DOMContentLoaded", () => {
        const loadingScreen = document.getElementById("loading-screen");
        const loadingVideo = document.getElementById("loading-video");

        // Получаем текущую дату
        const today = new Date().toISOString().split('T')[0];
        const lastPlayed = localStorage.getItem('lastPlayedDate');

        // Если видео ещё не воспроизводилось сегодня, показываем его
        if (lastPlayed !== today) {
            localStorage.setItem('lastPlayedDate', today);
            loadingScreen.style.display = "flex";

            loadingVideo.addEventListener("ended", () => {
                loadingScreen.style.transition = "opacity 0.5s ease";
                loadingScreen.style.opacity = "0";
                setTimeout(() => loadingScreen.style.display = "none", 500); // Убираем из DOM
            });
        }
    });
</script>
</body>
</html>
