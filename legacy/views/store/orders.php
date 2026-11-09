<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between pb-6 border-b border-stone-200 mb-8">
        <div>
            <h1 class="font-serif text-3xl font-bold text-stone-900">Historial de Mis Pedidos</h1>
            <p class="text-xs text-stone-500 mt-1">Consulta tus compras anteriores y el estado de entrega de tus prendas</p>
        </div>
        <a href="/catalog" class="text-xs font-semibold text-stone-700 hover:text-stone-900 flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Seguir Comprando
        </a>
    </div>

    <?php if (empty($orders)): ?>
        <div class="bg-white rounded-3xl border border-stone-200 p-16 text-center my-6 max-w-md mx-auto shadow-sm">
            <div class="w-16 h-16 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-4 text-2xl">
                <i class="fa-solid fa-box-open"></i>
            </div>
            <h2 class="font-serif text-xl font-bold text-stone-900 mb-2">No tienes pedidos registrados</h2>
            <p class="text-xs text-stone-500 mb-6">
                Tus pedidos aparecerán aquí una vez que completes tus compras en la tienda.
            </p>
            <a href="/catalog" class="inline-block px-6 py-3 bg-stone-900 text-white text-xs uppercase tracking-widest font-bold rounded-xl hover:bg-stone-800">
                Ir a la Colección
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($orders as $o): ?>
                <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden shadow-xs hover:shadow-md transition-shadow">
                    
                    <!-- Cabecera de la Orden -->
                    <div class="bg-stone-50/70 p-5 border-b border-stone-200/80 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
                        <div class="flex flex-wrap items-center gap-6">
                            <div>
                                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Número de Pedido</span>
                                <span class="font-mono font-bold text-stone-900 text-sm"><?= htmlspecialchars($o->orderNumber) ?></span>
                            </div>
                            <div>
                                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Fecha de Compra</span>
                                <span class="font-semibold text-stone-700"><?= date('d/m/Y H:i', strtotime($o->createdAt)) ?></span>
                            </div>
                            <div>
                                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Estado</span>
                                <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded text-[10px] font-bold <?= $o->status->badgeClass() ?>">
                                    <?= $o->status->label() ?>
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 sm:justify-end">
                            <div class="text-right">
                                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Total Pagado</span>
                                <span class="font-serif text-base font-bold text-stone-900">$<?= number_format($o->totalAmount, 2) ?> MXN</span>
                            </div>
                            <a href="/order/success/<?= htmlspecialchars($o->orderNumber) ?>" 
                               class="px-3.5 py-2 bg-white border border-stone-300 hover:border-stone-900 rounded-xl text-xs font-semibold text-stone-800 transition-colors">
                                Ver Recibo
                            </a>
                        </div>
                    </div>

                    <!-- Prendas del Pedido -->
                    <div class="p-6 divide-y divide-stone-100 text-xs">
                        <?php foreach ($o->items as $item): ?>
                            <div class="py-3 first:pt-0 last:pb-0 flex items-center justify-between">
                                <div>
                                    <strong class="font-bold text-stone-900 text-sm block"><?= htmlspecialchars($item->productName) ?></strong>
                                    <span class="text-stone-500">Talla: <?= htmlspecialchars($item->size) ?> · Color: <?= htmlspecialchars($item->color) ?> · Cantidad: <?= $item->quantity ?></span>
                                </div>
                                <span class="font-semibold text-stone-900">$<?= number_format($item->subtotal, 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Dirección de Envío -->
                    <div class="bg-stone-50 px-6 py-3 border-t border-stone-100 text-[11px] text-stone-500 flex items-center justify-between">
                        <span>
                            <i class="fa-solid fa-location-dot text-stone-400 mr-1"></i>
                            Destino: <?= htmlspecialchars($o->shippingAddress['recipient'] ?? '') ?>, <?= htmlspecialchars($o->shippingAddress['street'] ?? '') ?>, <?= htmlspecialchars($o->shippingAddress['city'] ?? '') ?>
                        </span>
                        <span class="text-stone-400 font-mono">
                            <?= htmlspecialchars($o->payment->paymentMethod ?? 'Pago con Tarjeta') ?>
                        </span>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
