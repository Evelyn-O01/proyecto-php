<?php if (isset($_GET['print'])): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas - AURA Atelier</title>
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

    <!-- Barra de Filtros y Acciones (Oculta en Impresión) -->
    <div class="no-print bg-white p-5 rounded-3xl border border-stone-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="/admin/reports/sales" method="GET" class="flex flex-wrap items-center gap-3 text-xs w-full md:w-auto">
            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">Desde</label>
                <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>" 
                       class="p-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">Hasta</label>
                <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>" 
                       class="p-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">Categoría</label>
                <select name="category_id" class="p-2 bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categories as $c): 
                        $cId = is_array($c) ? $c['id'] : $c->id;
                        $cName = is_array($c) ? $c['name'] : $c->name;
                    ?>
                        <option value="<?= $cId ?>" <?= $selected_category_id === $cId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="pt-4">
                <button type="submit" class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg font-bold">
                    Filtrar
                </button>
            </div>
        </form>

        <div class="flex items-center gap-2">
            <a href="/admin/reports/sales?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&export=csv" 
               class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold rounded-xl text-xs flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-file-csv text-emerald-700"></i> Descargar CSV
            </a>
            <button onclick="window.print()" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition-colors">
                <i class="fa-solid fa-print"></i> Imprimir Reporte
            </button>
            <a href="/admin/reports/sales?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>&print=1" target="_blank"
               class="px-4 py-2.5 border border-stone-300 text-stone-700 hover:bg-stone-50 font-bold rounded-xl text-xs flex items-center gap-2 transition-colors">
                <i class="fa-solid fa-file-pdf"></i> Modo PDF / A4
            </a>
        </div>
    </div>

    <!-- DOCUMENTO DE REPORTE DISEÑADO -->
    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-12 shadow-sm print:border-none print:shadow-none print:p-0 space-y-10">
        
        <!-- Cabecera Corporativa de Alta Costura -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-8 border-b-2 border-stone-900 gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="font-serif text-3xl font-bold tracking-tight text-stone-900">AURA</span>
                    <span class="h-6 w-px bg-stone-300"></span>
                    <span class="text-xs uppercase tracking-widest text-stone-500 font-semibold">Atelier & Boutiques S.A. de C.V.</span>
                </div>
                <h1 class="font-serif text-2xl font-bold text-stone-900 mt-2">
                    Reporte Ejecutivo de Ventas & Facturación
                </h1>
                <p class="text-xs text-stone-500 mt-0.5">
                    Período auditado: <strong class="text-stone-900"><?= date('d/m/Y', strtotime($start_date)) ?></strong> al <strong class="text-stone-900"><?= date('d/m/Y', strtotime($end_date)) ?></strong>
                </p>
            </div>

            <div class="text-left sm:text-right text-xs text-stone-500 space-y-1 bg-stone-50 sm:bg-transparent p-4 sm:p-0 rounded-2xl">
                <div>Código: <span class="font-mono font-bold text-stone-900">RPT-SLS-<?= date('Ymd') ?></span></div>
                <div>Emisión: <span class="font-medium text-stone-800"><?= $generated_at ?></span></div>
                <div>Moneda: <span class="font-bold text-stone-900">Pesos Mexicanos (MXN)</span></div>
                <div>Auditor: <span class="font-medium text-stone-800"><?= htmlspecialchars($user['name'] ?? 'Admin') ?></span></div>
            </div>
        </div>

        <!-- 1. Tarjetas de Indicadores Ejecutivos (KPI Cards) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
            <div class="p-5 rounded-2xl bg-stone-900 text-white shadow-sm">
                <span class="text-[10px] uppercase tracking-wider text-amber-400 font-bold block mb-1">
                    Ventas Netas Totales
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold">$<?= number_format($summary['total_sales'], 2) ?></p>
                <span class="text-[10px] text-stone-400 mt-1 block">MXN acumulado</span>
            </div>

            <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <span class="text-[10px] uppercase tracking-wider text-stone-500 font-bold block mb-1">
                    Órdenes Liquidadas
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold text-stone-900"><?= $summary['total_orders'] ?></p>
                <span class="text-[10px] text-emerald-700 font-semibold mt-1 block">100% cobro confirmado</span>
            </div>

            <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <span class="text-[10px] uppercase tracking-wider text-stone-500 font-bold block mb-1">
                    Ticket Promedio
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">$<?= number_format($summary['avg_ticket'], 2) ?></p>
                <span class="text-[10px] text-stone-400 mt-1 block">Por cliente / orden</span>
            </div>

            <div class="p-5 rounded-2xl bg-stone-50 border border-stone-200">
                <span class="text-[10px] uppercase tracking-wider text-stone-500 font-bold block mb-1">
                    Prendas Entregadas
                </span>
                <p class="font-serif text-2xl sm:text-3xl font-bold text-stone-900"><?= $summary['total_items_sold'] ?></p>
                <span class="text-[10px] text-stone-400 mt-1 block">Unidades físicas</span>
            </div>
        </div>

        <!-- 2. Gráficos de Distribución (Categorías & Pasarelas) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
            
            <!-- Ventas por Categoría -->
            <div class="p-6 rounded-2xl border border-stone-200 bg-stone-50/50 space-y-4">
                <h3 class="font-serif font-bold text-stone-900 text-sm flex items-center justify-between">
                    <span>Participación por Categoría</span>
                    <span class="text-xs font-normal text-stone-500">% sobre ventas</span>
                </h3>

                <div class="space-y-3.5">
                    <?php if (empty($sales_by_category)): ?>
                        <p class="text-xs text-stone-400">Sin ventas en este rango de fechas.</p>
                    <?php else: ?>
                        <?php foreach ($sales_by_category as $cat): ?>
                            <div>
                                <div class="flex justify-between text-xs font-medium mb-1">
                                    <span class="text-stone-800"><?= htmlspecialchars($cat['category_name']) ?></span>
                                    <span class="font-bold text-stone-900">$<?= number_format($cat['total_revenue'], 2) ?> (<?= $cat['percentage'] ?>%)</span>
                                </div>
                                <div class="w-full h-2.5 bg-stone-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-stone-900 rounded-full transition-all" style="width: <?= $cat['percentage'] ?>%"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Métodos de Pago y Pasarelas -->
            <div class="p-6 rounded-2xl border border-stone-200 bg-stone-50/50 space-y-4">
                <h3 class="font-serif font-bold text-stone-900 text-sm">
                    Desglose por Método & Pasarela de Pago
                </h3>

                <div class="space-y-3">
                    <?php foreach ($payment_methods as $pm): ?>
                        <div class="flex items-center justify-between p-3 bg-white rounded-xl border border-stone-200 text-xs">
                            <div class="flex items-center gap-2.5">
                                <i class="fa-regular fa-credit-card text-stone-500"></i>
                                <div>
                                    <strong class="font-bold text-stone-900"><?= htmlspecialchars($pm['payment_method']) ?></strong>
                                    <span class="text-[10px] text-stone-400 block"><?= $pm['transaction_count'] ?> transacciones</span>
                                </div>
                            </div>
                            <span class="font-serif font-bold text-stone-900 text-sm">$<?= number_format($pm['total_processed'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div>

        <!-- 3. Top Prendas Más Vendidas -->
        <div class="space-y-4">
            <h3 class="font-serif font-bold text-stone-900 text-base">
                Top Prendas con Mayor Facturación
            </h3>

            <div class="border border-stone-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-6">Ranking</th>
                            <th class="py-3 px-4">Prenda de Ropa</th>
                            <th class="py-3 px-4">Categoría</th>
                            <th class="py-3 px-4 text-center">Unidades</th>
                            <th class="py-3 px-6 text-right">Monto Recaudado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-700">
                        <?php $rank = 1; foreach ($top_products as $prod): ?>
                            <tr class="hover:bg-stone-50/50">
                                <td class="py-3 px-6">
                                    <span class="w-6 h-6 rounded-full inline-flex items-center justify-center font-bold text-xs <?= $rank <= 3 ? 'bg-amber-100 text-amber-900' : 'bg-stone-100 text-stone-600' ?>">
                                        #<?= $rank++ ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-bold text-stone-900">
                                    <?= htmlspecialchars($prod['product_name']) ?>
                                </td>
                                <td class="py-3 px-4 text-stone-500">
                                    <?= htmlspecialchars($prod['category_name']) ?>
                                </td>
                                <td class="py-3 px-4 text-center font-bold">
                                    <?= $prod['total_quantity'] ?> uds
                                </td>
                                <td class="py-3 px-6 text-right font-serif font-bold text-stone-900">
                                    $<?= number_format($prod['total_revenue'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Registro de Transacciones Recientes -->
        <div class="space-y-4">
            <h3 class="font-serif font-bold text-stone-900 text-base">
                Detalle Cronológico de Pedidos
            </h3>

            <div class="border border-stone-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-6">Pedido</th>
                            <th class="py-3 px-4">Fecha / Hora</th>
                            <th class="py-3 px-4">Cliente</th>
                            <th class="py-3 px-4 text-center">Ítems</th>
                            <th class="py-3 px-6 text-right">Total Cobrado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-700">
                        <?php foreach ($recent_orders as $ord): ?>
                            <tr>
                                <td class="py-3 px-6 font-mono font-bold text-stone-900">
                                    <?= htmlspecialchars($ord['order_number']) ?>
                                </td>
                                <td class="py-3 px-4 text-stone-500">
                                    <?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?>
                                </td>
                                <td class="py-3 px-4 font-medium text-stone-900">
                                    <?= htmlspecialchars($ord['customer_name']) ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?= $ord['item_count'] ?> piezas
                                </td>
                                <td class="py-3 px-6 text-right font-serif font-bold text-stone-900">
                                    $<?= number_format($ord['total_amount'], 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. Pie de Firma y Certificación de Auditoría -->
        <div class="pt-12 border-t-2 border-stone-200 grid grid-cols-2 gap-12 text-xs text-stone-500">
            <div class="text-left space-y-1">
                <p class="font-bold text-stone-900">AURA Atelier E-Commerce System</p>
                <p>Base de datos: PostgreSQL 16+ · Arquitectura Hexagonal</p>
                <p class="text-[10px] text-stone-400">Documento confidencial para uso exclusivo de la gerencia general y el departamento fiscal.</p>
            </div>

            <div class="text-right space-y-12">
                <div class="inline-block border-t border-stone-400 pt-1 text-center min-w-[200px]">
                    <span class="block font-bold text-stone-900"><?= htmlspecialchars($user['name'] ?? 'Administrador') ?></span>
                    <span class="text-[10px] uppercase text-stone-500">Dirección General & Auditoría</span>
                </div>
            </div>
        </div>

    </div>

</div>

<?php if (isset($_GET['print'])): ?>
</body>
</html>
<?php endif; ?>
