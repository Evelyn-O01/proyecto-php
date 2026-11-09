<?php

namespace App\Frontend\Controllers;

use App\Backend\Views\Engine\ReportViewDispatcher;
use App\Frontend\Services\BackendClient;
use App\Frontend\Services\Session;

class AdminController
{
    private BackendClient $client;

    public function __construct()
    {
        $this->client = new BackendClient();
    }

    public function dashboard(): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('ADMIN')) {
            Session::flash('error', 'Acceso restringido para administradores.');
            header('Location: /login');
            exit;
        }

        try {
            $users = $this->client->get('/api/v1/admin/users');
            $products = $this->client->get('/api/v1/products', ['per_page' => 1]);
            $categories = $this->client->get('/api/v1/categories');
            $reportsData = $this->client->get('/api/v1/reports/sales/data');
        } catch (\Throwable $e) {
            $users = [];
            $products = ['total' => 0];
            $categories = [];
            $reportsData = ['summary' => ['total_sales' => 0, 'total_orders' => 0], 'recent_orders' => []];
        }

        $data = [
            'title' => 'Panel de Administración General | AURA Atelier',
            'user' => $user,
            'usersCount' => count($users),
            'ordersCount' => $reportsData['summary']['total_orders'] ?? 0,
            'totalRevenue' => $reportsData['summary']['total_sales'] ?? 0.0,
            'productsCount' => $products['total'] ?? 0,
            'categoriesCount' => count($categories),
            'recentOrders' => $reportsData['recent_orders'] ?? []
        ];

        echo StoreController::render('admin/dashboard', $data, 'admin');
    }

    public function users(): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('ADMIN')) {
            header('Location: /login');
            exit;
        }

        try {
            $users = $this->client->get('/api/v1/admin/users');
        } catch (\Throwable $e) {
            $users = [];
        }

        $data = [
            'title' => 'Gestión de Usuarios y Roles | AURA Atelier',
            'user' => $user,
            'users' => $users
        ];

        echo StoreController::render('admin/users', $data, 'admin');
    }

    public function updateRole(): void
    {
        $userId = $_POST['user_id'] ?? '';
        $role = $_POST['role'] ?? 'CLIENT';

        try {
            $this->client->post('/api/v1/admin/users/role', [
                'user_id' => $userId,
                'role' => $role
            ]);
            Session::flash('success', 'Rol de usuario actualizado.');
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: /admin/users');
        exit;
    }

    public function categories(): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('ADMIN')) {
            header('Location: /login');
            exit;
        }

        try {
            $categories = $this->client->get('/api/v1/categories');
        } catch (\Throwable $e) {
            $categories = [];
        }

        $data = [
            'title' => 'Categorías del Catálogo | AURA Atelier',
            'user' => $user,
            'categories' => $categories
        ];

        echo StoreController::render('admin/categories', $data, 'admin');
    }

    public function createCategory(): void
    {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $imageUrl = trim($_POST['image_url'] ?? '');

        try {
            $this->client->post('/api/v1/admin/categories', [
                'name' => $name,
                'description' => $description,
                'image_url' => $imageUrl
            ]);
            Session::flash('success', "Categoría '{$name}' creada.");
        } catch (\Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        header('Location: /admin/categories');
        exit;
    }

    public function salesReport(): void
    {
        $this->renderReport('sales', 'Reporte Ejecutivo de Ventas e Ingresos | AURA Atelier');
    }

    public function inventoryReport(): void
    {
        $this->renderReport('inventory', 'Reporte de Inventario y Stock Crítico | AURA Atelier');
    }

    public function sellersReport(): void
    {
        $this->renderReport('sellers', 'Reporte de Desempeño por Vendedor | AURA Atelier');
    }

    private function renderReport(string $method, string $title): void
    {
        $user = Session::getUser();
        if (!$user || !Session::hasRole('ADMIN')) {
            Session::flash('error', 'Acceso restringido para administradores.');
            header('Location: /login');
            exit;
        }

        $dispatcher = new ReportViewDispatcher();

        // El CSV envía sus propias cabeceras y termina con exit().
        if (($_GET['export'] ?? '') === 'csv') {
            $dispatcher->$method();
            return;
        }

        ob_start();
        $dispatcher->$method();
        $reportHtml = ob_get_clean();

        // La impresión genera su propio documento HTML completo.
        if (isset($_GET['print'])) {
            echo $reportHtml;
            return;
        }

        echo StoreController::render('admin/report_embed', [
            'title' => $title,
            'reportHtml' => $reportHtml,
        ], 'admin');
    }
}
