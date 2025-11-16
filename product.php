<?php
$page_title = "Product";
require_once 'includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo '<div class="container"><div class="alert alert-error">Invalid product.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} catch (Exception $e) {
    $product = null;
}

if (!$product) {
    echo '<div class="container"><div class="alert alert-error">Product not found.</div></div>';
    require_once 'includes/footer.php';
    exit;
}

// Related products: same category
try {
    $stmt = $pdo->prepare("SELECT id, name, price, image, brand FROM products WHERE status = 'active' AND category_id = ? AND id <> ? ORDER BY created_at DESC LIMIT 4");
    $stmt->execute([$product['category_id'], $product['id']]);
    $related = $stmt->fetchAll();
} catch (Exception $e) {
    $related = [];
}
?>

<div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem; align-items: start;">
        <div>
            <div class="product-image" style="height: 400px;">
                <?php if (!empty($product['image'])): ?>
                    <img src="/computer_shop/upload/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                <?php else: ?>
                    <i class="fas fa-image"></i>
                <?php endif; ?>
            </div>
        </div>
        <div>
            <h1 style="margin-bottom: 0.5rem; color:#333;"><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="product-brand">Brand: <?php echo htmlspecialchars($product['brand'] ?: 'N/A'); ?></div>
            <div class="product-brand">Category: <?php echo htmlspecialchars($product['category_name'] ?: ''); ?></div>
            <div class="product-price" style="font-size:1.8rem;">$<?php echo number_format($product['price'], 2); ?></div>
            <div style="margin: 1rem 0; color: #666;">Stock: <?php echo (int)$product['stock_quantity'] > 0 ? (int)$product['stock_quantity'] : 'Out of stock'; ?></div>
            <p style="margin: 1rem 0; white-space: pre-line;"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

            <?php if (isLoggedIn()): ?>
                <?php if ((int)$product['stock_quantity'] > 0): ?>
                    <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                <?php else: ?>
                    <button class="btn-add-cart" disabled>Out of Stock</button>
                <?php endif; ?>
            <?php else: ?>
                <a class="btn-add-cart" href="/computer_shop/login.php">Login to Purchase</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($related)): ?>
        <h2 style="margin: 3rem 0 1rem; color: #333;">Related Products</h2>
        <div class="products-grid">
            <?php foreach ($related as $p): ?>
                <div class="product-card">
                    <a href="/computer_shop/product.php?id=<?php echo $p['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-image">
                            <?php if (!empty($p['image'])): ?>
                                <img src="/computer_shop/upload/<?php echo htmlspecialchars($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <i class="fas fa-image"></i>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                        <div class="product-brand"><?php echo htmlspecialchars($p['brand'] ?: ''); ?></div>
                        <div class="product-price">$<?php echo number_format($p['price'], 2); ?></div>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn-add-cart" data-product-id="<?php echo $p['id']; ?>">Add to Cart</button>
                        <?php else: ?>
                            <a class="btn-add-cart" href="/computer_shop/login.php">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>