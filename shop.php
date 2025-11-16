<?php
$page_title = "Shop";
require_once 'includes/header.php';

// Fetch categories and brands for filters
$categories = [];
$brands = [];
try {
    // Categories
    $stmt = $pdo->query("SELECT id, name FROM categories WHERE status = 'active' ORDER BY name");
    $categories = $stmt->fetchAll();

    // Brands (distinct)
    $stmt = $pdo->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand <> '' ORDER BY brand");
    $brands = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $categories = [];
    $brands = [];
}

// Filters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryParam = isset($_GET['category']) ? trim($_GET['category']) : '';
$brand = isset($_GET['brand']) ? trim($_GET['brand']) : '';
$min_price = isset($_GET['min_price']) ? trim($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? trim($_GET['max_price']) : '';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

$where = ["p.status = 'active'"];
$params = [];
$category_title = '';

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryParam !== '') {
    if (ctype_digit($categoryParam)) {
        $where[] = "p.category_id = ?";
        $params[] = (int)$categoryParam;
        // Set category title
        foreach ($categories as $cat) {
            if ((int)$cat['id'] === (int)$categoryParam) {
                $category_title = $cat['name'];
                break;
            }
        }
    } else {
        // Match by category name (case-insensitive)
        $where[] = "LOWER(c.name) = LOWER(?)";
        $params[] = $categoryParam;
        $category_title = ucfirst($categoryParam);
    }
}

if ($brand !== '') {
    $where[] = "p.brand = ?";
    $params[] = $brand;
}

// Price filters (numeric validation)
if ($min_price !== '' && is_numeric($min_price)) {
    $where[] = "p.price >= ?";
    $params[] = (float)$min_price;
}
if ($max_price !== '' && is_numeric($max_price)) {
    $where[] = "p.price <= ?";
    $params[] = (float)$max_price;
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

// Total count for pagination
try {
    $count_sql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $where_sql";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_products = (int)$stmt->fetchColumn();
} catch (Exception $e) {
    $total_products = 0;
}

$total_pages = max(1, (int)ceil($total_products / $per_page));

// Fetch products with current filters
try {
    $sql = "SELECT p.*, c.name AS category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            $where_sql 
            ORDER BY p.created_at DESC 
            LIMIT $per_page OFFSET $offset";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (Exception $e) {
    $products = [];
}

// Helper to build query string preserving filters but changing page
function build_query($overrides = []) {
    $qs = $_GET;
    foreach ($overrides as $k => $v) {
        if ($v === null) {
            unset($qs[$k]);
        } else {
            $qs[$k] = $v;
        }
    }
    return http_build_query($qs);
}
?>

<div class="container">
    <h1 style="color:#333; margin-bottom: 1rem;">
        Shop <?php echo $category_title ? ('- ' . htmlspecialchars($category_title)) : ''; ?>
    </h1>

    <!-- Filters -->
    <form method="GET" class="form-container" style="max-width: 100%;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div class="form-group">
                <label for="search">Search</label>
                <input type="text" id="search" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
            </div>

            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($categoryParam !== '' && (string)$categoryParam === (string)$cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="brand">Brand</label>
                <select id="brand" name="brand">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo htmlspecialchars($b); ?>" <?php echo $brand === $b ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="min-price">Min Price</label>
                <input type="number" step="0.01" id="min-price" name="min_price" value="<?php echo htmlspecialchars($min_price); ?>">
            </div>
            <div class="form-group">
                <label for="max-price">Max Price</label>
                <input type="number" step="0.01" id="max-price" name="max_price" value="<?php echo htmlspecialchars($max_price); ?>">
            </div>

            <div class="form-group">
                <button type="submit" class="btn-submit">Apply Filters</button>
            </div>
        </div>
        <?php if ($search || $categoryParam || $brand || $min_price || $max_price): ?>
            <div style="margin-top: 1rem;">
                <a href="/computer_shop/shop.php" class="btn-register">Clear Filters</a>
            </div>
        <?php endif; ?>
    </form>

    <!-- Products Grid -->
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <div class="alert alert-info" style="grid-column: 1 / -1;">No products found. Try adjusting your filters.</div>
        <?php else: ?>
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <a href="/computer_shop/product.php?id=<?php echo $product['id']; ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-image">
                            <?php if (!empty($product['image'])): ?>
                                <img src="/computer_shop/upload/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width:100%; height:100%; object-fit:cover;">
                            <?php else: ?>
                                <i class="fas fa-image"></i>
                            <?php endif; ?>
                        </div>
                    </a>
                    <div class="product-info">
                        <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-brand"><?php echo htmlspecialchars($product['brand'] ?: ''); ?></div>
                        <div class="product-price">$<?php echo number_format($product['price'], 2); ?></div>
                        <?php if (isLoggedIn()): ?>
                            <button class="btn-add-cart" data-product-id="<?php echo $product['id']; ?>">Add to Cart</button>
                        <?php else: ?>
                            <a class="btn-add-cart" href="/computer_shop/login.php">Login to Purchase</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div style="display: flex; justify-content:center; gap: 0.5rem; margin-top: 2rem;">
            <?php 
            $start = max(1, $page - 2);
            $end = min($total_pages, $page + 2);
            if ($page > 1): ?>
                <a class="btn-register" href="/computer_shop/shop.php?<?php echo build_query(['page' => $page - 1]); ?>">Prev</a>
            <?php endif; ?>
            <?php for ($i = $start; $i <= $end; $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="btn-login" style="pointer-events:none;"><?php echo $i; ?></span>
                <?php else: ?>
                    <a class="btn-register" href="/computer_shop/shop.php?<?php echo build_query(['page' => $i]); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($page < $total_pages): ?>
                <a class="btn-register" href="/computer_shop/shop.php?<?php echo build_query(['page' => $page + 1]); ?>">Next</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>