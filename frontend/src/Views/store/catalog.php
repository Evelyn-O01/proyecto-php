<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Hero / Announcement Banner -->
    <div class="relative rounded-2xl overflow-hidden bg-stone-900 text-white mb-10 shadow-lg">
        <div class="absolute inset-0 opacity-40 bg-cover bg-center" style="background-image: url('https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1600&q=80')"></div>
        <div class="relative z-10 p-8 sm:p-14 max-w-2xl">
            <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-semibold uppercase tracking-widest mb-4">
                Colección Exclusiva 2026
            </span>
            <h1 class="font-serif text-3xl sm:text-5xl font-bold tracking-tight mb-4 leading-tight">
                Elegancia atemporal, siluetas contemporáneas.
            </h1>
            <p class="text-stone-300 text-sm sm:text-base leading-relaxed mb-6 font-light">
                Descubre prendas confeccionadas con materiales de calidad superior, diseñadas por vendedores y talleres independientes selectos.
            </p>
            <div class="flex flex-wrap gap-3">
                <a href="#catalogo" class="bg-white text-stone-900 text-xs uppercase tracking-widest font-bold px-6 py-3 rounded-full hover:bg-stone-200 transition-all">
                    Explorar Catálogo
                </a>
            </div>
        </div>
    </div>

    <div id="catalogo" class="flex flex-col lg:flex-row gap-8">

        <!-- Barra Lateral de Filtros -->
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white p-6 rounded-2xl border border-stone-200 shadow-sm sticky top-28">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-6">
                    <h3 class="font-serif text-lg font-bold text-stone-900">Filtros</h3>
                    <?php if (!empty($current_filters)): ?>
                        <a href="/catalog" class="text-xs text-rose-600 hover:underline font-semibold">Limpiar</a>
                    <?php endif; ?>
                </div>

                <form action="/catalog" method="GET" class="space-y-6">

                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-semibold text-stone-700 mb-2">Búsqueda</label>
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Ej. Chaqueta, Seda..." 
                               class="w-full text-xs p-2.5 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
                    </div>

                    <!-- Categorías -->
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-semibold text-stone-700 mb-2">Categoría</label>
                        <div class="space-y-1.5 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer hover:text-stone-900">
                                <input type="radio" name="category" value="" <?= empty($_GET['category']) ? 'checked' : '' ?> class="text-stone-900 focus:ring-0">
                                <span class="<?= empty($_GET['category']) ? 'font-bold text-stone-900' : 'text-stone-600' ?>">Todas las categorías</span>
                            </label>
                            <?php foreach ($categories as $cat): 
                                $catSlug = is_array($cat) ? $cat['slug'] : $cat->slug;
                                $catName = is_array($cat) ? $cat['name'] : $cat->name;
                            ?>
                                <label class="flex items-center gap-2 cursor-pointer hover:text-stone-900">
                                    <input type="radio" name="category" value="<?= htmlspecialchars($catSlug) ?>" 
                                           <?= (($_GET['category'] ?? '') === $catSlug) ? 'checked' : '' ?>
                                           class="text-stone-900 focus:ring-0">
                                    <span class="<?= (($_GET['category'] ?? '') === $catSlug) ? 'font-bold text-stone-900' : 'text-stone-600' ?>">
                                        <?= htmlspecialchars($catName) ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Tallas Disponibles -->
                    <?php if (!empty($available_sizes)): ?>
                        <div>
                            <label class="block text-xs uppercase tracking-wider font-semibold text-stone-700 mb-2">Talla</label>
                            <div class="flex flex-wrap gap-1.5">
                                <?php foreach ($available_sizes as $sz): ?>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="size" value="<?= htmlspecialchars($sz) ?>" 
                                               <?= (($_GET['size'] ?? '') === $sz) ? 'checked' : '' ?> class="hidden peer">
                                        <span class="inline-block px-3 py-1.5 text-xs rounded-lg border border-stone-200 bg-stone-50 text-stone-700 peer-checked:bg-stone-900 peer-checked:text-white peer-checked:border-stone-900 font-semibold transition-all">
                                            <?= htmlspecialchars($sz) ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Rango de Precios -->
                    <div>
                        <label class="block text-xs uppercase tracking-wider font-semibold text-stone-700 mb-2">Precio Máximo</label>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-stone-500">$</span>
                            <input type="number" name="max_price" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>" placeholder="Ej. 200"
                                   class="w-full text-xs p-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
                        </div>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-stone-900 hover:bg-stone-800 text-white text-xs uppercase tracking-widest font-bold rounded-xl transition-all shadow-sm">
                        Aplicar Filtros
                    </button>
                </form>
            </div>
        </aside>

        <!-- Cuadrícula de Productos -->
        <div class="flex-1">
            <div class="flex items-center justify-between mb-6">
                <p class="text-xs text-stone-500 font-medium">
                    Mostrando <strong class="text-stone-900"><?= count($products) ?></strong> de <strong class="text-stone-900"><?= $total ?></strong> prendas exclusivas
                </p>
                <div class="text-xs text-stone-500">
                    Ordenado por: <span class="font-semibold text-stone-800">Nuevos lanzamientos</span>
                </div>
            </div>

            <?php if (empty($products)): ?>
                <div class="bg-white rounded-2xl border border-stone-200 p-12 text-center my-8">
                    <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                        <i class="fa-solid fa-shirt"></i>
                    </div>
                    <h3 class="font-serif text-xl font-bold text-stone-900 mb-2">No encontramos prendas con esos filtros</h3>
                    <p class="text-xs text-stone-500 max-w-sm mx-auto mb-6">
                        Intenta ajustar los criterios de búsqueda, cambiar la talla o remover filtros de precio.
                    </p>
                    <a href="/catalog" class="inline-block px-5 py-2.5 bg-stone-900 text-white text-xs uppercase tracking-wider font-semibold rounded-full hover:bg-stone-800">
                        Ver todo el catálogo
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    <?php foreach ($products as $p): 
                        $pId = is_array($p) ? $p['id'] : $p->id;
                        $pSlug = is_array($p) ? $p['slug'] : $p->slug;
                        $pName = is_array($p) ? $p['name'] : $p->name;
                        $pDesc = is_array($p) ? $p['description'] : $p->description;
                        $pPrice = is_array($p) ? $p['base_price'] : $p->basePrice;
                        $pImg = is_array($p) ? $p['primary_image'] : $p->getPrimaryImage();
                        $pStock = is_array($p) ? $p['total_stock'] : $p->getTotalStock();
                        $pCatName = is_array($p) ? ($p['category_name'] ?? 'Moda') : ($p->categoryName ?? 'Moda');
                        $pSeller = is_array($p) ? ($p['seller_name'] ?? 'Vendedor') : ($p->sellerName ?? 'Vendedor');
                        $pSizes = is_array($p) ? ($p['available_sizes'] ?? []) : $p->getAvailableSizes();
                    ?>
                        <div class="group bg-white rounded-2xl border border-stone-200/80 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                            
                            <!-- Imagen y Badges -->
                            <div class="relative aspect-[3/4] bg-stone-100 overflow-hidden">
                                <a href="/product/<?= htmlspecialchars($pSlug) ?>">
                                    <img src="<?= htmlspecialchars($pImg) ?>" 
                                         alt="<?= htmlspecialchars($pName) ?>" 
                                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>

                                <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                                    <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider bg-white/90 backdrop-blur-md rounded-md text-stone-800 shadow-sm">
                                        <?= htmlspecialchars($pCatName) ?>
                                    </span>
                                    <?php if ($pStock <= 10 && $pStock > 0): ?>
                                        <span class="px-2.5 py-0.5 text-[9px] font-bold uppercase tracking-wider bg-amber-500 text-white rounded-md shadow-sm">
                                            Últimas <?= $pStock ?> piezas
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Información de la Prenda -->
                            <div class="p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] text-stone-400 mb-1">
                                        <span>Por: <?= htmlspecialchars($pSeller) ?></span>
                                        <span>Stock: <?= $pStock ?> uds</span>
                                    </div>

                                    <h3 class="font-serif font-bold text-stone-900 text-base mb-1.5 group-hover:text-amber-800 transition-colors line-clamp-1">
                                        <a href="/product/<?= htmlspecialchars($pSlug) ?>">
                                            <?= htmlspecialchars($pName) ?>
                                        </a>
                                    </h3>

                                    <p class="text-xs text-stone-500 line-clamp-2 mb-3 leading-relaxed font-light">
                                        <?= htmlspecialchars($pDesc) ?>
                                    </p>
                                </div>

                                <div>
                                    <!-- Variantes de Tallas -->
                                    <div class="flex items-center gap-1 mb-3">
                                        <span class="text-[10px] text-stone-400 uppercase font-semibold mr-1">Tallas:</span>
                                        <?php foreach ($pSizes as $size): ?>
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-stone-100 text-stone-700">
                                                <?= htmlspecialchars($size) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>

                                    <!-- Precio y Botón -->
                                    <div class="pt-3 border-t border-stone-100 flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] text-stone-400 block uppercase font-medium">Precio</span>
                                            <span class="font-serif text-lg font-bold text-stone-900">
                                                $<?= number_format($pPrice, 2) ?> <span class="text-xs font-normal text-stone-500">MXN</span>
                                            </span>
                                        </div>

                                        <a href="/product/<?= htmlspecialchars($pSlug) ?>" 
                                           class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                                            <span>Detalles</span>
                                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginación -->
                <?php if ($total_pages > 1): ?>
                    <div class="flex justify-center items-center gap-2 mt-12">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?= $i ?><?= !empty($_GET['category']) ? '&category=' . urlencode($_GET['category']) : '' ?><?= !empty($_GET['q']) ? '&q=' . urlencode($_GET['q']) : '' ?>" 
                               class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold transition-all <?= (int)$page === $i ? 'bg-stone-900 text-white shadow' : 'bg-white border border-stone-200 text-stone-700 hover:bg-stone-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>
