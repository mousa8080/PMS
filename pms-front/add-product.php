<?php
require_once('inc/header.php');

$message = '';
$messageType = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $salePrice = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    $category = trim($_POST['category'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $image = trim($_POST['image'] ?? '');

    // Validation
    if (empty($name) || $price <= 0) {
        $message = 'يرجى ملء اسم المنتج والسعر بشكل صحيح';
        $messageType = 'error';
    } elseif ($salePrice !== null && $salePrice >= $price) {
        $message = 'سعر التخفيض يجب أن يكون أقل من السعر الأصلي';
        $messageType = 'error';
    } else {
        // Prepare product data
        $productData = [
            'id' => uniqid(),
            'name' => $name,
            'description' => $description,
            'price' => $price,
            'sale_price' => $salePrice,
            'category' => $category,
            'stock' => $stock,
            'image' => $image,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Load existing products
        $productsFile = 'products.json';
        $products = [];

        if (file_exists($productsFile)) {
            $products = json_decode(file_get_contents($productsFile), true) ?? [];
        }

        // Add new product
        $products[] = $productData;

        // Save to file
        if (file_put_contents($productsFile, json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $message = 'تم إضافة المنتج بنجاح';
            $messageType = 'success';
            // Clear form
            $_POST = [];
        } else {
            $message = 'حدث خطأ أثناء إضافة المنتج';
            $messageType = 'error';
        }
    }
}
?>

<!-- Header-->
<header class="bg-dark py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h1 class="display-4 fw-bolder">إضافة منتج جديد</h1>
            <p class="lead fw-normal text-white-50 mb-0">أضف منتجاً جديداً إلى متجرك</p>
        </div>
    </div>
</header>

<!-- Add Product Section-->
<section class="py-5">
    <div class="container px-4 px-lg-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">بيانات المنتج</h3>
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-2"></i>العودة للقائمة
                        </a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="add-product.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">اسم المنتج *</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">الفئة</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">اختر الفئة</option>
                                        <option value="Electronics" <?php echo ($_POST['category'] ?? '') === 'Electronics' ? 'selected' : ''; ?>>إلكترونيات</option>
                                        <option value="Clothing" <?php echo ($_POST['category'] ?? '') === 'Clothing' ? 'selected' : ''; ?>>ملابس</option>
                                        <option value="Books" <?php echo ($_POST['category'] ?? '') === 'Books' ? 'selected' : ''; ?>>كتب</option>
                                        <option value="Home" <?php echo ($_POST['category'] ?? '') === 'Home' ? 'selected' : ''; ?>>منزل وحديقة</option>
                                        <option value="Sports" <?php echo ($_POST['category'] ?? '') === 'Sports' ? 'selected' : ''; ?>>رياضة</option>
                                        <option value="Other" <?php echo ($_POST['category'] ?? '') === 'Other' ? 'selected' : ''; ?>>أخرى</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">وصف المنتج</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="price" class="form-label">السعر ($) *</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="price" name="price"
                                        value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="sale_price" class="form-label">سعر التخفيض ($)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="sale_price" name="sale_price"
                                        value="<?php echo htmlspecialchars($_POST['sale_price'] ?? ''); ?>">
                                    <small class="form-text text-muted">اتركه فارغاً إذا لم يكن هناك تخفيض</small>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="stock" class="form-label">الكمية المتوفرة</label>
                                    <input type="number" min="0" class="form-control" id="stock" name="stock"
                                        value="<?php echo htmlspecialchars($_POST['stock'] ?? '0'); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">رابط الصورة</label>
                                <input type="url" class="form-control" id="image" name="image"
                                    value="<?php echo htmlspecialchars($_POST['image'] ?? ''); ?>"
                                    placeholder="https://example.com/image.jpg">
                                <small class="form-text text-muted">أدخل رابط صورة المنتج أو اتركه فارغاً</small>
                            </div>

                            <!-- Image Preview -->
                            <div class="mb-3" id="imagePreview" style="display: none;">
                                <label class="form-label">معاينة الصورة</label>
                                <div>
                                    <img id="previewImg" src="" alt="معاينة الصورة" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="products.php" class="btn btn-secondary me-md-2">إلغاء</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>إضافة المنتج
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Image preview functionality
    document.getElementById('image').addEventListener('input', function() {
        const imageUrl = this.value;
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if (imageUrl) {
            previewImg.src = imageUrl;
            preview.style.display = 'block';

            previewImg.onerror = function() {
                preview.style.display = 'none';
            };
        } else {
            preview.style.display = 'none';
        }
    });

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const price = parseFloat(document.getElementById('price').value);
        const salePrice = parseFloat(document.getElementById('sale_price').value);

        if (salePrice && salePrice >= price) {
            e.preventDefault();
            alert('سعر التخفيض يجب أن يكون أقل من السعر الأصلي');
        }
    });
</script>

<?php require_once('inc/footer.php'); ?>