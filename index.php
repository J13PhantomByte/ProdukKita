<?php
declare(strict_types=1);

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'httponly' => true,
    'secure' => $isHttps,
    'samesite' => 'Lax',
]);
session_start();

$nonce = bin2hex(random_bytes(16));
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net; style-src 'self' https://cdn.jsdelivr.net; img-src 'self' https://images.unsplash.com data:; font-src 'self' https://cdn.jsdelivr.net data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");

require __DIR__ . '/config.php';

function e(mixed $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirectHome(): never {
    header('Location: index.php', true, 303);
    exit;
}
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$allowedCategories = ['Makanan', 'Minuman', 'Dessert', 'Lainnya'];
$errors = [];
$form = ['id' => '', 'nama' => '', 'barang' => 'Makanan', 'harga' => '', 'stok' => '', 'gambar' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman dan coba lagi.');
    }

    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id) {
            setFlash('danger', 'ID produk tidak valid.');
        } else {
            $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
            $stmt->execute(['id' => $id]);
            setFlash($stmt->rowCount() ? 'success' : 'warning', $stmt->rowCount() ? 'Produk berhasil dihapus.' : 'Produk tidak ditemukan.');
        }
        redirectHome();
    }

    if ($action === 'create' || $action === 'update') {
        $idRaw = $_POST['id'] ?? '';
        $form['id'] = is_scalar($idRaw) ? (string)$idRaw : '';
        foreach (['nama', 'barang', 'harga', 'stok', 'gambar'] as $key) {
            $value = $_POST[$key] ?? '';
            $form[$key] = is_string($value) ? trim($value) : '';
        }

        if ($form['nama'] === '' || mb_strlen($form['nama']) > 120) {
            $errors[] = 'Nama produk wajib diisi (maksimal 120 karakter).';
        }
        if (!in_array($form['barang'], $allowedCategories, true)) {
            $errors[] = 'Kategori produk tidak valid.';
        }
        $harga = filter_var($form['harga'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
        $stok = filter_var($form['stok'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100000000]]);
        if ($harga === false) $errors[] = 'Harga harus berupa bilangan bulat positif atau nol.';
        if ($stok === false) $errors[] = 'Stok harus berupa bilangan bulat positif atau nol.';

        // Batasi gambar ke HTTPS Unsplash agar tidak menerima skema berbahaya atau host sembarang.
        $imageUrl = filter_var($form['gambar'], FILTER_VALIDATE_URL);
        $host = $imageUrl ? strtolower((string)parse_url($form['gambar'], PHP_URL_HOST)) : '';
        if (!$imageUrl || strtolower((string)parse_url($form['gambar'], PHP_URL_SCHEME)) !== 'https' || !in_array($host, ['images.unsplash.com'], true) || mb_strlen($form['gambar']) > 500) {
            $errors[] = 'URL gambar harus berupa HTTPS dari images.unsplash.com.';
        }

        if ($action === 'update' && !filter_var($form['id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
            $errors[] = 'ID produk tidak valid.';
        }

        if (!$errors) {
            $data = [
                'nama' => $form['nama'],
                'barang' => $form['barang'],
                'harga' => $harga,
                'stok' => $stok,
                'gambar' => $form['gambar'],
            ];
            if ($action === 'create') {
                $stmt = $pdo->prepare('INSERT INTO products (nama, barang, harga, stok, gambar) VALUES (:nama, :barang, :harga, :stok, :gambar)');
                $stmt->execute($data);
                setFlash('success', 'Produk berhasil ditambahkan.');
            } else {
                $data['id'] = (int)$form['id'];
                $stmt = $pdo->prepare('UPDATE products SET nama=:nama, barang=:barang, harga=:harga, stok=:stok, gambar=:gambar WHERE id=:id');
                $stmt->execute($data);
                setFlash('success', $stmt->rowCount() ? 'Produk berhasil diperbarui.' : 'Tidak ada perubahan atau produk tidak ditemukan.');
            }
            redirectHome();
        }
    }
}

$editId = filter_input(INPUT_GET, 'edit', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $editId) {
    $stmt = $pdo->prepare('SELECT id, nama, barang, harga, stok, gambar FROM products WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $existing = $stmt->fetch();
    if ($existing) {
        $form = array_map(static fn($v) => (string)$v, $existing);
    } else {
        setFlash('warning', 'Produk yang ingin diedit tidak ditemukan.');
        redirectHome();
    }
}

$search = trim((string)($_GET['q'] ?? ''));
$categoryFilter = (string)($_GET['kategori'] ?? '');
$where = [];
$params = [];
if ($search !== '') {
    $where[] = '(nama LIKE :q1 OR barang LIKE :q2)';
    $escapedSearch = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
    $params['q1'] = $escapedSearch;
    $params['q2'] = $escapedSearch;
}
if (in_array($categoryFilter, $allowedCategories, true)) {
    $where[] = 'barang = :kategori';
    $params['kategori'] = $categoryFilter;
}
$sql = 'SELECT id, nama, barang, harga, stok, gambar FROM products' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$isEditing = $form['id'] !== '';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f7f8fc">
  <title>Manajemen Produk | ProdukKita</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body>
<nav class="topbar">
  <div class="container topbar-inner">
    <a class="brand" href="index.php">ProdukKita<span>.</span></a>
    <span class="topbar-note">Dashboard katalog produk</span>
  </div>
</nav>
<main class="container py-4 py-lg-5">
  <header class="page-heading">
    <div><p class="eyebrow">MANAJEMEN KATALOG</p><h1>Daftar Produk</h1><p class="muted mb-0">Kelola produk, harga, stok, dan kategori dari satu tempat.</p></div>
    <a class="btn btn-primary btn-add" href="#product-form">+ Tambah Produk</a>
  </header>

  <?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div><?php endif; ?>
  <?php if ($errors): ?><div class="alert alert-danger" role="alert"><strong>Periksa kembali data berikut:</strong><ul class="mb-0 mt-2"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>

  <section class="panel form-panel" id="product-form" aria-labelledby="form-title">
    <div class="section-heading"><div><p class="eyebrow mb-1"><?= $isEditing ? 'PERBARUI DATA' : 'DATA BARU' ?></p><h2 id="form-title"><?= $isEditing ? 'Edit Produk' : 'Tambah Produk' ?></h2></div><?php if ($isEditing): ?><a class="btn btn-light btn-sm" href="index.php">Batal edit</a><?php endif; ?></div>
    <form method="post" action="index.php<?= $isEditing ? '?edit=' . (int)$form['id'] : '' ?>" class="row g-3" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
      <input type="hidden" name="action" value="<?= $isEditing ? 'update' : 'create' ?>">
      <?php if ($isEditing): ?><input type="hidden" name="id" value="<?= (int)$form['id'] ?>"><?php endif; ?>
      <div class="col-12 col-md-6"><label for="nama" class="form-label">Nama produk</label><input class="form-control" id="nama" name="nama" maxlength="120" required value="<?= e($form['nama']) ?>" placeholder="Contoh: Es Kopi Susu"></div>
      <div class="col-12 col-md-6"><label for="barang" class="form-label">Kategori</label><select class="form-select" id="barang" name="barang" required><?php foreach ($allowedCategories as $category): ?><option value="<?= e($category) ?>" <?= $form['barang'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
      <div class="col-12 col-sm-6 col-md-3"><label for="harga" class="form-label">Harga (Rupiah)</label><input class="form-control" id="harga" name="harga" type="number" min="0" max="2147483647" step="1" required value="<?= e($form['harga']) ?>" placeholder="15000"></div>
      <div class="col-12 col-sm-6 col-md-3"><label for="stok" class="form-label">Stok</label><input class="form-control" id="stok" name="stok" type="number" min="0" max="100000000" step="1" required value="<?= e($form['stok']) ?>" placeholder="10"></div>
      <div class="col-12 col-md-6"><label for="gambar" class="form-label">URL gambar</label><input class="form-control" id="gambar" name="gambar" type="url" maxlength="500" required value="<?= e($form['gambar']) ?>" placeholder="https://images.unsplash.com/..."><div class="form-text">Gunakan URL HTTPS dari images.unsplash.com.</div></div>
      <div class="col-12 d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit"><?= $isEditing ? 'Simpan Perubahan' : 'Simpan Produk' ?></button><?php if ($isEditing): ?><a class="btn btn-light" href="index.php">Batal</a><?php endif; ?></div>
    </form>
  </section>

  <section class="catalog-section" aria-labelledby="catalog-title">
    <div class="section-heading"><div><p class="eyebrow mb-1">KATALOG</p><h2 id="catalog-title">Produk tersimpan <span class="count-pill"><?= count($products) ?></span></h2></div></div>
    <form method="get" class="filter-bar" role="search">
      <div class="search-wrap"><label class="visually-hidden" for="q">Cari produk</label><input class="form-control" id="q" name="q" value="<?= e($search) ?>" maxlength="100" placeholder="Cari nama atau kategori..."></div>
      <div><label class="visually-hidden" for="kategori">Filter kategori</label><select class="form-select" id="kategori" name="kategori"><option value="">Semua kategori</option><?php foreach ($allowedCategories as $category): ?><option value="<?= e($category) ?>" <?= $categoryFilter === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
      <button class="btn btn-outline-primary" type="submit">Cari</button><?php if ($search !== '' || $categoryFilter !== ''): ?><a class="btn btn-light" href="index.php">Reset</a><?php endif; ?>
    </form>

    <?php if (!$products): ?><div class="empty-state"><h3>Produk belum ditemukan</h3><p class="muted mb-0">Coba kata kunci lain atau tambahkan produk baru.</p></div><?php else: ?>
    <div class="row g-3 g-lg-4">
      <?php foreach ($products as $product): ?>
      <div class="col-6 col-md-4 col-xl-3"><article class="product-card">
        <img class="product-image" src="<?= e($product['gambar']) ?>" alt="<?= e($product['nama']) ?>" width="800" height="600" loading="lazy" decoding="async">
        <div class="product-body"><span class="category-pill"><?= e($product['barang']) ?></span><h3><?= e($product['nama']) ?></h3><p class="stock-line">Stok <strong><?= (int)$product['stok'] ?></strong></p><p class="price">Rp<?= number_format((int)$product['harga'], 0, ',', '.') ?></p>
          <div class="card-actions"><a class="btn btn-sm btn-outline-primary" href="index.php?edit=<?= (int)$product['id'] ?>#product-form">Edit</a><form method="post" action="index.php" class="delete-form"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$product['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-product-name="<?= e($product['nama']) ?>">Hapus</button></form></div>
        </div>
      </article></div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </section>
  <footer class="footer-note">ProdukKita · PHP Native, PDO, MySQL</footer>
</main>
<script src="assets/app.js" defer></script>
</body>
</html>
