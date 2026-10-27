<?php

namespace App\Presentation\Controllers;

use App\Domain\Entities\Category;
use App\Domain\ValueObjects\Role;
use App\Infrastructure\Database\PostgresConnection;
use App\Infrastructure\Repositories\PostgresCategoryRepository;
use App\Infrastructure\Repositories\PostgresOrderRepository;
use App\Infrastructure\Repositories\PostgresProductRepository;
use App\Infrastructure\Repositories\PostgresUserRepository;
use App\Presentation\Middlewares\RoleMiddleware;
use App\Presentation\Middlewares\SessionManager;
use App\Presentation\Router;

class AdminController
{
    private PostgresUserRepository $userRepo;
    private PostgresProductRepository $productRepo;
    private PostgresOrderRepository $orderRepo;
    private PostgresCategoryRepository $categoryRepo;

    public function __construct()
    {
        RoleMiddleware::requireRole('ADMIN');
        $pdo = PostgresConnection::getConnection();
        $this->userRepo = new PostgresUserRepository($pdo);
        $this->productRepo = new PostgresProductRepository($pdo);
        $this->orderRepo = new PostgresOrderRepository($pdo);
        $this->categoryRepo = new PostgresCategoryRepository($pdo);
    }

    public function index(): void
    {
        $users = $this->userRepo->findAll();
        $orders = $this->orderRepo->findAll(10, 0);
        $productsCount = $this->productRepo->countWithFilters(['status' => 'ACTIVE']);
        $categories = $this->categoryRepo->findAll();

        $totalRevenue = 0.0;
        foreach ($orders as $order) {
            if ($order->status->value === 'PAID') {
                $totalRevenue += $order->totalAmount;
            }
        }

        $data = [
            'title' => 'Panel Administrativo General | AURA Atelier',
            'user' => SessionManager::getUser(),
            'usersCount' => count($users),
            'ordersCount' => count($orders),
            'productsCount' => $productsCount,
            'categoriesCount' => count($categories),
            'totalRevenue' => $totalRevenue,
            'recentOrders' => $orders,
        ];

        echo Router::renderView('admin/dashboard', $data, 'admin');
    }

    public function users(): void
    {
        $users = $this->userRepo->findAll();
        $data = [
            'title' => 'Gestión de Usuarios y Roles | AURA Atelier',
            'user' => SessionManager::getUser(),
            'users' => $users,
        ];

        echo Router::renderView('admin/users', $data, 'admin');
    }

    public function updateRole(): void
    {
        $userId = $_POST['user_id'] ?? '';
        $roleStr = $_POST['role'] ?? 'CLIENT';

        $user = $this->userRepo->findById($userId);
        if ($user) {
            $user->role = Role::from($roleStr);
            $this->userRepo->update($user);
            SessionManager::setFlash('success', "Rol actualizado para {$user->name}.");
        }

        header('Location: /admin/users');
        exit;
    }

    public function categories(): void
    {
        $categories = $this->categoryRepo->findAll();
        $data = [
            'title' => 'Categorías del Catálogo | AURA Atelier',
            'user' => SessionManager::getUser(),
            'categories' => $categories,
        ];

        echo Router::renderView('admin/categories', $data, 'admin');
    }

    public function createCategory(): void
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');

        if (!empty($name)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            $category = new Category(
                id: null,
                name: $name,
                slug: $slug,
                description: $description,
                imageUrl: $imageUrl
            );
            $this->categoryRepo->save($category);
            SessionManager::setFlash('success', "Categoría '{$name}' creada.");
        }

        header('Location: /admin/categories');
        exit;
    }
}
