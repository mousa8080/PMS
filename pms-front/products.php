<?php
require_once('inc/header.php');

// Handle delete action
if (isset($_GET['delete'])) {
    $productId = $_GET['delete'];
    $productsFile = 'products.json';

    if (file_exists($productsFile)) {
        $products = json_decode(file_get_contents($productsFile), true) ?? [];
        $products = array_filter($products, function ($product) use ($productId) {
            return $product['id'] !== $productId;
        });

        if (file_put_contents($productsFile, json_encode(array_values($products), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $message = 'تم حذف المنتج بنجاح';
            $messageType = 'success';
        } else {
            $message = 'حدث خطأ أثناء حذف المنتج';
            $messageType = 'error';
        }
    }
}

// Load products
$productsFile = 'products.json';
$products = [];

if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true) ?? [];
}
?>

<!-- Header-->
<header class="bg-dark py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h1 class="display-4 fw-bolder">إدارة المنتجات</h1>
            <p class="lead fw-normal text-white-50 mb-0">إضافة وتعديل وحذف المنتجات</p>
        </div>
    </div>
</header>

<!-- Products Management Section-->
<section class="py-5">
    <div class="container px-4 px-lg-5">
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>قائمة المنتجات</h2>
            <a href="add-product.php" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>إضافة منتج جديد
            </a>
        </div>

        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
                <h4 class="text-muted">لا توجد منتجات حالياً</h4>
                <p class="text-muted">ابدأ بإضافة منتج جديد</p>
                <a href="add-product.php" class="btn btn-primary">إضافة منتج جديد</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>الصورة</th>
                            <th>اسم المنتج</th>
                            <th>السعر</th>
                            <th>الفئة</th>
                            <th>المخزون</th>
                            <th>تاريخ الإضافة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($product['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                            class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                            style="width: 60px; height: 60px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                    <?php if (!empty($product['description'])): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($product['sale_price']) && $product['sale_price'] < $product['price']): ?>
                                        <span class="text-decoration-line-through text-muted">$<?php echo number_format($product['price'], 2); ?></span><br>
                                        <strong class="text-success">$<?php echo number_format($product['sale_price'], 2); ?></strong>
                                    <?php else: ?>
                                        <strong>$<?php echo number_format($product['price'], 2); ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['category'] ?? 'غير محدد'); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo ($product['stock'] ?? 0) > 0 ? 'success' : 'danger'; ?>">
                                        <?php echo $product['stock'] ?? 0; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($product['created_at'] ?? 'now')); ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="edit-product.php?id=<?php echo $product['id']; ?>"
                                            class="btn btn-sm btn-outline-primary" title="تعديل">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="products.php?delete=<?php echo $product['id']; ?>"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا المنتج؟')"
                                            title="حذف">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <div class="row">
                    <div class="col-md-6">
                        <p class="text-muted">إجمالي المنتجات: <strong><?php echo count($products); ?></strong></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="add-product.php" class="btn btn-primary">إضافة منتج جديد</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once('inc/footer.php'); ?>