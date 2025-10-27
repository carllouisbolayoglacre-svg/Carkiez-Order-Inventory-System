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
            <form action="Includes/processCheckout.php" method="POST" id="checkout-form" enctype="multipart/form-data">
                <div class="form-group">
                    <div class="radio-group">
                        <input type="radio" id="pay-on-pickup" name="payment_method" value="pay_on_pickup" checked>
                        <label for="pay-on-pickup">Pay on Pickup</label>
                    </div>
                    <div class="radio-group">
                        <input type="radio" id="online-payment" name="payment_method" value="online_payment">
                        <label for="online-payment">Pay via Gcash</label>
                    </div>
                </div>

                <!-- GCash Payment Section -->
                <div id="gcash-section" class="gcash-payment-section">
                    <h3>GCash Payment Instructions</h3>
                    <p class="gcash-instruction"><strong>Send your payment to:</strong></p>
                    <img src="Assets/gcashqr.jpg" alt="GCash QR Code" class="gcash-qr-image">
                    
                    <div class="gcash-form-group">
                        <label for="reference_number">GCash Reference No.:</label>
                        <input type="text" name="reference_number" id="reference_number" placeholder="Enter reference number">
                    </div>
                    
                    <div class="gcash-form-group">
                        <label for="proof_image">Upload Screenshot:</label>
                        <input type="file" name="proof_image" id="proof_image" accept="image/*">
                        <small class="file-help-text">Upload a screenshot of your GCash payment confirmation</small>
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
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1975.9717399302717!2d123.77012398018928!3d7.900974031470483!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32543494c8e5a309%3A0xea1c4ebf451f6fd!2sCARKIEZ%20AUTO%20SUPPLY!5e0!3m2!1sen!2sph!4v1761585936685!5m2!1sen!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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

<script>
// Toggle GCash section visibility
document.addEventListener('DOMContentLoaded', function() {
    const payOnPickup = document.getElementById('pay-on-pickup');
    const onlinePayment = document.getElementById('online-payment');
    const gcashSection = document.getElementById('gcash-section');
    const referenceInput = document.getElementById('reference_number');
    const proofInput = document.getElementById('proof_image');

    function toggleGcashSection() {
        if (onlinePayment.checked) {
            gcashSection.style.display = 'block';
            referenceInput.required = true;
            proofInput.required = true;
        } else {
            gcashSection.style.display = 'none';
            referenceInput.required = false;
            proofInput.required = false;
        }
    }

    payOnPickup.addEventListener('change', toggleGcashSection);
    onlinePayment.addEventListener('change', toggleGcashSection);

    // Form validation
    document.getElementById('checkout-form').addEventListener('submit', function(e) {
        if (onlinePayment.checked) {
            if (!referenceInput.value.trim()) {
                e.preventDefault();
                alert('Please enter the GCash reference number');
                referenceInput.focus();
                return false;
            }
            if (!proofInput.files.length) {
                e.preventDefault();
                alert('Please upload a screenshot of your payment');
                proofInput.focus();
                return false;
            }
        }
    });
});
</script>

<?php include 'Includes/userFooter.php'; ?>