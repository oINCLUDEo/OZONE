<?php
session_start();

// Удаляем все сессионные переменные
session_unset();

// Разрушаем сессию
session_destroy();

// Перенаправляем на главную страницу (или страницу входа)
header("Location: index.php");
exit();
