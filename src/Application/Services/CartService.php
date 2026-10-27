<?php

namespace App\Application\Services;

use App\Domain\Ports\ProductRepositoryPort;
use RuntimeException;

class CartService
{
    public function __construct(private ProductRepositoryPort $productRepo)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function addItem(string $productId, string $variantId, int $quantity = 1): void
    {
        $product = $this->productRepo->findById($productId);
        if (!$product) {
            throw new RuntimeException("El producto solicitado no existe.");
        }

        $variant = $this->productRepo->findVariantById($variantId);
        if (!$variant || $variant->productId !== $productId) {
            throw new RuntimeException("La variante (talla/color) seleccionada no es válida.");
        }

        if ($variant->stock < $quantity) {
            throw new RuntimeException("Existencias insuficientes. Stock disponible: {$variant->stock}");
        }

        $cartKey = $variantId;
        $currentQty = $_SESSION['cart'][$cartKey]['quantity'] ?? 0;
        $newQty = $currentQty + $quantity;

        if ($variant->stock < $newQty) {
            throw new RuntimeException("No puedes añadir más unidades de las disponibles en stock ({$variant->stock}).");
        }

        $unitPrice = $product->basePrice + $variant->additionalPrice;

        $_SESSION['cart'][$cartKey] = [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'size' => $variant->size,
            'color' => $variant->color,
            'color_code' => $variant->colorCode,
            'unit_price' => $unitPrice,
            'quantity' => $newQty,
            'subtotal' => $unitPrice * $newQty,
            'image' => $product->getPrimaryImage(),
            'max_stock' => $variant->stock
        ];
    }

    public function updateQuantity(string $variantId, int $quantity): void
    {
        if ($quantity <= 0) {
            $this->removeItem($variantId);
            return;
        }

        if (isset($_SESSION['cart'][$variantId])) {
            $variant = $this->productRepo->findVariantById($variantId);
            if ($variant && $variant->stock < $quantity) {
                throw new RuntimeException("Existencias insuficientes ({$variant->stock} disponibles).");
            }
            $_SESSION['cart'][$variantId]['quantity'] = $quantity;
            $_SESSION['cart'][$variantId]['subtotal'] = $_SESSION['cart'][$variantId]['unit_price'] * $quantity;
        }
    }

    public function removeItem(string $variantId): void
    {
        unset($_SESSION['cart'][$variantId]);
    }

    public function clear(): void
    {
        $_SESSION['cart'] = [];
    }

    public function getItems(): array
    {
        return $_SESSION['cart'] ?? [];
    }

    public function getItemCount(): int
    {
        $total = 0;
        foreach ($this->getItems() as $item) {
            $total += $item['quantity'];
        }
        return $total;
    }

    public function getSubtotal(): float
    {
        $subtotal = 0.0;
        foreach ($this->getItems() as $item) {
            $subtotal += $item['subtotal'];
        }
        return round($subtotal, 2);
    }

    public function getTax(): float
    {
        // 16% IVA incluido o desglosado
        return round($this->getSubtotal() * 0.16, 2);
    }

    public function getShipping(): float
    {
        // Envío gratuito en compras superiores a $999 MXN
        return $this->getSubtotal() >= 999.00 || $this->getSubtotal() === 0.0 ? 0.00 : 99.00;
    }

    public function getTotal(): float
    {
        return round($this->getSubtotal() + $this->getShipping(), 2);
    }
}
