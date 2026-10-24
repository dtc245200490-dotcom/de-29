<?php
require_once __DIR__ . '/includes/db.php';
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit('Không tìm thấy công thức.'); }
$stmt = $pdo->prepare("SELECT r.*, c.name AS category_name FROM recipes r JOIN categories c ON c.id=r.category_id WHERE r.id=?");
$stmt->execute([$id]); $recipe = $stmt->fetch();
if (!$recipe) { http_response_code(404); exit('Không tìm thấy công thức.'); }
$stmt = $pdo->prepare("SELECT name, quantity, unit FROM ingredients WHERE recipe_id=? ORDER BY id");
$stmt->execute([$id]); $ingredients = $stmt->fetchAll();
$steps = array_filter(array_map('trim', explode("\n", $recipe['instructions'])));
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($recipe['title']) ?> · Bếp Nhà</title><link rel="stylesheet" href="/css/style.css"></head><body>
<header class="simple-nav"><a class="brand" href="/">🍲 Bếp Nhà</a><a href="/">← Tất cả công thức</a></header>
<main class="detail container"><span class="tag"><?= e($recipe['category_name']) ?></span><h1><?= e($recipe['title']) ?></h1><p class="lead"><?= e($recipe['description']) ?></p><img class="detail-image" src="<?= e($recipe['image_url']) ?>" alt="<?= e($recipe['title']) ?>"><div class="meta detail-meta">⏱ <?= (int)$recipe['cooking_time'] ?> phút <span>🍽 <?= (int)$recipe['servings'] ?> phần</span></div>
<div class="detail-columns"><section><h2>Nguyên liệu</h2><ul class="ingredients"><?php foreach ($ingredients as $item): ?><li><span><?= e($item['name']) ?></span><strong><?= e(trim($item['quantity'].' '.$item['unit'])) ?></strong></li><?php endforeach; ?></ul></section>
<section><h2>Cách thực hiện</h2><ol class="steps"><?php foreach ($steps as $step): ?><li><?= e($step) ?></li><?php endforeach; ?></ol></section></div></main><footer>© <?= date('Y') ?> Bếp Nhà</footer></body></html>
