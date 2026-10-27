<div class="space-y-8">

    <!-- Encabezado con Botón de Creación -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-stone-200">
        <div>
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">Panel de Control del Vendedor</h1>
            <p class="text-xs text-stone-500 mt-1">
                Bienvenido, <strong class="text-stone-800"><?= htmlspecialchars($user['name']) ?></strong>. Gestiona tu catálogo de moda y monitorea tu inventario.
            </p>
        </div>
        <a href="/seller/product/new" class="px-5 py-2.5 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider flex items-center gap-2 shadow-sm transition-all">
            <i class="fa-solid fa-plus"></i> Publicar Nueva Prenda
        </a>
    </div>

    <!-- Indicadores Clave del Vendedor -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Mis Modelos</span>
                <i class="fa-solid fa-shirt text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900"><?= $totalProducts ?></p>
            <span class="text-[11px] text-stone-400">Prendas activas</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Stock Total</span>
                <i class="fa-solid fa-boxes-stacked text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900"><?= $totalUnits ?></p>
            <span class="text-[11px] text-emerald-600 font-semibold">Piezas en almacén</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Valoración Stock</span>
                <i class="fa-solid fa-sack-dollar text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900">$<?= number_format($totalValuation, 2) ?></p>
            <span class="text-[11px] text-stone-400">MXN a precio catálogo</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Ventas / Pedidos</span>
                <i class="fa-solid fa-receipt text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900"><?= count($orders) ?></p>
            <span class="text-[11px] text-stone-400">Transacciones generadas</span>
        </div>
    </div>

    <!-- Tabla de Prendas Publicadas -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-stone-900">Catálogo de Prendas Publicadas</h2>
            <span class="text-xs text-stone-400 font-medium"><?= count($products) ?> prendas</span>
        </div>

        <?php if (empty($products)): ?>
            <div class="p-12 text-center text-xs text-stone-500">
                <p class="mb-4">Aún no has publicado prendas en la tienda.</p>
                <a href="/seller/product/new" class="text-stone-900 font-bold underline">Publicar mi primera prenda</a>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-6">Prenda</th>
                            <th class="py-3 px-4">Categoría</th>
                            <th class="py-3 px-4">Precio Base</th>
                            <th class="py-3 px-4">Variantes</th>
                            <th class="py-3 px-4">Stock Total</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-6 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-700">
                        <?php foreach ($products as $p): ?>
                            <tr class="hover:bg-stone-50/60 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <img src="<?= htmlspecialchars($p->getPrimaryImage()) ?>" alt="" 
                                             class="w-12 h-14 object-cover rounded-lg border bg-stone-100 flex-shrink-0">
                                        <div>
                                            <strong class="text-stone-900 font-bold block text-sm"><?= htmlspecialchars($p->name) ?></strong>
                                            <span class="text-[11px] text-stone-400 font-mono"><?= htmlspecialchars($p->slug) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-medium text-stone-800">
                                    <?= htmlspecialchars($p->categoryName ?? 'General') ?>
                                </td>
                                <td class="py-4 px-4 font-bold text-stone-900">
                                    $<?= number_format($p->basePrice, 2) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        <?php foreach ($p->variants as $v): ?>
                                            <span class="px-1.5 py-0.5 rounded text-[10px] bg-stone-100 text-stone-700 font-medium">
                                                <?= htmlspecialchars($v->size) ?>: <?= $v->stock ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <?php $stock = $p->getTotalStock(); ?>
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[11px] font-bold <?= $stock <= 10 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' ?>">
                                        <?= $stock ?> piezas
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold <?= $p->status === 'ACTIVE' ? 'bg-blue-100 text-blue-800' : 'bg-stone-200 text-stone-600' ?>">
                                        <?= htmlspecialchars($p->status) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="/seller/product/edit/<?= htmlspecialchars($p->id) ?>" 
                                           class="p-2 text-stone-600 hover:text-stone-900 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors" title="Editar">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <a href="/product/<?= htmlspecialchars($p->slug) ?>" target="_blank"
                                           class="p-2 text-stone-600 hover:text-stone-900 bg-stone-100 hover:bg-stone-200 rounded-lg transition-colors" title="Ver en Tienda">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                        <form action="/seller/product/delete/<?= htmlspecialchars($p->id) ?>" method="POST" onsubmit="return confirm('¿Confirmas que deseas eliminar esta prenda del catálogo?')">
                                            <button type="submit" class="p-2 text-rose-500 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 rounded-lg transition-colors" title="Eliminar">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
