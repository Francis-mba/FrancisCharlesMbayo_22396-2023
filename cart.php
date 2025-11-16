<?php
$page_title = "Cart";
require_once 'includes/header.php';
require_once 'includes/auth.php';

requireLogin();

// Fetch cart items for current user
try {
    $stmt = $pdo->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.image, p.brand, p.stock_quantity 
                           FROM cart c 
                           JOIN products p ON c.product_id = p.id 
                           WHERE c.user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $items = $stmt->fetchAll();
} catch (Exception $e) {
    $items = [];
}

$total = 0.0;
foreach ($items as $it) {
    $total += (float)$it['price'] * (int)$it['quantity'];
}

// Orders section
$order = null;
$order_items = [];
if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $id = (int)$_GET['id'];
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        $order = $stmt->fetch();
        
        if ($order) {
            $stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
            $stmt->execute([$id]);
            $order_items = $stmt->fetchAll();
        }
    } catch (Exception $e) {
        $order = null;
    }
}

// Otherwise list orders
$orders = [];
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$total_orders = 0;

if (!$order) {
    try {
        $total_orders = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE user_id = " . (int)$_SESSION['user_id'])->fetchColumn();
        $stmt = $pdo->query("SELECT id, total_amount, status, created_at FROM orders WHERE user_id = " . (int)$_SESSION['user_id'] . " ORDER BY created_at DESC LIMIT $per_page OFFSET $offset");
        $orders = $stmt->fetchAll();
    } catch (Exception $e) {
        $orders = [];
    }
}
?>

<div class="container">
    <h1 style="color:#333; margin-bottom: 1rem;">Your Cart</h1>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">Your cart is empty. <a href="/computer_shop/shop.php" style="color:#e91e63;">Continue shopping</a>.</div>
    <?php else: ?>
        <div>
            <?php foreach ($items as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <?php if (!empty($item['image'])): ?>
                            <img src="/computer_shop/upload/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width:80px; height:80px; object-fit:cover;">
                        <?php else: ?>
                            <i class="fas fa-image" style="font-size:2rem; color:#ccc;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="cart-item-info">
                        <h3 style="margin-bottom: 0.5rem;">
                            <a href="/computer_shop/product.php?id=<?php echo $item['product_id']; ?>" style="text-decoration:none; color:#333;">
                                <?php echo htmlspecialchars($item['name']); ?>
                            </a>
                        </h3>
                        <div class="product-brand" style="margin-bottom:0.25rem;">Brand: <?php echo htmlspecialchars($item['brand'] ?: ''); ?></div>
                        <div class="product-price">$<?php echo number_format($item['price'], 2); ?></div>
                    </div>
                    <div class="cart-item-actions">
                        <input type="number" min="1" class="quantity-input" data-cart-id="<?php echo $item['cart_id']; ?>" value="<?php echo (int)$item['quantity']; ?>" <?php echo (int)$item['stock_quantity'] <= 0 ? 'disabled' : ''; ?>>
                        <button class="btn-remove" data-cart-id="<?php echo $item['cart_id']; ?>">Remove</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="cart-total">
            <div class="total-amount">Total: $<?php echo number_format($total, 2); ?></div>
            <a href="/computer_shop/checkout.php" class="btn-login" style="display:inline-block; margin-top: 1rem;">Proceed to Checkout</a>
        </div>
    <?php endif; ?>

    <hr style="margin: 2rem 0;">
    <h2 style="color:#333; margin-bottom: 1rem;">My Orders</h2>

    <?php if ($order): ?>
        <div class="form-container" style="max-width:100%;">
            <h3>Order #<?php echo (int)$order['id']; ?></h3>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
            <p><strong>Created:</strong> <?php echo htmlspecialchars($order['created_at']); ?></p>
            <p><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
            <p><strong>Shipping Address:</strong><br><pre style="white-space:pre-wrap; background:#f8f9fa; padding:1rem; border-radius:6px;"><?php echo htmlspecialchars($order['shipping_address']); ?></pre></p>

            <h4>Items</h4>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Product</th>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Qty</th>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Price</th>
                            <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_items as $it): ?>
                            <tr>
                                <td style="padding:8px; border-bottom:1px solid #f5f5f5;"><?php echo htmlspecialchars($it['name']); ?></td>
                                <td style="padding:8px; border-bottom:1px solid #f5f5f5;"><?php echo (int)$it['quantity']; ?></td>
                                <td style="padding:8px; border-bottom:1px solid #f5f5f5;">$<?php echo number_format((float)$it['price'], 2); ?></td>
                                <td style="padding:8px; border-bottom:1px solid #f5f5f5;">$<?php echo number_format((float)$it['price']*(int)$it['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="cart-total" style="text-align:left; margin-top:1rem;">
                <div class="total-amount">Total: $<?php echo number_format((float)$order['total_amount'], 2); ?></div>
            </div>

            <a href="/computer_shop/cart.php" class="btn-register" style="margin-top: 1rem;">Back to Cart</a>
        </div>
    <?php else: ?>
        <div class="form-container" style="max-width:100%;">
            <?php if (empty($orders)): ?>
                <div class="alert alert-info">No orders found.</div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table style="width:100%; border-collapse: collapse;">
                        <thead>
                            <tr>
                                <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Order #</th>
                                <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Amount</th>
                                <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Status</th>
                                <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Date</th>
                                <th style="text-align:left; padding:8px; border-bottom:1px solid #eee;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td style="padding:8px; border-bottom:1px solid #f5f5f5;">#<?php echo (int)$o['id']; ?></td>
                                    <td style="padding:8px; border-bottom:1px solid #f5f5f5;">$<?php echo number_format((float)$o['total_amount'], 2); ?></td>
                                    <td style="padding:8px; border-bottom:1px solid #f5f5f5; text-transform:capitalize;"><?php echo htmlspecialchars($o['status']); ?></td>
                                    <td style="padding:8px; border-bottom:1px solid #f5f5f5;"><?php echo htmlspecialchars($o['created_at']); ?></td>
                                    <td style="padding:8px; border-bottom:1px solid #f5f5f5;">
                                        <a class="btn-register" href="/computer_shop/cart.php?id=<?php echo (int)$o['id']; ?>">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php $total_pages = max(1, (int)ceil($total_orders / $per_page)); if ($total_pages > 1): ?>
                    <div style="display:flex; gap:0.5rem; justify-content:center; margin-top:1rem;">
                        <?php if ($page > 1): ?><a class="btn-register" href="/computer_shop/cart.php?page=<?php echo $page-1; ?>">Prev</a><?php endif; ?>
                        <span class="btn-login" style="pointer-events:none;">Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                        <?php if ($page < $total_pages): ?><a class="btn-register" href="/computer_shop/cart.php?page=<?php echo $page+1; ?>">Next</a><?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>