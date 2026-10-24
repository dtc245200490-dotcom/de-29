<?php
session_start();
require_once __DIR__ . '/../includes/db.php';
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
$error = '';
if (isset($_GET['logout'])) { $_SESSION = []; session_destroy(); header('Location: /admin/'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if (password_verify($_POST['password'], getenv('ADMIN_PASSWORD_HASH') ?: '')) {
        session_regenerate_id(true); $_SESSION['admin'] = true; header('Location: /admin/'); exit;
    }
    $error = 'Mật khẩu chưa đúng.';
}
if (empty($_SESSION['admin'])): ?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Đăng nhập quản trị</title><link rel="stylesheet" href="/css/style.css"></head><body><main class="login-box"><a class="brand" href="/">🍲 Bếp Nhà</a><h1>Quản trị</h1><p>Đăng nhập để xem và quản lý công thức.</p><?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?><form method="post"><label>Mật khẩu quản trị<input type="password" name="password" required autocomplete="current-password"></label><button type="submit">Đăng nhập</button></form><a href="/">← Về trang chủ</a></main></body></html>
<?php exit; endif;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['title'])) {
    $title = trim($_POST['title'] ?? ''); $description = trim($_POST['description'] ?? '');
    $category = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $time = filter_input(INPUT_POST, 'cooking_time', FILTER_VALIDATE_INT);
    $servings = filter_input(INPUT_POST, 'servings', FILTER_VALIDATE_INT);
    $image = trim($_POST['image_url'] ?? '');
    $instructions = trim($_POST['instructions'] ?? '');
    $ingredientNames = $_POST['ingredient_name'] ?? []; $quantities = $_POST['quantity'] ?? []; $units = $_POST['unit'] ?? [];
    if ($title && $category && $time > 0 && $servings > 0 && $instructions) {
        $pdo->beginTransaction();
        try {
            $s = $pdo->prepare('INSERT INTO recipes (category_id,title,description,image_url,cooking_time,servings,instructions) VALUES (?,?,?,?,?,?,?)');
            $s->execute([$category,$title,$description,$image,$time,$servings,$instructions]); $rid=(int)$pdo->lastInsertId();
            $ins=$pdo->prepare('INSERT INTO ingredients (recipe_id,name,quantity,unit) VALUES (?,?,?,?)');
            foreach ($ingredientNames as $i=>$name) { $name=trim($name); if ($name!=='') $ins->execute([$rid,$name,trim($quantities[$i]??''),trim($units[$i]??'')]); }
            $pdo->commit(); header('Location: /admin/?saved=1'); exit;
        } catch (Throwable $e) { $pdo->rollBack(); error_log($e->getMessage()); $error='Không thể lưu công thức.'; }
    } else { $error='Vui lòng nhập tên, danh mục, thời gian, số phần và cách làm.'; }
}
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) { $s=$pdo->prepare('DELETE FROM recipes WHERE id=?'); $s->execute([(int)$_GET['delete']]); header('Location: /admin/?deleted=1'); exit; }
$categories=$pdo->query('SELECT id,name FROM categories ORDER BY name')->fetchAll();
$recipes=$pdo->query('SELECT r.id,r.title,c.name category_name FROM recipes r JOIN categories c ON c.id=r.category_id ORDER BY r.id DESC')->fetchAll();
?>
<!doctype html><html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Quản trị · Bếp Nhà</title><link rel="stylesheet" href="/css/style.css"></head><body><header class="simple-nav"><a class="brand" href="/">🍲 Bếp Nhà</a><a href="/admin/?logout=1">Đăng xuất</a></header><main class="container admin"><h1>Quản lý công thức</h1><?php if(isset($_GET['saved'])):?><p class="success">Đã thêm công thức.</p><?php endif;?><?php if(isset($_GET['deleted'])):?><p class="success">Đã xóa công thức.</p><?php endif;?><?php if($error):?><p class="error"><?=e($error)?></p><?php endif;?>
<form method="post" class="admin-form"><h2>Thêm món ăn mới</h2><label>Tên món<input name="title" required maxlength="150"></label><label>Danh mục<select name="category_id" required><?php foreach($categories as $c):?><option value="<?=(int)$c['id']?>"><?=e($c['name'])?></option><?php endforeach;?></select></label><label>Mô tả<textarea name="description" rows="2"></textarea></label><label>Đường dẫn ảnh (URL)<input name="image_url" type="url" placeholder="https://..."></label><div class="form-row"><label>Thời gian (phút)<input type="number" name="cooking_time" min="1" value="30" required></label><label>Số phần ăn<input type="number" name="servings" min="1" value="2" required></label></div><label>Nguyên liệu (mỗi dòng một nguyên liệu theo mẫu: Tên|Số lượng|Đơn vị)<textarea id="ingredients-input" rows="5" placeholder="Thịt gà|500|g&#10;Muối|1|thìa cà phê"></textarea></label><div id="ingredient-hidden"></div><label>Cách làm (mỗi bước một dòng)<textarea name="instructions" rows="6" required></textarea></label><button type="submit">Lưu công thức</button></form>
<h2>Danh sách món ăn</h2><div class="table-wrap"><table><thead><tr><th>ID</th><th>Tên món</th><th>Danh mục</th><th>Thao tác</th></tr></thead><tbody><?php foreach($recipes as $r):?><tr><td><?=(int)$r['id']?></td><td><?=e($r['title'])?></td><td><?=e($r['category_name'])?></td><td><a href="/recipe.php?id=<?=(int)$r['id']?>">Xem</a> · <a class="danger" href="/admin/?delete=<?=(int)$r['id']?>" onclick="return confirm('Bạn chắc chắn muốn xóa món này?')">Xóa</a></td></tr><?php endforeach;?></tbody></table></div></main><script>
document.querySelector('.admin-form').addEventListener('submit',function(){const box=document.getElementById('ingredient-hidden');box.innerHTML='';document.getElementById('ingredients-input').value.split('\n').forEach(line=>{if(!line.trim())return;const p=line.split('|');[['ingredient_name[]',p[0]],['quantity[]',p[1]||''],['unit[]',p[2]||'']].forEach(([name,value])=>{const input=document.createElement('input');input.type='hidden';input.name=name;input.value=value;box.appendChild(input);});});});
</script></body></html>
