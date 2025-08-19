<?php
require_once('inc/header.php');

// Handle form submission
$message = '';
$messageType = '';

if ($_POST) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $messageText = trim($_POST['message'] ?? '');

    // Basic validation
    if (empty($name) || empty($email) || empty($messageText)) {
        $message = 'يرجى ملء جميع الحقول المطلوبة';
        $messageType = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'يرجى إدخال بريد إلكتروني صحيح';
        $messageType = 'error';
    } else {
        // Prepare data
        $contactData = [
            'id' => uniqid(),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'message' => $messageText,
            'date' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];

        // Read existing contacts
        $contactsFile = 'contact.json';
        $contacts = [];

        if (file_exists($contactsFile)) {
            $existingData = file_get_contents($contactsFile);
            $contacts = json_decode($existingData, true) ?? [];
        }

        // Add new contact
        $contacts[] = $contactData;

        // Save to file
        if (file_put_contents($contactsFile, json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
            $message = 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً';
            $messageType = 'success';
            // Clear form data
            $_POST = [];
        } else {
            $message = 'حدث خطأ أثناء إرسال الرسالة. يرجى المحاولة مرة أخرى';
            $messageType = 'error';
        }
    }
}
?>

<!-- Header-->
<header class="bg-dark py-5">
    <div class="container px-4 px-lg-5 my-5">
        <div class="text-center text-white">
            <h1 class="display-4 fw-bolder">اتصل بنا</h1>
            <p class="lead fw-normal text-white-50 mb-0">نحن هنا للمساعدة والإجابة على استفساراتك</p>
        </div>
    </div>
</header>

<!-- Contact Section-->
<section class="py-5">
    <div class="container px-4 px-lg-5">
        <div class="row gx-4 gx-lg-5 justify-content-center">
            <div class="col-lg-8">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">نموذج الاتصال</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="contact.php">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">الاسم *</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">البريد الإلكتروني *</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">رقم الهاتف</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">الرسالة *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">إرسال الرسالة</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Contact Info -->
                <div class="row mt-5">
                    <div class="col-md-4 text-center mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="bi bi-geo-alt-fill text-primary fs-1 mb-3"></i>
                                <h5>العنوان</h5>
                                <p class="text-muted">القاهرة، مصر</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="bi bi-telephone-fill text-primary fs-1 mb-3"></i>
                                <h5>الهاتف</h5>
                                <p class="text-muted">+20 123 456 789</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <i class="bi bi-envelope-fill text-primary fs-1 mb-3"></i>
                                <h5>البريد الإلكتروني</h5>
                                <p class="text-muted">info@eraasoft.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('inc/footer.php'); ?>