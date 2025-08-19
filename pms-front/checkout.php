<?php
require_once('inc/header.php');

// Load cart data
$cartFile = 'cart.json';
$cart = [];

if (file_exists($cartFile)) {
    $cart = json_decode(file_get_contents($cartFile), true) ?? [];
}

// If cart is empty, redirect to cart page
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.1; // 10% tax
$shipping = $subtotal > 100 ? 0 : 10; // Free shipping over $100
$total = $subtotal + $tax + $shipping;

$message = '';
$messageType = '';
$orderSuccess = false;

// Handle form submission
if ($_POST) {
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $shippingAddress = trim($_POST['shipping_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    // Validation
    if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($shippingAddress) || empty($city) || empty($paymentMethod)) {
        $message = 'يرجى ملء جميع الحقول المطلوبة';
        $messageType = 'error';
    } elseif (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $message = 'يرجى إدخال بريد إلكتروني صحيح';
        $messageType = 'error';
    } else {
        // Create order data
        $orderId = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        $orderData = [
            'order_id' => $orderId,
            'customer_info' => [
                'name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
                'shipping_address' => $shippingAddress,
                'city' => $city
            ],
            'items' => $cart,
            'order_summary' => [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'total' => $total
            ],
            'payment_method' => $paymentMethod,
            'notes' => $notes,
            'status' => 'pending',
            'order_date' => date('Y-m-d H:i:s'),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        // Load existing orders
        $checkoutFile = 'checkout.json';
        $orders = [];

        if (file_exists($checkoutFile)) {
            $orders = json_decode(file_get_contents($checkoutFile), true) ?? [];
        }

        // Add new order
        $orders[] = $orderData;

        // Save to file
        if (file_put_contents($checkoutFile, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            // Clear cart after successful order
            file_put_contents($cartFile, json_encode([], JSON_PRETTY_PRINT));

            $message = 'تم تأكيد طلبك بنجاح! رقم الطلب: ' . $orderId;
            $messageType = 'success';
            $orderSuccess = true;

            // Clear form data
            $_POST = [];
        } else {
            $message = 'حدث خطأ أثناء معالجة الطلب. يرجى المحاولة مرة أخرى';
            $messageType = 'error';
        }
    }
}
?>

<!-- Header-->
<header class="bg-dark py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h1 class="display-4 fw-bolder">إتمام الشراء</h1>
            <p class="lead fw-normal text-white-50 mb-0">أكمل بياناتك لتأكيد الطلب</p>
        </div>
    </div>
</header>

<!-- Checkout Section-->
<section class="py-5">
    <div class="container px-4 px-lg-5">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($orderSuccess): ?>
            <div class="text-center py-5">
                <i class="bi bi-check-circle-fill text-success fs-1 mb-3"></i>
                <h3 class="text-success">تم تأكيد طلبك بنجاح!</h3>
                <p class="text-muted">سنتواصل معك قريباً لتأكيد التفاصيل</p>
                <div class="mt-4">
                    <a href="index.php" class="btn btn-primary me-2">العودة للتسوق</a>
                    <a href="cart.php" class="btn btn-outline-secondary">عرض السلة</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">بيانات العميل</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="checkout.php">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_name" class="form-label">الاسم الكامل *</label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name"
                                            value="<?php echo htmlspecialchars($_POST['customer_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_email" class="form-label">البريد الإلكتروني *</label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email"
                                            value="<?php echo htmlspecialchars($_POST['customer_email'] ?? ''); ?>" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_phone" class="form-label">رقم الهاتف *</label>
                                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone"
                                            value="<?php echo htmlspecialchars($_POST['customer_phone'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">المدينة *</label>
                                        <select class="form-select" id="city" name="city" required>
                                            <option value="">اختر المدينة</option>
                                            <option value="القاهرة" <?php echo ($_POST['city'] ?? '') === 'القاهرة' ? 'selected' : ''; ?>>القاهرة</option>
                                            <option value="الجيزة" <?php echo ($_POST['city'] ?? '') === 'الجيزة' ? 'selected' : ''; ?>>الجيزة</option>
                                            <option value="الإسكندرية" <?php echo ($_POST['city'] ?? '') === 'الإسكندرية' ? 'selected' : ''; ?>>الإسكندرية</option>
                                            <option value="أخرى" <?php echo ($_POST['city'] ?? '') === 'أخرى' ? 'selected' : ''; ?>>أخرى</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="shipping_address" class="form-label">عنوان الشحن *</label>
                                    <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo htmlspecialchars($_POST['shipping_address'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">طريقة الدفع *</label>
                                    <select class="form-select" id="payment_method" name="payment_method" required>
                                        <option value="">اختر طريقة الدفع</option>
                                        <option value="cash_on_delivery" <?php echo ($_POST['payment_method'] ?? '') === 'cash_on_delivery' ? 'selected' : ''; ?>>الدفع عند الاستلام</option>
                                        <option value="bank_transfer" <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'selected' : ''; ?>>تحويل بنكي</option>
                                        <option value="credit_card" <?php echo ($_POST['payment_method'] ?? '') === 'credit_card' ? 'selected' : ''; ?>>بطاقة ائتمان</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">ملاحظات إضافية</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="2"><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success btn-lg">
                                        <i class="bi bi-check-circle me-2"></i>تأكيد الطلب
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">ملخص الطلب</h5>
                        </div>
                        <div class="card-body">
                            <!-- Order Items -->
                            <div class="mb-3">
                                <h6>المنتجات:</h6>
                                <?php foreach ($cart as $item): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div>
                                            <small><?php echo htmlspecialchars($item['name']); ?></small>
                                            <br><small class="text-muted">الكمية: <?php echo $item['quantity']; ?></small>
                                        </div>
                                        <small>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <hr>

                            <!-- Order Summary -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>المجموع الفرعي:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>الضريبة (10%):</span>
                                <span>$<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>الشحن:</span>
                                <span><?php echo $shipping > 0 ? '$' . number_format($shipping, 2) : 'مجاني'; ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>المجموع الكلي:</strong>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="cart.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>العودة للسلة
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Update cart count after successful order
    <?php if ($orderSuccess): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('cart-count').textContent = '0';
        });
    <?php endif; ?>
</script>

<?php require_once('inc/footer.php'); ?>