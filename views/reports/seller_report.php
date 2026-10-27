<?php if (isset($_GET['print'])): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Vendedores - AURA Atelier</title>
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

    <div class="no-print bg-white p-5 rounded-3xl border border-stone-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="/admin/reports/sellers" method="GET" class="flex flex-wrap items-center gap-3 text-xs w-full md:w-auto">
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

            <div class="pt-4">
                <button type="submit" class="px-4 py-2 bg-stone-900 hover:bg-stone-800 text-white rounded-lg font-bold">
                    Filtrar
                </button>
            </div>
        </form>

        <button onclick="window.print()" class="px-4 py-2.5 bg-stone-900 hover:bg-stone-800 text-white font-bold rounded-xl text-xs flex items-center gap-2 shadow-xs transition-colors">
            <i class="fa-solid fa-print"></i> Imprimir Reporte
        </button>
    </div>

    <!-- DOCUMENTO DE REPORTE DISEÑADO -->
    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-12 shadow-sm print:border-none print:shadow-none print:p-0 space-y-10">

        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-8 border-b-2 border-stone-900 gap-6">
            <div>
                <div class="flex items-center gap-3">
                    <span class="font-serif text-3xl font-bold tracking-tight text-stone-900">AURA</span>
                    <span class="h-6 w-px bg-stone-300"></span>
                    <span class="text-xs uppercase tracking-widest text-stone-500 font-semibold">Auditoría Marketplace</span>
                </div>
                <h1 class="font-serif text-2xl font-bold text-stone-900 mt-2">
                    Rendimiento y Facturación por Vendedor
                </h1>
                <p class="text-xs text-stone-500 mt-0.5">
                    Período auditado: <strong class="text-stone-900"><?= date('d/m/Y', strtotime($start_date)) ?></strong> al <strong class="text-stone-900"><?= date('d/m/Y', strtotime($end_date)) ?></strong>
                </p>
            </div>

            <div class="text-left sm:text-right text-xs text-stone-500 space-y-1">
                <div>Código: <span class="font-mono font-bold text-stone-900">RPT-VND-<?= date('Ymd') ?></span></div>
                <div>Emisión: <span class="font-medium text-stone-800"><?= $generated_at ?></span></div>
                <div>Volumen Marketplace: <span class="font-bold text-stone-900">$<?= number_format($total_revenue, 2) ?> MXN</span></div>
            </div>
        </div>

        <div class="border border-stone-200 rounded-2xl overflow-hidden">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-6">Vendedor / Boutique</th>
                        <th class="py-3 px-4">Correo de Contacto</th>
                        <th class="py-3 px-4 text-center">Prendas en Catálogo</th>
                        <th class="py-3 px-4 text-center">Órdenes Generadas</th>
                        <th class="py-3 px-4 text-center">Piezas Vendidas</th>
                        <th class="py-3 px-6 text-right">Venta Bruta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    <?php foreach ($sellers as $s): ?>
                        <tr class="hover:bg-stone-50/50">
                            <td class="py-4 px-6 font-bold text-stone-900 flex items-center gap-2.5">
                                <i class="fa-solid fa-store text-stone-400"></i>
                                <?= htmlspecialchars($s['seller_name']) ?>
                            </td>
                            <td class="py-4 px-4 font-mono text-[11px] text-stone-500">
                                <?= htmlspecialchars($s['seller_email']) ?>
                            </td>
                            <td class="py-4 px-4 text-center font-semibold">
                                <?= $s['total_products'] ?> modelos
                            </td>
                            <td class="py-4 px-4 text-center font-semibold">
                                <?= $s['total_orders'] ?> pedidos
                            </td>
                            <td class="py-4 px-4 text-center font-bold text-stone-900">
                                <?= $s['units_sold'] ?> uds
                            </td>
                            <td class="py-4 px-6 text-right font-serif text-sm font-bold text-stone-900">
                                $<?= number_format($s['total_revenue'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</div>

<?php if (isset($_GET['print'])): ?>
</body>
</html>
<?php endif; ?>
