<?php
include 'Includes/userHeader.php';
include 'Includes/connection.php';
check_login_redirect();

$user_id = $_SESSION['user_id'];

// Get the current cart
$sql = "SELECT cart_id FROM cart WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cart = $result->fetch_assoc();
    $cart_id = $cart['cart_id'];

    // Get all items in this cart
    $sql_items = "
        SELECT ci.cart_item_id, ci.quantity, 
               p.product_id, p.product_name, p.price, p.image_path
        FROM cart_item ci
        JOIN products p ON ci.product_id = p.product_id
        WHERE ci.cart_id = ?";
    
    $stmt_items = $conn->prepare($sql_items);
    $stmt_items->bind_param("i", $cart_id);
    $stmt_items->execute();
    $items_result = $stmt_items->get_result();

    // Fetch all items into an array
    $cart_items = [];
    $total = 0;
    while ($row = $items_result->fetch_assoc()) {
        $cart_items[] = $row;
        $total += $row['price'] * $row['quantity'];
    }
    $stmt_items->close();
?>
<main>
    <h2>Your Cart</h2>
    <div class="cart-container">
        <div class="cart-items">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($cart_items as $row) {
                        $item_total = $row['price'] * $row['quantity'];
                    ?>
                    <tr>
                        <td>
                            <div class="cart-item">
                                <div class="cart-item-image">
                                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                                        alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                                </div>
                                <div class="cart-item-details">
                                    <p><?php echo htmlspecialchars($row['product_name']); ?></p>
                                    <p>₱<?php echo number_format($row['price'], 2); ?></p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="cart-item-quantity">
                                <div class="quantity">
                                    <button class="minus" data-id="<?php echo $row['cart_item_id']; ?>">−</button>
                                    <input type="number" value="<?php echo $row['quantity']; ?>" min="1">
                                    <button class="plus" data-id="<?php echo $row['cart_item_id']; ?>">+</button>
                                </div>
                                <button class="remove-btn" data-id="<?php echo $row['cart_item_id']; ?>">Remove</button>
                            </div>
                        </td>
                        <td>₱<?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="cart-items-mobile">
            <?php
            foreach ($cart_items as $row) {
                $item_total = $row['price'] * $row['quantity'];
            ?>
            <div class="cart-item-mobile">
                <div class="image-placeholder">
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" 
                        alt="<?php echo htmlspecialchars($row['product_name']); ?>">
                </div>
                <div class="cart-item-details">
                    <p><?php echo htmlspecialchars($row['product_name']); ?></p>
                    <p>₱<?php echo number_format($row['price'], 2); ?></p>
                    <div class="quantity">
                        <button class="minus" data-id="<?php echo $row['cart_item_id']; ?>">−</button>
                        <input type="number" value="<?php echo $row['quantity']; ?>" min="1">
                        <button class="plus" data-id="<?php echo $row['cart_item_id']; ?>">+</button>
                    </div>
                    <p><strong>Total:</strong> ₱<?php echo number_format($item_total, 2); ?></p>
                    <div class="cart-item-actions">
                        <button class="remove-btn" data-id="<?php echo $row['cart_item_id']; ?>">Remove</button>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        <div class="checkout-section">
            <form id="cart-form" method="GET" action="userCheckout.php">
                <div class="cart-total">
                    <h3>Total:</h3>
                    <h3>₱<?php echo number_format($total, 2); ?></h3>
                </div>
                <button class="checkout-button" <?php if ($total == 0) echo 'disabled'; ?>>Checkout</button>
            </form>
        </div>
    </div>
</main>

<?php 
} else {
    // No cart found
    ?>
    <main>
        <h2>Your Cart</h2>
        <p>Your cart is empty.</p>
    </main>
<?php
}
include 'Includes/userFooter.php';
include 'Includes/quantitybuttons.php';
?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  // Handle plus buttons
  document.querySelectorAll('.plus').forEach(btn => {
    btn.addEventListener('click', function() {
      const cartItemId = this.getAttribute('data-id');
      updateCart(cartItemId, 'increase');
    });
  });

  // Handle minus buttons
  document.querySelectorAll('.minus').forEach(btn => {
    btn.addEventListener('click', function() {
      const cartItemId = this.getAttribute('data-id');
      updateCart(cartItemId, 'decrease');
    });
  });

  // Handle remove buttons
  document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      if (confirm('Remove this item from cart?')) {
        const cartItemId = this.getAttribute('data-id');
        updateCart(cartItemId, 'remove');
      }
    });
  });

  // Handle direct input change
  document.querySelectorAll('.quantity input[type="number"]').forEach(input => {
    // Store the original value
    let originalValue = input.value;
    
    input.addEventListener('change', function() {
      const newValue = parseInt(this.value);
      const min = parseInt(this.min) || 1;
      const max = parseInt(this.max) || Infinity;
      
      // Validate the input
      if (isNaN(newValue) || newValue < min) {
        this.value = min;
      } else if (newValue > max) {
        this.value = max;
        alert('Maximum available quantity is ' + max);
      }
      
      // Only update if value actually changed
      if (this.value !== originalValue) {
        const cartItemId = this.closest('.quantity').querySelector('button').getAttribute('data-id');
        updateCartDirect(cartItemId, parseInt(this.value));
        originalValue = this.value;
      }
    });

    // Prevent non-numeric input
    input.addEventListener('keypress', function(e) {
      if (e.which < 48 || e.which > 57) {
        e.preventDefault();
      }
    });
  });

  function updateCart(cartItemId, action) {
    const formData = new FormData();
    formData.append('cart_item_id', cartItemId);
    formData.append('action', action);

    fetch('Includes/userUpdateCart.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || 'Error updating cart');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error updating cart');
    });
  }

  function updateCartDirect(cartItemId, quantity) {
    const formData = new FormData();
    formData.append('cart_item_id', cartItemId);
    formData.append('action', 'set_quantity');
    formData.append('quantity', quantity);

    fetch('Includes/userUpdateCart.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || 'Error updating cart');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error updating cart');
    });
  }
});
</script>