<?php
$page_title = "Checkout";
require_once 'includes/header.php';
require_once 'includes/auth.php';

requireLogin();

$errors = [];
$success = '';
$order_id = null;
$order_total = 0.0;
$order_items = [];

function get_cart_items($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.image, p.brand, p.stock_quantity, p.status
                           FROM cart c
                           JOIN products p ON c.product_id = p.id
                           WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name   = trim($_POST['full_name'] ?? '');
    $address1    = trim($_POST['address1'] ?? '');
    $city        = trim($_POST['city'] ?? '');
    $state       = trim($_POST['state'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $country     = trim($_POST['country'] ?? '');
    $phone       = trim($_POST['phone'] ?? '');
    $payment_method = trim($_POST['payment_method'] ?? 'cod');

    // Basic validation
    if ($full_name === '' || $address1 === '' || $city === '' || $state === '' || $postal_code === '' || $country === '' || $phone === '') {
        $errors[] = 'Please fill in all required shipping fields.';
    }

    // Fetch cart
    $items = get_cart_items($pdo, $_SESSION['user_id']);
    if (empty($items)) {
        $errors[] = 'Your cart is empty.';
    }

    if (empty($errors)) {
        $shipping_address = $full_name . "\n" . $address1 . "\n" . $city . ', ' . $state . ' ' . $postal_code . "\n" . $country . "\nPhone: " . $phone;

        try {
            $pdo->beginTransaction();

            // Validate stock and compute total
            $total = 0.0;
            foreach ($items as $it) {
                if ($it['status'] !== 'active') {
                    throw new Exception('Product "' . $it['name'] . '" is no longer available.');
                }
                if ((int)$it['stock_quantity'] < (int)$it['quantity']) {
                    throw new Exception('Insufficient stock for "' . $it['name'] . '".');
                }
                $total += (float)$it['price'] * (int)$it['quantity'];
            }

            // Insert order
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method) VALUES (?, ?, 'pending', ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $shipping_address, $payment_method]);
            $new_order_id = (int)$pdo->lastInsertId();

            // Insert order items and decrement stock
            $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmtStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");

            foreach ($items as $it) {
                $stmtItem->execute([$new_order_id, $it['product_id'], (int)$it['quantity'], (float)$it['price']]);
                $stmtStock->execute([(int)$it['quantity'], $it['product_id'], (int)$it['quantity']]);
                if ($stmtStock->rowCount() === 0) {
                    throw new Exception('Failed to update stock for "' . $it['name'] . '".');
                }
            }

            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);

            $pdo->commit();

            $order_id = $new_order_id;
            $order_total = $total;
            $order_items = $items;
            $success = 'Order placed successfully.';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = $e->getMessage();
        }
    }
}

// For GET or after failure, load current cart to display summary
$cart_items = [];
if (!$order_id) {
    try {
        $cart_items = get_cart_items($pdo, $_SESSION['user_id']);
    } catch (Exception $e) {
        $cart_items = [];
    }
}
?>

<div class="container">
    <h1 style="color:#333; margin-bottom: 1rem;">Checkout</h1>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $err): ?>
                <div><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success && $order_id): ?>
        <div class="alert alert-success">Order #<?php echo $order_id; ?> placed successfully.</div>
        <div class="form-container" style="max-width:100%;">
            <h2>Order Summary</h2>
            <div style="margin: 1rem 0;">
                <?php foreach ($order_items as $it): ?>
                    <div style="display:flex; justify-content: space-between; margin-bottom:0.5rem;">
                        <div><?php echo htmlspecialchars($it['name']); ?> x <?php echo (int)$it['quantity']; ?></div>
                        <div>$<?php echo number_format((float)$it['price'] * (int)$it['quantity'], 2); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="cart-total" style="text-align:left;">
                <div class="total-amount">Total Paid: $<?php echo number_format($order_total, 2); ?></div>
            </div>
            <div style="margin-top: 1rem;">
                <a class="btn-login" href="/computer_shop/shop.php">Continue Shopping</a>
            </div>
        </div>
    <?php else: ?>
        <?php if (empty($cart_items)): ?>
            <div class="alert alert-info">Your cart is empty. <a href="/computer_shop/shop.php" style="color:#e91e63;">Go to shop</a>.</div>
        <?php else: ?>
            <div class="form-container">
                <h2>Shipping Information</h2>
                <form method="POST" id="checkout-form">
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ($_SESSION['full_name'] ?? '')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="address1">Address *</label>
                        <input type="text" id="address1" name="address1" required value="<?php echo htmlspecialchars($_POST['address1'] ?? ''); ?>">
                    </div>

                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div class="form-group">
                            <label for="city">City *</label>
                            <input type="text" id="city" name="city" required value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="state">State/Province *</label>
                            <input type="text" id="state" name="state" required value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="postal_code">Postal Code *</label>
                            <input type="text" id="postal_code" name="postal_code" required value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="country">Country *</label>
                            <input type="text" id="country" name="country" required value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="text" id="phone" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method">
                            <option value="cod" <?php echo (($_POST['payment_method'] ?? '') === 'cod') ? 'selected' : ''; ?>>Cash on Delivery</option>
                            <option value="card" <?php echo (($_POST['payment_method'] ?? '') === 'card') ? 'selected' : ''; ?>>Credit/Debit Card (offline)</option>
                            <option value="paypal" <?php echo (($_POST['payment_method'] ?? '') === 'paypal') ? 'selected' : ''; ?>>PayPal (offline)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">Place Order</button>
                </form>
            </div>

            <div class="form-container" style="max-width:100%;">
                <h2>Order Summary</h2>
                <?php 
                    $sum = 0.0; 
                    foreach ($cart_items as $it) { $sum += (float)$it['price'] * (int)$it['quantity']; }
                ?>
                <div style="margin: 1rem 0;">
                    <?php foreach ($cart_items as $it): ?>
                        <div style="display:flex; justify-content: space-between; margin-bottom:0.5rem;">
                            <div><?php echo htmlspecialchars($it['name']); ?> x <?php echo (int)$it['quantity']; ?></div>
                            <div>$<?php echo number_format((float)$it['price'] * (int)$it['quantity'], 2); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="cart-total" style="text-align:left;">
                    <div class="total-amount">Total: $<?php echo number_format($sum, 2); ?></div>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
const checkoutForm = document.getElementById('checkout-form');
if (checkoutForm) {
    checkoutForm.addEventListener('submit', function(e) {
        if (!validateForm('checkout-form')) {
            e.preventDefault();
            showAlert('Please fill in all required fields correctly.', 'error');
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>