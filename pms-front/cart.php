<?php
require_once('inc/header.php');

// Load cart data
$cartFile = 'cart.json';
$cart = [];

if (file_exists($cartFile)) {
    $cart = json_decode(file_get_contents($cartFile), true) ?? [];
}

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * 0.1; // 10% tax
$total = $subtotal + $tax;
?>

<!-- Header-->
<header class="bg-dark py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h1 class="display-4 fw-bolder">سلة التسوق</h1>
            <p class="lead fw-normal text-white-50 mb-0">مراجعة المنتجات المختارة</p>
        </div>
    </div>
</header>

<!-- Cart Section-->
<section class="py-5">
    <div class="container px-4 px-lg-5">
        <?php if (empty($cart)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart-x fs-1 text-muted mb-3"></i>
                <h4 class="text-muted">سلة التسوق فارغة</h4>
                <p class="text-muted">ابدأ بإضافة بعض المنتجات إلى سلتك</p>
                <a href="index.php" class="btn btn-primary">تصفح المنتجات</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title mb-0">المنتجات في السلة</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>المنتج</th>
                                            <th>السعر</th>
                                            <th>الكمية</th>
                                            <th>المجموع</th>
                                            <th>إجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart as $item): ?>
                                            <tr data-product-id="<?php echo $item['id']; ?>">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($item['image'])): ?>
                                                            <img src="<?php echo htmlspecialchars($item['image']); ?>"
                                                                alt="<?php echo htmlspecialchars($item['name']); ?>"
                                                                class="img-thumbnail me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-light d-flex align-items-center justify-content-center me-3"
                                                                style="width: 60px; height: 60px;">
                                                                <i class="bi bi-image text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['name']); ?></h6>
                                                            <small class="text-muted">أُضيف في: <?php echo date('Y-m-d H:i', strtotime($item['added_at'])); ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>$<?php echo number_format($item['price'], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <div class="input-group" style="width: 120px;">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                                            onclick="updateQuantity('<?php echo $item['id']; ?>', <?php echo $item['quantity'] - 1; ?>)">-</button>
                                                        <input type="number" class="form-control form-control-sm text-center"
                                                            value="<?php echo $item['quantity']; ?>" min="1"
                                                            onchange="updateQuantity('<?php echo $item['id']; ?>', this.value)">
                                                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                                            onclick="updateQuantity('<?php echo $item['id']; ?>', <?php echo $item['quantity'] + 1; ?>)">+</button>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="removeFromCart('<?php echo $item['id']; ?>')"
                                                        title="حذف من السلة">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">ملخص الطلب</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>المجموع الفرعي:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>الضريبة (10%):</span>
                                <span>$<?php echo number_format($tax, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>المجموع الكلي:</strong>
                                <strong>$<?php echo number_format($total, 2); ?></strong>
                            </div>

                            <div class="d-grid gap-2">
                                <a href="checkout.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-credit-card me-2"></i>إتمام الشراء
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>متابعة التسوق
                                </a>
                                <button class="btn btn-outline-danger" onclick="clearCart()">
                                    <i class="bi bi-trash me-2"></i>مسح السلة
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function updateQuantity(productId, quantity) {
        if (quantity < 1) {
            removeFromCart(productId);
            return;
        }

        const formData = new FormData();
        formData.append('action', 'update_quantity');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload to update totals
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error updating quantity:', error);
                showMessage('حدث خطأ أثناء تحديث الكمية', 'error');
            });
    }

    function removeFromCart(productId) {
        if (!confirm('هل أنت متأكد من حذف هذا المنتج من السلة؟')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('product_id', productId);

        fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error removing from cart:', error);
                showMessage('حدث خطأ أثناء حذف المنتج', 'error');
            });
    }

    function clearCart() {
        if (!confirm('هل أنت متأكد من مسح جميع المنتجات من السلة؟')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'clear');

        fetch('cart-handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error clearing cart:', error);
                showMessage('حدث خطأ أثناء مسح السلة', 'error');
            });
    }
</script>

<?php require_once('inc/footer.php'); ?>