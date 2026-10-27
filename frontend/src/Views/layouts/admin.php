<!DOCTYPE html>
<html lang="es" class="h-full bg-stone-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Panel Administrativo | AURA Atelier') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="h-full flex flex-col md:flex-row antialiased text-stone-800">

    <?php $currentUser = \App\Frontend\Services\Session::getUser(); ?>

    <!-- Barra Lateral (Sidebar) -->
    <aside class="no-print w-full md:w-64 bg-stone-900 text-stone-300 flex-shrink-0 flex flex-col justify-between border-r border-stone-800 min-h-screen">
        <div>
            <!-- Marca / Header Sidebar -->
            <div class="px-6 py-6 border-b border-stone-800 flex items-center justify-between">
                <div>
                    <a href="/catalog" class="font-serif text-2xl font-bold tracking-tight text-white">AURA</a>
                    <span class="block text-[9px] uppercase tracking-widest text-stone-400 font-semibold">Console Manager</span>
                </div>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded <?= ($currentUser['role'] ?? '') === 'ADMIN' ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' ?>">
                    <?= htmlspecialchars($currentUser['role'] ?? '') ?>
                </span>
            </div>

            <!-- Navegación -->
            <nav class="px-4 py-6 space-y-1 text-sm font-medium">
                
                <?php if (($currentUser['role'] ?? '') === 'ADMIN'): ?>
                    <div class="px-3 pb-2 text-[10px] uppercase font-bold tracking-wider text-stone-500">Administración Global</div>
                    <a href="/admin" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-chart-pie w-5 text-stone-400"></i> Dashboard General
                    </a>
                    <a href="/admin/users" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-users-gear w-5 text-stone-400"></i> Gestión de Usuarios
                    </a>
                    <a href="/admin/categories" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-tags w-5 text-stone-400"></i> Categorías de Ropa
                    </a>

                    <div class="pt-6 px-3 pb-2 text-[10px] uppercase font-bold tracking-wider text-stone-500">Reportes Ejecutivos Diseñados</div>
                    <a href="/admin/reports/sales" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-amber-400 font-medium transition-colors">
                        <i class="fa-solid fa-receipt w-5"></i> Reporte de Ventas
                    </a>
                    <a href="/admin/reports/inventory" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-boxes-stacked w-5 text-stone-400"></i> Reporte de Inventario
                    </a>
                    <a href="/admin/reports/sellers" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-hand-holding-dollar w-5 text-stone-400"></i> Desempeño Vendedores
                    </a>
                <?php endif; ?>

                <?php if (($currentUser['role'] ?? '') === 'SELLER' || ($currentUser['role'] ?? '') === 'ADMIN'): ?>
                    <div class="pt-6 px-3 pb-2 text-[10px] uppercase font-bold tracking-wider text-stone-500">Módulo de Vendedor</div>
                    <a href="/seller" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-store w-5 text-stone-400"></i> Mis Prendas & Stock
                    </a>
                    <a href="/seller/product/new" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-square-plus w-5 text-stone-400"></i> Publicar Prenda
                    </a>
                    <a href="/seller/sales" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-200 transition-colors">
                        <i class="fa-solid fa-truck-fast w-5 text-stone-400"></i> Pedidos Recibidos
                    </a>
                <?php endif; ?>

                <div class="pt-6 px-3 pb-2 text-[10px] uppercase font-bold tracking-wider text-stone-500">Navegación Tienda</div>
                <a href="/catalog" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-stone-800 text-stone-400 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square w-5"></i> Ir al Catálogo Público
                </a>
            </nav>
        </div>

        <!-- Perfil Inferior & Cerrar Sesión -->
        <div class="p-4 border-t border-stone-800">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-stone-800 border border-stone-700 flex items-center justify-center font-bold text-xs text-white">
                        <?= substr($currentUser['name'] ?? 'U', 0, 2) ?>
                    </div>
                    <div class="overflow-hidden">
                        <p class="text-xs font-semibold text-white truncate"><?= htmlspecialchars($currentUser['name'] ?? '') ?></p>
                        <p class="text-[11px] text-stone-400 truncate"><?= htmlspecialchars($currentUser['email'] ?? '') ?></p>
                    </div>
                </div>
                <a href="/logout" class="text-stone-400 hover:text-rose-400 text-sm p-1.5 transition-colors" title="Cerrar Sesión">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">
        <!-- Top Bar Móvil / Acciones Rápidas -->
        <header class="no-print bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <h1 class="text-lg font-bold text-stone-900"><?= htmlspecialchars($title ?? 'Consola') ?></h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="/catalog" class="text-xs font-semibold bg-stone-100 hover:bg-stone-200 text-stone-800 px-3.5 py-2 rounded-lg transition-colors flex items-center gap-1.5">
                    <i class="fa-solid fa-eye text-stone-500"></i> Ver Tienda
                </a>
                <a href="/logout" class="text-xs font-semibold text-rose-600 hover:text-rose-700 px-3 py-2 rounded-lg transition-colors">
                    Salir
                </a>
            </div>
        </header>

        <!-- Mensajes Flash -->
        <div class="no-print max-w-7xl mx-auto px-6 pt-4 w-full">
            <?php foreach (\App\Frontend\Services\Session::getFlash('success') as $msg): ?>
                <div class="mb-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span><?= htmlspecialchars($msg) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
                </div>
            <?php endforeach; ?>

            <?php foreach (\App\Frontend\Services\Session::getFlash('error') as $msg): ?>
                <div class="mb-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                        <span><?= htmlspecialchars($msg) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Render de la Vista del Panel -->
        <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
            <?= $content ?>
        </main>
    </div>

</body>
</html>
