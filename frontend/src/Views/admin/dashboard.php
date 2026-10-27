<div class="space-y-8">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-stone-200">
        <div>
            <h1 class="font-serif text-2xl sm:text-3xl font-bold text-stone-900">Panel de Administración General</h1>
            <p class="text-xs text-stone-500 mt-1">
                Supervisión global de la plataforma, roles, catálogo y analítica comercial.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="/admin/reports/sales" class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-stone-950 font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition-colors">
                <i class="fa-solid fa-chart-line"></i> Reporte de Ventas
            </a>
            <a href="/admin/reports/inventory" class="px-4 py-2.5 bg-stone-900 hover:bg-stone-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition-colors">
                <i class="fa-solid fa-boxes-stacked"></i> Reporte de Inventario
            </a>
        </div>
    </div>

    <!-- Indicadores Principales -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Ingresos Facturados</span>
                <i class="fa-solid fa-hand-holding-dollar text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900">$<?= number_format($totalRevenue, 2) ?></p>
            <span class="text-[11px] text-emerald-600 font-semibold">MXN liquidados</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Pedidos Totales</span>
                <i class="fa-solid fa-receipt text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900"><?= $ordersCount ?></p>
            <span class="text-[11px] text-stone-400">Transacciones en tienda</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Usuarios Registrados</span>
                <i class="fa-solid fa-users text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900"><?= $usersCount ?></p>
            <span class="text-[11px] text-stone-400">Admins, Vendedores y Clientes</span>
        </div>

        <div class="bg-white p-5 rounded-2xl border border-stone-200 shadow-xs">
            <div class="flex items-center justify-between text-stone-400 mb-2">
                <span class="text-xs font-semibold uppercase tracking-wider">Prendas en Catálogo</span>
                <i class="fa-solid fa-shirt text-stone-600"></i>
            </div>
            <p class="font-serif text-2xl font-bold text-stone-900"><?= $productsCount ?></p>
            <span class="text-[11px] text-stone-400">En <?= $categoriesCount ?> categorías</span>
        </div>
    </div>

    <!-- Acceso Directo a los Reportes Diseñados -->
    <div class="bg-gradient-to-r from-stone-900 to-stone-800 rounded-3xl p-6 sm:p-8 text-white shadow-md">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-xl">
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-400 text-stone-950">
                    Módulo de Auditoría & Reportes
                </span>
                <h2 class="font-serif text-2xl font-bold">Generador de Reportes Ejecutivos Diseñados</h2>
                <p class="text-xs text-stone-300 leading-relaxed font-light">
                    Visualiza y exporta informes contables y operativos con formato editorial, desglose por categorías, cálculo de márgenes, ranking de productos y formato optimizado para impresión A4/PDF.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="/admin/reports/sales" class="px-5 py-3 bg-white text-stone-900 text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-stone-100 transition-all">
                    Reporte de Ventas
                </a>
                <a href="/admin/reports/inventory" class="px-5 py-3 bg-stone-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-stone-600 transition-all">
                    Stock Crítico
                </a>
                <a href="/admin/reports/sellers" class="px-5 py-3 bg-stone-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl hover:bg-stone-600 transition-all">
                    Por Vendedor
                </a>
            </div>
        </div>
    </div>

    <!-- Pedidos Recientes de la Plataforma -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-serif text-lg font-bold text-stone-900">Transacciones Recientes de la Tienda</h2>
            <a href="/admin/reports/sales" class="text-xs text-stone-900 font-semibold hover:underline">Ver reporte completo &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-6">Pedido</th>
                        <th class="py-3 px-4">Fecha</th>
                        <th class="py-3 px-4">Cliente</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4">Pasarela</th>
                        <th class="py-3 px-6 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    <?php foreach ($recentOrders as $o): 
                        $oNumber = is_array($o) ? ($o['order_number'] ?? '') : $o->orderNumber;
                        $oDate = is_array($o) ? ($o['created_at'] ?? '') : $o->createdAt;
                        $oCustName = is_array($o) ? ($o['customer_name'] ?? '') : $o->customerName;
                        $oCustEmail = is_array($o) ? ($o['customer_email'] ?? '') : $o->customerEmail;
                        $oBadge = is_array($o) ? ($o['status_badge'] ?? 'bg-emerald-100 text-emerald-800') : (isset($o->status) ? $o->status->badgeClass() : 'bg-emerald-100 text-emerald-800');
                        $oLabel = is_array($o) ? ($o['status_label'] ?? 'Completado') : (isset($o->status) ? $o->status->label() : 'Completado');
                        $oGateway = is_array($o) ? ($o['payment']['gateway'] ?? 'Stripe / Tarjeta') : ($o->payment->gateway ?? 'Stripe / Tarjeta');
                        $oLast4 = is_array($o) ? ($o['payment']['card_last4'] ?? '4242') : ($o->payment->cardLast4 ?? '4242');
                        $oTotal = is_array($o) ? ($o['total_amount'] ?? 0) : $o->totalAmount;
                    ?>
                        <tr class="hover:bg-stone-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-stone-900">
                                <?= htmlspecialchars($oNumber) ?>
                            </td>
                            <td class="py-4 px-4 text-stone-500">
                                <?= !empty($oDate) ? date('d/m/Y H:i', strtotime($oDate)) : '-' ?>
                            </td>
                            <td class="py-4 px-4">
                                <strong class="text-stone-900 block"><?= htmlspecialchars($oCustName) ?></strong>
                                <span class="text-[11px] text-stone-400"><?= htmlspecialchars($oCustEmail) ?></span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $oBadge ?>">
                                    <?= htmlspecialchars($oLabel) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-medium text-stone-800"><?= htmlspecialchars($oGateway) ?></span>
                                <span class="text-stone-400 block text-[10px]">•••• <?= htmlspecialchars($oLast4) ?></span>
                            </td>
                            <td class="py-4 px-6 text-right font-serif text-sm font-bold text-stone-900">
                                $<?= number_format((float)$oTotal, 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
