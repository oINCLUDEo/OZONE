<?php
session_start();
require_once 'config.php';

$product_id = $_GET['id'] ?? null;
if (!$product_id) {
    header("Location: index.php");
    exit();
}

// Проверка, вошел ли пользователь
$user_id = $_SESSION['user_id'] ?? null;

// Если пользователь вошел, получаем его данные
$user = null;
if ($user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Получаем данные о продукте
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = :id");
$stmt->execute(['id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    echo "Товар не найден.";
    exit();
}

// Преобразуем строку из базы данных в массив
$gallery = $product['gallery'] ? explode(',', trim($product['gallery'], '{}')) : [];

// Удаляем лишние кавычки из каждого элемента
$gallery = array_map(function($image) {
    return trim($image, '"');
}, $gallery);

// Добавление комментария
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    $parent_id = $_POST['parent_id'] ?? null;  // Получаем id родительского комментария, если есть

    $stmt = $pdo->prepare("INSERT INTO comments (product_id, user_id, content, parent_id) VALUES (:product_id, :user_id, :content, :parent_id)");
    $stmt->execute([
        'product_id' => $product_id,
        'user_id' => $user_id,
        'content' => $content,
        'parent_id' => $parent_id
    ]);
    header("Location: product.php?id=" . $product_id);
    exit();
}

// Получение всех комментариев и подкомментариев
$stmt = $pdo->prepare("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE product_id = :product_id ORDER BY created_at DESC");
$stmt->execute(['product_id' => $product_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Функция для отображения комментариев с учетом иерархии
function displayComments($comments, $parentId = null) {
    foreach ($comments as $comment) {
        if ($comment['parent_id'] == $parentId) {
            echo '<div class="comment" id="comment-' . $comment['id'] . '">';
            echo '<strong class="comment-username">' . htmlspecialchars($comment['username']) . ':</strong>';
            echo '<p class="comment-content">' . htmlspecialchars($comment['content']) . '</p>';
            echo '<button class="reply-btn" data-comment-id="' . $comment['id'] . '">Ответить</button>';
            echo '<div class="sub-comments">';
            displayComments($comments, $comment['id']);
            echo '</div>';
            echo '<div class="reply-form" id="reply-form-' . $comment['id'] . '" style="display:none;">';
            echo '<form method="post">';
            echo '<textarea name="content" required placeholder="Ваш ответ..." class="comment-input"></textarea>';
            echo '<input type="hidden" name="parent_id" value="' . $comment['id'] . '">';
            echo '<button type="submit" class="submit-comment-btn">Добавить ответ</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?></title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .comment-form { display: none; }
        .sub-comments { margin-left: 20px; }
        .reply-form { margin-top: 10px; }
        .reply-form textarea { width: 100%; padding: 8px; }
        .reply-form button { margin-top: 5px; }
    </style>
</head>
<body>
<header>
    <a href="index.php" id="back-arrow">&#8592; Назад</a>

    <a href="index.php" id="logo">OZONE</a>
    <div id="user-menu">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php">
                <img src="<?= isset($user['avatar']) && !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'default_avatar.jpg' ?>" alt="Аватар" class="header-avatar">
            </a>
            <a href="logout.php">Выйти</a>
        <?php else: ?>
            <a href="login.php">🔒 Войти</a>
        <?php endif; ?>
    </div>
</header>

<div class="product-page-container">
    <div class="product-container">
        <div class="product-image">
            <img src="images/<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>

        <div class="product-info">
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>

            <h2>Галерея</h2>
            <div class="gallery">
                <?php if (!empty($gallery)): ?>
                    <?php foreach ($gallery as $image): ?>
                        <img src="images/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="gallery-image">
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Галерея пуста.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <section class="comments-section">
        <h2>Комментарии</h2>
        <div class="comments">
            <?php displayComments($comments); ?>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <button id="show-comment-form" class="show-comment-form-btn">Написать комментарий</button>
            <form method="post" class="comment-form" id="comment-form">
                <textarea name="content" required placeholder="Добавьте комментарий..." class="comment-input"></textarea>
                <button type="submit" class="submit-comment-btn">Добавить комментарий</button>
            </form>
        <?php else: ?>
            <p><a href="login.php">Войдите</a>, чтобы оставить комментарий.</p>
        <?php endif; ?>
    </section>
</div>

<script>
    document.querySelectorAll('.reply-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.getAttribute('data-comment-id');
            const replyForm = document.getElementById('reply-form-' + commentId);
            replyForm.style.display = replyForm.style.display === 'none' || replyForm.style.display === '' ? 'block' : 'none';
        });
    });

    document.getElementById('show-comment-form').addEventListener('click', function() {
        const form = document.getElementById('comment-form');
        form.style.display = form.style.display === 'none' || form.style.display === '' ? 'block' : 'none';
    });
</script>

</body>
</html>
