<?php if (isset($_GET['print'])): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Inventario - AURA Atelier</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        @page { size: A4; margin: 15mm; }
    </style>
</head>
<body class="bg-white text-stone-900 p-8" onload="window.print()">
<?php endif; ?>

<div class="space-y-8 print:p-0">

    <!-- Filtros de Inventario -->
    <div class="no-print bg-white p-5 rounded-3xl border border-stone-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="/admin/reports/inventory" method="GET" class="flex flex-wrap items-center gap-3 text-xs w-full md:w-auto">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">Categoría</label>
                <select name="category_id" class="p-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c->id ?>" <?= $selected_category_id === $c->id ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c->name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">Umbral Stock Bajo</label>
                <input type="number" name="threshold" value="<?= $low_stock_threshold ?>" min="1" max="100"
                       class="w-24 p-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
            </div>

            <div class="pt-4">
                <button type="submit" class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg font-bold">
                    Filtrar
                </button>
            </div>
        </form>

        <div class="flex items-center gap-2">
            <a href="/admin/reports/inventory?threshold=<?= $low_stock_threshold ?>&export=csv" 
               class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-file-csv text-emerald-700"></i> Descargar CSV
            </a>
            <button onclick="window.print()" class="px-4 py-2.5 bg-stone-900 hover:bg-stone-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition-colors">
                <i class="fa-solid fa-print"></i> Imprimir Reporte
            </button>
            <a href="/admin/reports/inventory?threshold=<?= $low_stock_threshold ?>&print=1" target="_blank"
               class="px-4 py-2.5 border border-stone-300 text-stone-700 hover:bg-stone-50 font-bold rounded-xl text-xs flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-file-pdf"></i> Modo PDF
            </a>
        </div>
    </div>

    <!-- DOCUMENTO DE REPORTE DISEÑADO -->
    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-12 shadow-sm print:border-none print:shadow-none print:p-0 space-y-10">

        <!-- Cabecera -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-8 border-b-2 border-stone-900 gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="font-serif text-3xl font-bold tracking-tight text-stone-900">AURA</span>
                    <span class="h-6 w-px bg-stone-300"></span>
                    <span class="text-xs uppercase tracking-widest text-stone-500 font-semibold">Atelier & Boutiques S.A. de C.V.</span>
                </div>
                <h1 class="font-serif text-2xl font-bold text-stone-900 mt-2">
                    Reporte de Existencias & Stock Crítico
                </h1>
                <p class="text-xs text-stone-500 mt-0.5">
                    Inventario físico consolidado y valuación a precio catálogo.
                </p>
            </div>

            <div class="text-left sm:text-right text-xs text-stone-500 space-y-1 bg-stone-50 sm:bg-transparent p-4 sm:p-0 rounded-2xl">
                <div>Código: <span class="font-mono font-bold text-stone-900">RPT-INV-<?= date('Ymd') ?></span></div>
                <div>Emisión: <span class="font-medium text-stone-800"><?= $generated_at ?></span></div>
                <div>Alerta: <span class="font-bold text-rose-600">&le; <?= $low_stock_threshold ?> unidades</span></div>
            </div>
        </div>

        <!-- KPIs de Inventario -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="p-5 rounded-2xl bg-stone-900 text-white shadow-sm">
                <span class="text-[10px] uppercase tracking-wider text-amber-400 font-bold block mb-1">
                    Valoración Total en Bodega
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold">$<?= number_format($summary['total_inventory_value'], 2) ?></p>
                <span class="text-[10px] text-stone-400 mt-1 block">MXN costo retail</span>
            </div>

            <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <span class="text-[10px] uppercase tracking-wider text-stone-500 font-bold block mb-1">
                    Piezas Físicas Disponibles
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold text-stone-900"><?= number_format($summary['total_units']) ?></p>
                <span class="text-[10px] text-stone-500 mt-1 block">En todas las tallas/colores</span>
            </div>

            <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <span class="text-[10px] uppercase tracking-wider text-stone-500 font-bold block mb-1">
                    Modelos / Variantes
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold text-stone-900"><?= $summary['total_products'] ?> <span class="text-sm font-normal text-stone-400">(<?= $summary['total_variants'] ?> variantes)</span></p>
                <span class="text-[10px] text-stone-500 mt-1 block">SKUs activos</span>
            </div>

            <div class="p-5 rounded-2xl <?= $summary['low_stock_count'] > 0 ? 'bg-rose-50 border border-rose-200 text-rose-900' : 'bg-stone-50 border border-stone-200' ?>">
                <span class="text-[10px] uppercase tracking-wider font-bold block mb-1 <?= $summary['low_stock_count'] > 0 ? 'text-rose-700' : 'text-stone-500' ?>">
                    Variantes en Stock Crítico
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold"><?= $summary['low_stock_count'] ?></p>
                <span class="text-[10px] mt-1 block font-semibold <?= $summary['low_stock_count'] > 0 ? 'text-rose-600' : 'text-stone-400' ?>">
                    Requieren resurtido urgente
                </span>
            </div>
        </div>

        <!-- Alerta de Variantes con Stock Bajo (Tabla Resaltada) -->
        <?php if (!empty($low_stock_items)): ?>
            <div class="space-y-4">
                <div class="flex items-center gap-2 text-rose-700">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <h3 class="font-serif font-bold text-stone-900 text-base">
                        Prendas con Existencia Crítica (&le; <?= $low_stock_threshold ?> unidades)
                    </h3>
                </div>

                <div class="border border-rose-200 rounded-2xl overflow-hidden bg-rose-50/20">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-rose-100/60 border-b border-rose-200 text-rose-900 font-semibold uppercase tracking-wider text-[10px]">
                            <tr>
                                <th class="py-3 px-6">Prenda</th>
                                <th class="py-3 px-4">Talla / Color</th>
                                <th class="py-3 px-4">SKU</th>
                                <th class="py-3 px-4">Vendedor</th>
                                <th class="py-3 px-4">Precio</th>
                                <th class="py-3 px-6 text-right">Stock Restante</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-rose-100 text-stone-800">
                            <?php foreach ($low_stock_items as $item): ?>
                                <tr>
                                    <td class="py-3 px-6 font-bold text-stone-900">
                                        <?= htmlspecialchars($item['product_name']) ?>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded bg-white border border-rose-200 font-semibold text-rose-800">
                                            <?= htmlspecialchars($item['size']) ?> · <?= htmlspecialchars($item['color']) ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 font-mono text-[11px] text-stone-500">
                                        <?= htmlspecialchars($item['sku']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-stone-600">
                                        <?= htmlspecialchars($item['seller_name']) ?>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-stone-900">
                                        $<?= number_format($item['final_price'], 2) ?>
                                    </td>
                                    <td class="py-3 px-6 text-right">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold <?= $item['stock'] <= 5 ? 'bg-rose-600 text-white' : 'bg-amber-500 text-stone-950' ?>">
                                            <?= $item['stock'] ?> piezas
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Desglose de Stock por Categoría -->
        <div class="space-y-4">
            <h3 class="font-serif font-bold text-stone-900 text-base">
                Distribución de Inventario por Categoría
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($stock_by_category as $cat): ?>
                    <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-2">
                        <div class="flex justify-between items-center text-xs">
                            <strong class="text-stone-900 font-bold"><?= htmlspecialchars($cat['category_name']) ?></strong>
                            <span class="text-stone-500 font-semibold"><?= $cat['total_units'] ?? 0 ?> uds</span>
                        </div>
                        <div class="w-full h-2 bg-stone-200 rounded-full overflow-hidden">
                            <div class="h-full bg-stone-900 rounded-full" style="width: <?= $cat['percentage'] ?? 0 ?>%"></div>
                        </div>
                        <div class="text-[11px] text-stone-500 flex justify-between">
                            <span>Valoración:</span>
                            <span class="font-bold text-stone-900">$<?= number_format($cat['stock_value'] ?? 0, 2) ?> MXN</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Tabla de Inventario Consolidado -->
        <div class="space-y-4">
            <h3 class="font-serif font-bold text-stone-900 text-base">
                Listado General de Existencias
            </h3>

            <div class="border border-stone-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-6">Prenda</th>
                            <th class="py-3 px-4">Categoría</th>
                            <th class="py-3 px-4">Talla</th>
                            <th class="py-3 px-4">Color</th>
                            <th class="py-3 px-4">SKU</th>
                            <th class="py-3 px-4 text-center">Stock</th>
                            <th class="py-3 px-4">Precio Unitario</th>
                            <th class="py-3 px-6 text-right">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-700">
                        <?php foreach ($full_inventory as $row): ?>
                            <tr>
                                <td class="py-3 px-6 font-bold text-stone-900"><?= htmlspecialchars($row['product_name']) ?></td>
                                <td class="py-3 px-4 text-stone-500"><?= htmlspecialchars($row['category_name']) ?></td>
                                <td class="py-3 px-4 font-semibold"><?= htmlspecialchars($row['size']) ?></td>
                                <td class="py-3 px-4"><?= htmlspecialchars($row['color']) ?></td>
                                <td class="py-3 px-4 font-mono text-[11px] text-stone-400"><?= htmlspecialchars($row['sku']) ?></td>
                                <td class="py-3 px-4 text-center font-bold <?= $row['stock'] <= $low_stock_threshold ? 'text-rose-600' : 'text-stone-800' ?>">
                                    <?= $row['stock'] ?>
                                </td>
                                <td class="py-3 px-4 font-semibold text-stone-800">$<?= number_format($row['final_price'], 2) ?></td>
                                <td class="py-3 px-6 text-right font-serif font-bold text-stone-900">$<?= number_format($row['total_value'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<?php if (isset($_GET['print'])): ?>
</body>
</html>
<?php endif; ?>
