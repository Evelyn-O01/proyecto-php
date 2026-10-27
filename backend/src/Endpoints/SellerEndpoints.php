<?php

namespace App\Backend\Endpoints;

use App\Backend\Controllers\OrderController;
use App\Backend\Controllers\ProductController;

class SellerEndpoints
{
    private ProductController $productCtrl;
    private OrderController $orderCtrl;

    public function __construct()
    {
        $this->productCtrl = new ProductController();
        $this->orderCtrl = new OrderController();
    }

    public function products(array $params): void
    {
        $sellerId = $params['id'] ?? '';
        $products = $this->productCtrl->getBySeller($sellerId);
        ApiRouter::json(['success' => true, 'data' => $products]);
    }

    public function create(): void
    {
        $body = ApiRouter::getJsonBody();
        $sellerId = $body['seller_id'] ?? '';
        $categoryId = (int)($body['category_id'] ?? 1);
        $name = $body['name'] ?? '';
        $description = $body['description'] ?? '';
        $basePrice = (float)($body['base_price'] ?? 0);
        $variants = $body['variants'] ?? [];
        $images = $body['images'] ?? [];

        try {
            $product = $this->productCtrl->createProduct(
                $sellerId,
                $categoryId,
                $name,
                $description,
                $basePrice,
                $variants,
                $images
            );
            ApiRouter::json(['success' => true, 'data' => ['id' => $product->id, 'slug' => $product->slug]], 201);
        } catch (\Throwable $e) {
            ApiRouter::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function update(array $params): void
    {
        $id = $params['id'] ?? '';
        $body = ApiRouter::getJsonBody();
        $sellerId = $body['seller_id'] ?? '';
        $categoryId = (int)($body['category_id'] ?? 1);
        $name = $body['name'] ?? '';
        $description = $body['description'] ?? '';
        $basePrice = (float)($body['base_price'] ?? 0);
        $status = $body['status'] ?? 'ACTIVE';

        try {
            $product = $this->productCtrl->updateProduct(
                $id,
                $sellerId,
                $categoryId,
                $name,
                $description,
                $basePrice,
                $status
            );
            ApiRouter::json(['success' => true, 'data' => ['id' => $product->id]]);
        } catch (\Throwable $e) {
            ApiRouter::json(['success' => false, 'error' => $e->getMessage()], 400);
        }
    }

    public function delete(array $params): void
    {
        $id = $params['id'] ?? '';
        $body = ApiRouter::getJsonBody();
        $sellerId = $body['seller_id'] ?? '';

        $success = $this->productCtrl->deleteProduct($id, $sellerId);
        ApiRouter::json(['success' => $success]);
    }

    public function sales(array $params): void
    {
        $sellerId = $params['id'] ?? '';
        $orders = $this->orderCtrl->getBySeller($sellerId);
        ApiRouter::json(['success' => true, 'data' => $orders]);
    }
}
