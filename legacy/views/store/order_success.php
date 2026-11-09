<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-12 shadow-sm space-y-8">
        
        <!-- Cabecera de Confirmación -->
        <div class="text-center space-y-3 pb-8 border-b border-stone-100">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto text-3xl shadow-inner">
                <i class="fa-solid fa-check"></i>
            </div>
            <span class="text-xs uppercase tracking-widest text-emerald-800 font-bold bg-emerald-50 px-3 py-1 rounded-full inline-block">
                ¡Pago Aprobado y Transacción Exitosa!
            </span>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900">
                Gracias por tu compra, <?= htmlspecialchars(explode(' ', $order->customerName ?? 'Cliente')[0]) ?>
            </h1>
            <p class="text-xs text-stone-500 max-w-md mx-auto">
                Hemos enviado la confirmación y el comprobante fiscal digital a <strong class="text-stone-800"><?= htmlspecialchars($order->customerEmail ?? '') ?></strong>.
            </p>
        </div>

        <!-- Ficha de Datos del Pedido -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 p-6 rounded-2xl bg-stone-50 border border-stone-200 text-xs">
            <div>
                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Número de Pedido</span>
                <span class="font-mono font-bold text-stone-900 text-sm"><?= htmlspecialchars($order->orderNumber) ?></span>
            </div>
            <div>
                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Fecha & Hora</span>
                <span class="font-semibold text-stone-800"><?= date('d/m/Y H:i', strtotime($order->createdAt ?? 'now')) ?></span>
            </div>
            <div>
                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Estado</span>
                <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded text-[11px] font-bold <?= $order->status->badgeClass() ?>">
                    <?= $order->status->label() ?>
                </span>
            </div>
        </div>

        <!-- Desglose de Pago e Información de Entrega -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-xs">
            <!-- Envío -->
            <div class="p-6 rounded-2xl border border-stone-200 space-y-2">
                <h3 class="font-serif font-bold text-stone-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-stone-500"></i> Dirección de Entrega
                </h3>
                <p class="text-stone-800 font-semibold"><?= htmlspecialchars($order->shippingAddress['recipient'] ?? '') ?></p>
                <p class="text-stone-600"><?= htmlspecialchars($order->shippingAddress['street'] ?? '') ?></p>
                <p class="text-stone-600">
                    <?= htmlspecialchars($order->shippingAddress['city'] ?? '') ?>, 
                    <?= htmlspecialchars($order->shippingAddress['state'] ?? '') ?> · CP <?= htmlspecialchars($order->shippingAddress['zip'] ?? '') ?>
                </p>
                <p class="text-stone-500">Tel: <?= htmlspecialchars($order->shippingAddress['phone'] ?? '') ?></p>
            </div>

            <!-- Pasarela -->
            <div class="p-6 rounded-2xl border border-stone-200 space-y-2">
                <h3 class="font-serif font-bold text-stone-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-stone-500"></i> Pasarela de Pago
                </h3>
                <?php if ($order->payment): ?>
                    <p class="text-stone-800 font-semibold">
                        <?= htmlspecialchars($order->payment->paymentMethod ?: 'Tarjeta Bancaria') ?> 
                        (•••• <?= htmlspecialchars($order->payment->cardLast4 ?: '4242') ?>)
                    </p>
                    <p class="text-stone-500 font-mono text-[11px]">
                        Ref. Transacción: <?= htmlspecialchars($order->payment->transactionReference) ?>
                    </p>
                    <p class="text-emerald-700 font-medium">Autorización bancaria procesada vía <?= htmlspecialchars($order->payment->gateway) ?></p>
                <?php else: ?>
                    <p class="text-stone-600">Pago registrado en el sistema.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabla de Artículos Comprados -->
        <div>
            <h3 class="font-serif font-bold text-stone-900 text-base mb-4">Prendas Adquiridas</h3>
            <div class="border border-stone-200 rounded-2xl overflow-hidden divide-y divide-stone-100 text-xs">
                <?php foreach ($order->items as $item): ?>
                    <div class="p-4 flex items-center justify-between hover:bg-stone-50/50">
                        <div>
                            <strong class="font-bold text-stone-900 text-sm block"><?= htmlspecialchars($item->productName) ?></strong>
                            <span class="text-stone-500">Talla: <?= htmlspecialchars($item->size) ?> · Color: <?= htmlspecialchars($item->color) ?></span>
                        </div>
                        <div class="text-right">
                            <span class="text-stone-500 block"><?= $item->quantity ?> x $<?= number_format($item->unitPrice, 2) ?></span>
                            <span class="font-bold text-stone-900">$<?= number_format($item->subtotal, 2) ?> MXN</span>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="p-4 bg-stone-50 space-y-1.5 text-right font-medium">
                    <div class="flex justify-between text-stone-500">
                        <span>Costo de Envío:</span>
                        <span><?= $order->shippingCost === 0.0 ? 'Gratis' : '$' . number_format($order->shippingCost, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-stone-500">
                        <span>Impuestos (16% IVA):</span>
                        <span>$<?= number_format($order->taxAmount, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-stone-900 text-sm font-bold pt-2 border-t border-stone-200">
                        <span>Total Pagado:</span>
                        <span class="font-serif text-lg">$<?= number_format($order->totalAmount, 2) ?> MXN</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="pt-6 border-t border-stone-100 flex flex-col sm:flex-row items-center justify-between gap-4">
            <button onclick="window.print()" class="w-full sm:w-auto px-6 py-3 border border-stone-300 rounded-xl text-xs font-bold text-stone-700 hover:bg-stone-50 flex items-center justify-center gap-2">
                <i class="fa-solid fa-print"></i> Imprimir Recibo
            </button>

            <a href="/catalog" class="w-full sm:w-auto px-8 py-3 bg-stone-900 text-white rounded-xl text-xs uppercase tracking-widest font-bold hover:bg-stone-800 text-center shadow-md">
                Volver a la Tienda
            </a>
        </div>

    </div>

</div>
