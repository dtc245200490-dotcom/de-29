<?php
require_once __DIR__ . '/includes/db.php';
$q = trim($_GET['q'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category', FILTER_VALIDATE_INT) ?: 0;

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
$sql = "SELECT r.id, r.title, r.description, r.image_url, r.cooking_time, r.servings, c.name AS category_name
        FROM recipes r JOIN categories c ON c.id = r.category_id WHERE 1=1";
$params = [];
if ($q !== '') { $sql .= " AND (r.title LIKE ? OR r.description LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($categoryId > 0) { $sql .= " AND c.id = ?"; $params[] = $categoryId; }
$sql .= " ORDER BY r.created_at DESC";
$stmt = $pdo->prepare($sql); $stmt->execute($params); $recipes = $stmt->fetchAll();
function e($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bếp Nhà - Công thức nấu ăn</title><link rel="stylesheet" href="/css/style.css"></head><body>
<header class="hero"><nav><a class="brand" href="/">🍲 Bếp Nhà</a><a href="/admin/">Quản trị</a></nav>
<div class="hero-content"><p class="eyebrow">NẤU NGON MỖI NGÀY</p><h1>Món ngon bắt đầu từ căn bếp của bạn</h1><p>Tìm cảm hứng và công thức cho bữa ăn tiếp theo.</p>
<form class="search" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Tìm món ăn, ví dụ: phở, gà..." aria-label="Tìm món ăn">
<select name="category"><option value="0">Tất cả danh mục</option><?php foreach ($categories as $cat): ?><option value="<?= (int)$cat['id'] ?>" <?= $categoryId === (int)$cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select><button type="submit">Tìm món</button></form>
</div></header>
<main class="container"><div class="section-heading"><div><p class="eyebrow">GỢI Ý HÔM NAY</p><h2>Công thức dành cho bạn</h2></div><span><?= count($recipes) ?> công thức</span></div>
<div class="recipe-grid"><?php foreach ($recipes as $r): ?><article class="recipe-card"><a href="/recipe.php?id=<?= (int)$r['id'] ?>"><img src="<?= e($r['image_url'] ?: 'https://images.unsplash.com/photo-1547592180-85f173990554?w=900') ?>" alt="<?= e($r['title']) ?>" loading="lazy"><div class="card-body"><span class="tag"><?= e($r['category_name']) ?></span><h3><?= e($r['title']) ?></h3><p><?= e($r['description']) ?></p><div class="meta">⏱ <?= (int)$r['cooking_time'] ?> phút <span>🍽 <?= (int)$r['servings'] ?> phần</span></div></div></a></article><?php endforeach; ?></div>
<?php if (!$recipes): ?><p class="empty">Chưa tìm thấy công thức phù hợp. Hãy thử từ khóa khác.</p><?php endif; ?></main><footer>© <?= date('Y') ?> Bếp Nhà · Chia sẻ niềm vui nấu nướng</footer></body></html>
