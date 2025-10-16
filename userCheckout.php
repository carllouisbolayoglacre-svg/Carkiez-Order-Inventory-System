<?php
include 'Includes/userHeader.php';
include 'Includes/connection.php';
check_login_redirect();

$user_id = $_SESSION['user_id'];

// Get user info
$user_stmt = $conn->prepare("SELECT first_name, last_name, email, contact_number FROM user WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Get cart
$cart_stmt = $conn->prepare("SELECT cart_id FROM cart WHERE user_id = ?");
$cart_stmt->bind_param("i", $user_id);
$cart_stmt->execute();
$cart_result = $cart_stmt->get_result();
$cart = $cart_result->fetch_assoc();
$cart_stmt->close();

$cart_items = [];
$total = 0;

if ($cart) {
    $cart_id = $cart['cart_id'];
    $items_stmt = $conn->prepare("
        SELECT ci.quantity, p.product_name, p.price, p.image_path
        FROM cart_item ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?
    ");
    $items_stmt->bind_param("i", $cart_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    while ($row = $items_result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
    $items_stmt->close();
}
?>
<main>
    <div class="checkout-container">
        <div class="payment-details">
            <h2>Payment Details</h2>
            <form action="Includes/processCheckout.php" method="POST" id="checkout-form">
                <div class="form-group">
                    <div class="radio-group">
                        <input type="radio" id="pay-on-pickup" name="payment_method" value="pay_on_pickup" checked>
                        <label for="pay-on-pickup">Pay on Pickup</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="online-payment" name="payment_method" value="online_payment">
                        <label for="online-payment">Online Payment</label>
                    </div>
                </div>
                <div class="customer-pickup-info">
                    <h3>Customer Details</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($user['contact_number']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <div class="pickup-info">
                    <h3>Pickup Information</h3>
                    <p><strong>Pickup Location:</strong> Carkiez Store, Purok 3, Poblacion, Kapatagan Highway, Kapatagan, Lanao Del Norte</p>
                    <p><small>*Please pick up your order within 3 days after confirmation.</small></p>
                </div>
                <div class="confirm-order">
                    <button type="submit">Confirm Order</button>
                </div>
            </form>
        </div>
        <div class="order-summary">
            <h2>Order Summary</h2>
            <div class="summary-order-items">
                <?php if (count($cart_items) > 0): ?>
                    <?php foreach ($cart_items as $item): ?>
                    <div class="summary-order-item">
                        <div class="summary-item-details">
                            <div class="summary-image">
                                <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="Product Image">
                            </div>
                            <p class="summary-item-name">
                                <?php echo intval($item['quantity']); ?>x <?php echo htmlspecialchars($item['product_name']); ?>
                            </p>
                            <p class="summary-total-price">
                                ₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Your cart is empty.</p>
                <?php endif; ?>
            </div>
            <div class="total-amount">
                <h3>Total</h3>
                <h3>₱<?php echo number_format($total, 2); ?></h3>
            </div>
        </div>
        <div class="confirm-order-mobile">
            <button type="submit" form="checkout-form">Confirm Order</button>
        </div>
    </div>
</main>
<?php include 'Includes/userFooter.php'; ?>