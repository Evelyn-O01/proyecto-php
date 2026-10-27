<?php

namespace App\Frontend\Controllers;

use App\Frontend\Services\CartSession;
use App\Frontend\Services\Session;

class CartController
{
    public function index(): void
    {
        $data = [
            'title' => 'Bolsa de Compras | AURA Atelier',
            'items' => CartSession::getItems(),
            'itemCount' => CartSession::getItemCount(),
            'subtotal' => CartSession::getSubtotal(),
            'tax' => CartSession::getTax(),
            'shipping' => CartSession::getShipping(),
            'total' => CartSession::getTotal()
        ];

        echo StoreController::render('store/cart', $data);
    }

    public function add(): void
    {
        $item = [
            'product_id' => $_POST['product_id'] ?? '',
            'variant_id' => $_POST['variant_id'] ?? '',
            'name' => $_POST['name'] ?? 'Prenda',
            'slug' => $_POST['slug'] ?? '',
            'size' => $_POST['size'] ?? '',
            'color' => $_POST['color'] ?? '',
            'unit_price' => (float)($_POST['unit_price'] ?? 0),
            'quantity' => (int)($_POST['quantity'] ?? 1),
            'image' => $_POST['image'] ?? '',
            'max_stock' => (int)($_POST['max_stock'] ?? 99)
        ];

        CartSession::add($item);
        Session::flash('success', 'Prenda añadida a la bolsa de compras.');

        $redirect = $_POST['redirect_to'] ?? '/cart';
        header("Location: {$redirect}");
        exit;
    }

    public function update(): void
    {
        $variantId = $_POST['variant_id'] ?? '';
        $qty = (int)($_POST['quantity'] ?? 1);

        CartSession::updateQuantity($variantId, $qty);
        Session::flash('success', 'Bolsa actualizada.');
        header('Location: /cart');
        exit;
    }

    public function remove(array $params): void
    {
        $id = $params['id'] ?? '';
        CartSession::remove($id);
        Session::flash('success', 'Prenda retirada de la bolsa.');
        header('Location: /cart');
        exit;
    }
}
