<?php
session_start();
require_once 'config.php';

// Проверка авторизации
$user = null;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получаем выбранную категорию
$currentCategory = isset($_GET['category']) ? (int)$_GET['category'] : null;

// Функция для получения названия категории
function getCategoryName($categoryId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    return $stmt->fetchColumn();
}

// Запрос товаров с учетом категории
$sql = "SELECT * FROM products";
$params = [];
if ($currentCategory) {
    $sql .= " WHERE category_id = ?";
    $params[] = $currentCategory;
}
$sql .= " ORDER BY id LIMIT 6";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$initialProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$lastProductId = !empty($initialProducts) ? end($initialProducts)['id'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная | OZONE</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <button id="menu-btn">☰ Каталог</button>

    <div id="logo">
        <a href="index.php" class="logo-link">OZONE</a>
    </div>

    <div id="user-menu">
        <?php if ($user): ?>
            <a href="profile.php">
                <img src="<?= htmlspecialchars($user['avatar'] ?? 'default_avatar.jpg') ?>"
                     alt="Аватар"
                     class="header-avatar">
            </a>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="login.php">🔒 Войти</a>
        <?php endif; ?>
    </div>
</header>

<nav id="sidebar">
    <ul>
        <li><a href="index.php" class="<?= !$currentCategory ? 'active-category' : '' ?>">Все товары</a></li>
        <?php
        $categories = $pdo->query("SELECT * FROM categories")->fetchAll();
        foreach ($categories as $category):
            ?>
            <li>
                <a href="index.php?category=<?= $category['id'] ?>"
                   class="<?= $currentCategory == $category['id'] ? 'active-category' : '' ?>">
                    <?= htmlspecialchars($category['name']) ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

<main>
    <h1><?= $currentCategory ? 'Категория: '.htmlspecialchars(getCategoryName($currentCategory)) : 'Все товары' ?></h1>

    <div class="products" id="productsContainer">
        <?php foreach ($initialProducts as $product): ?>
            <div class="product" data-product-id="<?= $product['id'] ?>">
                <img src="images/<?= htmlspecialchars($product['main_image']) ?>"
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     loading="lazy">
                <h2><?= htmlspecialchars($product['name']) ?></h2>
                <a href="product.php?id=<?= $product['id'] ?>">Подробнее</a>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="loader" class="loader" style="display: none;">
        <div class="spinner"></div>
    </div>

    <div id="endMessage" style="display: none; text-align: center; padding: 20px;">
        <p>Вы просмотрели все товары</p>
    </div>
</main>

<script>
    // Состояние загрузки
    const productState = {
        isLoading: false,
        lastProductId: <?= $lastProductId ?>,
        hasMore: true
    };

    // Текущая категория из PHP
    const currentCategory = <?= $currentCategory ?: 'null' ?>;

    // Функция загрузки товаров
    async function loadMoreProducts() {
        if (productState.isLoading || !productState.hasMore) return;

        productState.isLoading = true;
        document.getElementById('loader').style.display = 'block';

        try {
            const url = `api/get_products.php?last_id=${productState.lastProductId}&limit=6${
                currentCategory ? `&category=${currentCategory}` : ''
            }`;

            const response = await fetch(url);
            const products = await response.json();

            if (products.length > 0) {
                renderProducts(products);
                productState.lastProductId = products[products.length - 1].id;
            } else {
                productState.hasMore = false;
                document.getElementById('endMessage').style.display = 'block';
            }
        } catch (error) {
            console.error("Ошибка загрузки:", error);
        } finally {
            productState.isLoading = false;
            document.getElementById('loader').style.display = 'none';
        }
    }

    // Функция отрисовки товаров
    function renderProducts(products) {
        const container = document.getElementById('productsContainer');

        products.forEach(product => {
            if (!document.querySelector(`[data-product-id="${product.id}"]`)) {
                const productCard = document.createElement('div');
                productCard.className = 'product';
                productCard.dataset.productId = product.id;
                productCard.innerHTML = `
                    <img src="images/${escapeHtml(product.main_image)}"
                         alt="${escapeHtml(product.name)}"
                         loading="lazy">
                    <h2>${escapeHtml(product.name)}</h2>
                    <a href="product.php?id=${product.id}">Подробнее</a>
                `;
                container.appendChild(productCard);
            }
        });
    }

    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Обработчик скролла
    window.addEventListener('scroll', () => {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 500) {
            loadMoreProducts();
        }
    });

    const menuBtn = document.getElementById('menu-btn');
    const sidebar = document.getElementById('sidebar');

    menuBtn.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    document.addEventListener('click', (e) => {
        if (!sidebar.contains(e.target) && e.target !== menuBtn) {
            sidebar.classList.remove('active');
        }
    });

    if (document.querySelectorAll('.product').length < 6) {
        productState.hasMore = false;
        document.getElementById('endMessage').style.display = 'block';
    }
</script>
</body>
</html>