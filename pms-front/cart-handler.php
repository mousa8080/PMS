<?php
session_start();
header('Content-Type: application/json');

$cartFile = 'cart.json';

// Get current cart data
function getCart()
{
    global $cartFile;
    if (file_exists($cartFile)) {
        $cartData = file_get_contents($cartFile);
        return json_decode($cartData, true) ?? [];
    }
    return [];
}

// Save cart data
function saveCart($cart)
{
    global $cartFile;
    return file_put_contents($cartFile, json_encode($cart, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Get cart count
function getCartCount()
{
    $cart = getCart();
    $count = 0;
    foreach ($cart as $item) {
        $count += $item['quantity'];
    }
    return $count;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = $_POST['product_id'] ?? '';
        $productName = $_POST['product_name'] ?? '';
        $productPrice = floatval($_POST['product_price'] ?? 0);
        $productImage = $_POST['product_image'] ?? '';

        if (empty($productId) || empty($productName) || $productPrice <= 0) {
            echo json_encode(['success' => false, 'message' => 'بيانات المنتج غير صحيحة']);
            exit;
        }

        $cart = getCart();

        // Check if product already exists in cart
        $found = false;
        for ($i = 0; $i < count($cart); $i++) {
            if ($cart[$i]['id'] === $productId) {
                $cart[$i]['quantity']++;
                $found = true;
                break;
            }
        }

        // If not found, add new item
        if (!$found) {
            $cart[] = [
                'id' => $productId,
                'name' => $productName,
                'price' => $productPrice,
                'image' => $productImage,
                'quantity' => 1,
                'added_at' => date('Y-m-d H:i:s')
            ];
        }

        if (saveCart($cart)) {
            echo json_encode([
                'success' => true,
                'message' => 'تم إضافة المنتج للسلة',
                'cart_count' => getCartCount()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء إضافة المنتج']);
        }
        break;

    case 'remove':
        $productId = $_POST['product_id'] ?? '';

        if (empty($productId)) {
            echo json_encode(['success' => false, 'message' => 'معرف المنتج مطلوب']);
            exit;
        }

        $cart = getCart();
        $cart = array_filter($cart, function ($item) use ($productId) {
            return $item['id'] !== $productId;
        });

        if (saveCart(array_values($cart))) {
            echo json_encode([
                'success' => true,
                'message' => 'تم حذف المنتج من السلة',
                'cart_count' => getCartCount()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء حذف المنتج']);
        }
        break;

    case 'update_quantity':
        $productId = $_POST['product_id'] ?? '';
        $quantity = intval($_POST['quantity'] ?? 0);

        if (empty($productId) || $quantity < 0) {
            echo json_encode(['success' => false, 'message' => 'بيانات غير صحيحة']);
            exit;
        }

        $cart = getCart();

        if ($quantity === 0) {
            // Remove item if quantity is 0
            $cart = array_filter($cart, function ($item) use ($productId) {
                return $item['id'] !== $productId;
            });
        } else {
            // Update quantity
            for ($i = 0; $i < count($cart); $i++) {
                if ($cart[$i]['id'] === $productId) {
                    $cart[$i]['quantity'] = $quantity;
                    break;
                }
            }
        }

        if (saveCart(array_values($cart))) {
            echo json_encode([
                'success' => true,
                'message' => 'تم تحديث الكمية',
                'cart_count' => getCartCount()
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء تحديث الكمية']);
        }
        break;

    case 'get_count':
        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount()
        ]);
        break;

    case 'get_cart':
        echo json_encode([
            'success' => true,
            'cart' => getCart(),
            'cart_count' => getCartCount()
        ]);
        break;

    case 'clear':
        if (saveCart([])) {
            echo json_encode([
                'success' => true,
                'message' => 'تم مسح السلة',
                'cart_count' => 0
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'حدث خطأ أثناء مسح السلة']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'عملية غير صحيحة']);
        break;
}
