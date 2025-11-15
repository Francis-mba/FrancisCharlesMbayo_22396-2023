<?php
$page_title = "Home";
require_once 'includes/header.php';
require_once 'config/db.php';

// Get featured products
$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    WHERE p.status = 'active' 
    ORDER BY p.created_at DESC 
    LIMIT 6
");
$stmt->execute();
$featured_products = $stmt->fetchAll();

// Get categories
$stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$stmt->execute();
$categories = $stmt->fetchAll();
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Welcome to Computer & Electronic Shop</h1>
        <p>Discover the latest trends in computer and electronic products</p>
        <a href="/computer_shop/shop.php" class="btn-primary">Shop Now</a>
    </div>
</section>

<div class="container">
    <!-- Categories Section -->
    <section class="categories-section">
        <h2 style="text-align: center; margin-bottom: 2rem; color: #333;">Shop by Category</h2>
        <div class="products-grid">
            <?php foreach ($categories as $category): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($category['image']): ?>
                            <img src="/computer_shop/upload/<?php echo htmlspecialchars($category['image']); ?>"
                                 alt="<?php echo htmlspecialchars($category['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-tag"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo htmlspecialchars($category['description']); ?></p>
                        <a href="/computer_shop/shop.php?category=<?php echo $category['id']; ?>" class="btn-add-cart">
                            Browse Category
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products">
        <h2 style="text-align: center; margin: 3rem 0 2rem; color: #333;">Featured Products</h2>
        <div class="products-grid">
            <?php foreach ($featured_products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($product['image']): ?>
                            <img src="/computer_shop/upload/<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="fas fa-image"></i>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-brand"><?php echo htmlspecialchars($product['brand']); ?></div>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <a href="/computer_shop/login.php" class="btn-add-cart">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" style="margin: 3rem 0; text-align: center;">
        <h2 style="margin-bottom: 2rem; color: #333;">Why Choose Us?</h2>
        <div class="products-grid">
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-shipping-fast" style="color: #007bff;"></i>
                </div>
                <div class="product-info">
                    <h3>Fast Shipping</h3>
                    <p>Quick and reliable delivery to your doorstep</p>
                </div>
            </div>
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-shield-alt" style="color: #007bff;"></i>
                </div>
                <div class="product-info">
                    <h3>Secure Shopping</h3>
                    <p>Your personal information is safe with us</p>
                </div>
            </div>
            <div class="product-card">
                <div class="product-image">
                    <i class="fas fa-headset" style="color: #007bff;"></i>
                </div>
                <div class="product-info">
                    <h3>24/7 Support</h3>
                    <p>We're here to help you anytime you need</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>