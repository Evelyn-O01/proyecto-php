<?php

namespace App\Frontend\Services;

class CartSession
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public static function add(array $item): void
    {
        self::start();
        $variantId = $item['variant_id'];
        $qty = max(1, (int)($item['quantity'] ?? 1));

        if (isset($_SESSION['cart'][$variantId])) {
            $_SESSION['cart'][$variantId]['quantity'] += $qty;
            $_SESSION['cart'][$variantId]['subtotal'] = $_SESSION['cart'][$variantId]['unit_price'] * $_SESSION['cart'][$variantId]['quantity'];
        } else {
            $_SESSION['cart'][$variantId] = [
                'product_id' => $item['product_id'],
                'variant_id' => $variantId,
                'name' => $item['name'],
                'slug' => $item['slug'],
                'size' => $item['size'],
                'color' => $item['color'],
                'unit_price' => (float)$item['unit_price'],
                'quantity' => $qty,
                'subtotal' => (float)$item['unit_price'] * $qty,
                'image' => $item['image'],
                'max_stock' => (int)($item['max_stock'] ?? 99)
            ];
        }
    }

    public static function updateQuantity(string $variantId, int $quantity): void
    {
        self::start();
        if ($quantity <= 0) {
            self::remove($variantId);
            return;
        }

        if (isset($_SESSION['cart'][$variantId])) {
            $_SESSION['cart'][$variantId]['quantity'] = $quantity;
            $_SESSION['cart'][$variantId]['subtotal'] = $_SESSION['cart'][$variantId]['unit_price'] * $quantity;
        }
    }

    public static function remove(string $variantId): void
    {
        self::start();
        unset($_SESSION['cart'][$variantId]);
    }

    public static function clear(): void
    {
        self::start();
        $_SESSION['cart'] = [];
    }

    public static function getItems(): array
    {
        self::start();
        return $_SESSION['cart'] ?? [];
    }

    public static function getItemCount(): int
    {
        $sum = 0;
        foreach (self::getItems() as $item) {
            $sum += $item['quantity'];
        }
        return $sum;
    }

    public static function getSubtotal(): float
    {
        $sum = 0.0;
        foreach (self::getItems() as $item) {
            $sum += $item['subtotal'];
        }
        return round($sum, 2);
    }

    public static function getTax(): float
    {
        return round(self::getSubtotal() * 0.16, 2);
    }

    public static function getShipping(): float
    {
        return self::getSubtotal() >= 999.00 || self::getSubtotal() === 0.0 ? 0.00 : 99.00;
    }

    public static function getTotal(): float
    {
        return round(self::getSubtotal() + self::getShipping(), 2);
    }
}
