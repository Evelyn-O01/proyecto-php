<!DOCTYPE html>
<html lang="es" class="h-full bg-stone-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AURA Atelier | Tienda de Ropa Online') ?></title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts (Playfair Display & Plus Jakarta Sans) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        atelier: {
                            50: '#faf8f5',
                            100: '#f5f0eb',
                            200: '#e8dcce',
                            300: '#dbc3af',
                            500: '#b08968',
                            700: '#7f5539',
                            800: '#583a25',
                            900: '#2b1a10',
                            dark: '#1a1918'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="flex flex-col min-h-screen text-stone-800 antialiased selection:bg-stone-900 selection:text-white">

    <!-- Barra de Notificación Superior -->
    <div class="bg-stone-900 text-stone-200 text-xs py-2 px-4 text-center tracking-widest uppercase font-medium">
        <span>✨ Envío express gratuito en compras superiores a $999 MXN | Temporada Otoño/Invierno 2026</span>
    </div>

    <!-- Navegación Principal -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logotipo -->
                <div class="flex items-center gap-8">
                    <a href="/catalog" class="flex flex-col">
                        <span class="font-serif text-2xl sm:text-3xl font-bold tracking-tight text-stone-900">AURA</span>
                        <span class="text-[9px] uppercase tracking-[0.28em] text-stone-500 font-semibold -mt-1">Atelier & Boutique</span>
                    </a>
                    
                    <!-- Enlaces Principales -->
                    <nav class="hidden md:flex items-center space-x-6 text-sm font-medium text-stone-700">
                        <a href="/catalog" class="hover:text-stone-900 transition-colors">Colección</a>
                        <a href="/catalog?category=hombre" class="hover:text-stone-900 transition-colors">Hombre</a>
                        <a href="/catalog?category=mujer" class="hover:text-stone-900 transition-colors">Mujer</a>
                        <a href="/catalog?category=abrigos-chaquetas" class="hover:text-stone-900 transition-colors">Abrigos</a>
                        <a href="/catalog?category=calzado" class="hover:text-stone-900 transition-colors">Calzado</a>
                    </nav>
                </div>

                <!-- Búsqueda y Acciones de Usuario -->
                <div class="flex items-center space-x-4 sm:space-x-6">
                    <!-- Formulario de Búsqueda Rápida -->
                    <form action="/catalog" method="GET" class="hidden sm:flex items-center relative">
                        <input type="text" name="q" placeholder="Buscar prendas..." 
                               value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                               class="w-44 md:w-64 pl-9 pr-4 py-1.5 text-xs bg-stone-100 rounded-full border border-stone-200 focus:outline-none focus:border-stone-900 focus:bg-white transition-all">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 text-stone-400 text-xs"></i>
                    </form>

                    <!-- Carrito / Bolsa de Compras -->
                    <?php 
                        $cartQty = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0;
                    ?>
                    <a href="/cart" class="relative text-stone-700 hover:text-stone-900 transition-colors p-1" title="Bolsa de compras">
                        <i class="fa-solid fa-bag-shopping text-xl"></i>
                        <?php if ($cartQty > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-stone-900 text-white text-[10px] font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                <?= $cartQty ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Menú de Usuario / Login -->
                    <?php if (isset($_SESSION['user'])): ?>
                        <div class="relative group">
                            <button class="flex items-center gap-2 text-stone-700 hover:text-stone-900 text-sm font-medium focus:outline-none">
                                <div class="w-8 h-8 rounded-full bg-stone-200 border border-stone-300 flex items-center justify-center text-xs font-bold text-stone-700 uppercase">
                                    <?= substr($_SESSION['user']['name'], 0, 2) ?>
                                </div>
                                <span class="hidden md:inline"><?= htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0]) ?></span>
                                <i class="fa-solid fa-chevron-down text-[10px] text-stone-400"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div class="absolute right-0 mt-2 w-56 bg-white border border-stone-200 rounded-xl shadow-xl py-2 hidden group-hover:block transition-all z-50">
                                <div class="px-4 py-2 border-b border-stone-100">
                                    <p class="text-xs text-stone-400">Sesión iniciada como</p>
                                    <p class="text-sm font-semibold text-stone-900 truncate"><?= htmlspecialchars($_SESSION['user']['email']) ?></p>
                                    <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold rounded bg-stone-100 text-stone-800">
                                        <?= htmlspecialchars($_SESSION['user']['role']) ?>
                                    </span>
                                </div>

                                <?php if ($_SESSION['user']['role'] === 'ADMIN'): ?>
                                    <a href="/admin" class="flex items-center gap-2.5 px-4 py-2 text-xs text-stone-700 hover:bg-stone-50 font-medium">
                                        <i class="fa-solid fa-gauge-high text-stone-500 w-4"></i> Panel de Administración
                                    </a>
                                    <a href="/admin/reports/sales" class="flex items-center gap-2.5 px-4 py-2 text-xs text-stone-700 hover:bg-stone-50 font-medium">
                                        <i class="fa-solid fa-chart-line text-stone-500 w-4"></i> Reportes Ejecutivos
                                    </a>
                                <?php endif; ?>

                                <a href="/orders" class="flex items-center gap-2.5 px-4 py-2 text-xs text-stone-700 hover:bg-stone-50 font-medium">
                                    <i class="fa-solid fa-box-archive text-stone-500 w-4"></i> Mis Pedidos
                                </a>

                                <?php if ($_SESSION['user']['role'] === 'SELLER' || $_SESSION['user']['role'] === 'ADMIN'): ?>
                                    <a href="/seller" class="flex items-center gap-2.5 px-4 py-2 text-xs text-stone-700 hover:bg-stone-50 font-medium">
                                        <i class="fa-solid fa-shirt text-stone-500 w-4"></i> Panel de Vendedor
                                    </a>
                                    <a href="/seller/product/new" class="flex items-center gap-2.5 px-4 py-2 text-xs text-stone-700 hover:bg-stone-50 font-medium">
                                        <i class="fa-solid fa-plus text-stone-500 w-4"></i> Publicar Nueva Prenda
                                    </a>
                                <?php endif; ?>

                                <a href="/logout" class="flex items-center gap-2.5 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-medium border-t border-stone-100 mt-1">
                                    <i class="fa-solid fa-arrow-right-from-bracket w-4"></i> Cerrar Sesión
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center gap-3">
                            <a href="/login" class="text-xs uppercase tracking-wider font-semibold text-stone-700 hover:text-stone-900 transition-colors">
                                Ingresar
                            </a>
                            <a href="/register" class="hidden sm:inline-block text-xs uppercase tracking-wider font-semibold bg-stone-900 text-white px-4 py-2 rounded-full hover:bg-stone-800 transition-all">
                                Registrarse
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Notificaciones Flash -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
        <?php foreach (\App\Presentation\Middlewares\SessionManager::getFlash('success') as $msg): ?>
            <div class="mb-3 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">&times;</button>
            </div>
        <?php endforeach; ?>

        <?php foreach (\App\Presentation\Middlewares\SessionManager::getFlash('error') as $msg): ?>
            <div class="mb-3 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-lg"></i>
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800">&times;</button>
            </div>
        <?php endforeach; ?>

        <?php foreach (\App\Presentation\Middlewares\SessionManager::getFlash('info') as $msg): ?>
            <div class="mb-3 p-4 rounded-xl bg-blue-50 border border-blue-200 text-blue-800 text-sm flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-info text-blue-600 text-lg"></i>
                    <span><?= htmlspecialchars($msg) ?></span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-blue-500 hover:text-blue-800">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Contenido de la Vista -->
    <main class="flex-grow">
        <?= $content ?>
    </main>

    <!-- Pie de Página -->
    <footer class="bg-stone-900 text-stone-400 text-sm border-t border-stone-800 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="space-y-4">
                    <span class="font-serif text-2xl font-bold tracking-tight text-white">AURA</span>
                    <p class="text-xs leading-relaxed text-stone-400">
                        Atelier de moda de alta gama con curaduría selecta. Prendas diseñadas con tejidos nobles, cortes contemporáneos y producción sostenible.
                    </p>
                    <div class="flex gap-4 text-stone-400 text-base">
                        <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-pinterest"></i></a>
                        <a href="#" class="hover:text-white transition-colors"><i class="fa-brands fa-tiktok"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-xs uppercase tracking-widest text-white font-semibold mb-4">Catálogo</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/catalog?category=mujer" class="hover:text-white transition-colors">Colección Mujer</a></li>
                        <li><a href="/catalog?category=hombre" class="hover:text-white transition-colors">Colección Hombre</a></li>
                        <li><a href="/catalog?category=abrigos-chaquetas" class="hover:text-white transition-colors">Abrigos & Blazers</a></li>
                        <li><a href="/catalog?category=calzado" class="hover:text-white transition-colors">Calzado & Sneakers</a></li>
                        <li><a href="/catalog?category=accesorios" class="hover:text-white transition-colors">Bolsos & Marroquinería</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs uppercase tracking-widest text-white font-semibold mb-4">Roles de Acceso Rápido</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="/auth/quick/admin" class="hover:text-amber-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-shield-halved text-[10px]"></i> Probar como Administrador</a></li>
                        <li><a href="/auth/quick/seller" class="hover:text-amber-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-store text-[10px]"></i> Probar como Vendedor</a></li>
                        <li><a href="/auth/quick/client" class="hover:text-amber-400 transition-colors flex items-center gap-1.5"><i class="fa-solid fa-user text-[10px]"></i> Probar como Cliente</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs uppercase tracking-widest text-white font-semibold mb-4">Pasarela Segura</h4>
                    <p class="text-xs text-stone-400 mb-3">Transacciones cifradas bajo estándares PCI-DSS con soporte para tarjetas de crédito, débito y tokenización segura.</p>
                    <div class="flex items-center gap-3 text-stone-500 text-2xl">
                        <i class="fa-brands fa-cc-visa hover:text-white transition-colors"></i>
                        <i class="fa-brands fa-cc-mastercard hover:text-white transition-colors"></i>
                        <i class="fa-brands fa-cc-amex hover:text-white transition-colors"></i>
                        <i class="fa-brands fa-stripe hover:text-white transition-colors"></i>
                    </div>
                </div>
            </div>

            <div class="border-t border-stone-800 mt-12 pt-6 flex flex-col sm:flex-row items-center justify-between text-xs text-stone-500">
                <p>&copy; <?= date('Y') ?> AURA Atelier. Todos los derechos reservados. Desarrollado con PHP 8.5 & PostgreSQL (Arquitectura Hexagonal).</p>
                <div class="flex gap-4 mt-4 sm:mt-0">
                    <a href="#" class="hover:text-stone-300">Términos y Condiciones</a>
                    <a href="#" class="hover:text-stone-300">Política de Privacidad</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
