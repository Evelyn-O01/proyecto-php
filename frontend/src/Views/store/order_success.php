<?php
    $oNumber = is_array($order) ? $order['order_number'] : $order->orderNumber;
    $oCustomer = is_array($order) ? ($order['customer_name'] ?? 'Cliente') : ($order->customerName ?? 'Cliente');
    $oEmail = is_array($order) ? ($order['customer_email'] ?? '') : ($order->customerEmail ?? '');
    $oDate = is_array($order) ? ($order['created_at'] ?? 'now') : ($order->createdAt ?? 'now');
    $oStatusLabel = is_array($order) ? ($order['status_label'] ?? 'Pagado') : $order->status->label();
    $oStatusBadge = is_array($order) ? ($order['status_badge'] ?? 'bg-emerald-100 text-emerald-800') : $order->status->badgeClass();
    $oAddress = is_array($order) ? $order['shipping_address'] : $order->shippingAddress;
    $oItems = is_array($order) ? $order['items'] : $order->items;
    $oPayment = is_array($order) ? ($order['payment'] ?? null) : $order->payment;
    $oTotal = is_array($order) ? $order['total_amount'] : $order->totalAmount;
    $oTax = is_array($order) ? $order['tax_amount'] : $order->taxAmount;
    $oShipping = is_array($order) ? $order['shipping_cost'] : $order->shippingCost;
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="bg-white rounded-3xl border border-stone-200 p-8 sm:p-12 shadow-sm space-y-8">
        
        <div class="text-center space-y-3 pb-8 border-b border-stone-100">
            <div class="w-20 h-20 bg-emerald-100 text-emerald-700 rounded-full flex items-center justify-center mx-auto text-3xl shadow-inner">
                <i class="fa-solid fa-check"></i>
            </div>
            <span class="text-xs uppercase tracking-widest text-emerald-800 font-bold bg-emerald-50 px-3 py-1 rounded-full inline-block">
                ¡Pago Aprobado y Transacción Exitosa!
            </span>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900">
                Gracias por tu compra, <?= htmlspecialchars(explode(' ', $oCustomer)[0]) ?>
            </h1>
            <p class="text-xs text-stone-500 max-w-md mx-auto">
                Hemos enviado la confirmación y el comprobante fiscal digital a <strong class="text-stone-800"><?= htmlspecialchars($oEmail) ?></strong>.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 p-6 rounded-2xl bg-stone-50 border border-stone-200 text-xs">
            <div>
                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Número de Pedido</span>
                <span class="font-mono font-bold text-stone-900 text-sm"><?= htmlspecialchars($oNumber) ?></span>
            </div>
            <div>
                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Fecha & Hora</span>
                <span class="font-semibold text-stone-800"><?= date('d/m/Y H:i', strtotime($oDate)) ?></span>
            </div>
            <div>
                <span class="text-stone-400 uppercase tracking-wider block font-semibold text-[10px]">Estado</span>
                <span class="inline-block mt-0.5 px-2.5 py-0.5 rounded text-[11px] font-bold <?= $oStatusBadge ?>">
                    <?= $oStatusLabel ?>
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 text-xs">
            <div class="p-6 rounded-2xl border border-stone-200 space-y-2">
                <h3 class="font-serif font-bold text-stone-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-location-dot text-stone-500"></i> Dirección de Entrega
                </h3>
                <p class="text-stone-800 font-semibold"><?= htmlspecialchars($oAddress['recipient'] ?? '') ?></p>
                <p class="text-stone-600"><?= htmlspecialchars($oAddress['street'] ?? '') ?></p>
                <p class="text-stone-600">
                    <?= htmlspecialchars($oAddress['city'] ?? '') ?>, 
                    <?= htmlspecialchars($oAddress['state'] ?? '') ?> · CP <?= htmlspecialchars($oAddress['zip'] ?? '') ?>
                </p>
                <p class="text-stone-500">Tel: <?= htmlspecialchars($oAddress['phone'] ?? '') ?></p>
            </div>

            <div class="p-6 rounded-2xl border border-stone-200 space-y-2">
                <h3 class="font-serif font-bold text-stone-900 text-sm flex items-center gap-2">
                    <i class="fa-solid fa-credit-card text-stone-500"></i> Pasarela de Pago
                </h3>
                <?php if ($oPayment): 
                    $pmMethod = is_array($oPayment) ? ($oPayment['payment_method'] ?? 'Tarjeta') : $oPayment->paymentMethod;
                    $pmLast4 = is_array($oPayment) ? ($oPayment['card_last4'] ?? '4242') : $oPayment->cardLast4;
                    $pmRef = is_array($oPayment) ? ($oPayment['transaction_reference'] ?? '') : $oPayment->transactionReference;
                    $pmGateway = is_array($oPayment) ? ($oPayment['gateway'] ?? 'SIMULATED') : $oPayment->gateway;
                ?>
                    <p class="text-stone-800 font-semibold">
                        <?= htmlspecialchars($pmMethod ?: 'Tarjeta Bancaria') ?> (•••• <?= htmlspecialchars($pmLast4 ?: '4242') ?>)
                    </p>
                    <p class="text-stone-500 font-mono text-[11px]">
                        Ref. Transacción: <?= htmlspecialchars($pmRef) ?>
                    </p>
                    <p class="text-emerald-700 font-medium">Autorización bancaria procesada vía <?= htmlspecialchars($pmGateway) ?></p>
                <?php else: ?>
                    <p class="text-stone-600">Pago registrado en el sistema.</p>
                <?php endif; ?>
            </div>
        </div>

        <div>
            <h3 class="font-serif font-bold text-stone-900 text-base mb-4">Prendas Adquiridas</h3>
            <div class="border border-stone-200 rounded-2xl overflow-hidden divide-y divide-stone-100 text-xs">
                <?php foreach ($oItems as $item): 
                    $iName = is_array($item) ? $item['product_name'] : $item->productName;
                    $iSize = is_array($item) ? $item['size'] : $item->size;
                    $iColor = is_array($item) ? $item['color'] : $item->color;
                    $iQty = is_array($item) ? $item['quantity'] : $item->quantity;
                    $iPrice = is_array($item) ? $item['unit_price'] : $item->unitPrice;
                    $iSub = is_array($item) ? $item['subtotal'] : $item->subtotal;
                ?>
                    <div class="p-4 flex items-center justify-between hover:bg-stone-50/50">
                        <div>
                            <strong class="font-bold text-stone-900 text-sm block"><?= htmlspecialchars($iName) ?></strong>
                            <span class="text-stone-500">Talla: <?= htmlspecialchars($iSize) ?> · Color: <?= htmlspecialchars($iColor) ?></span>
                        </div>
                        <div class="text-right">
                            <span class="text-stone-500 block"><?= $iQty ?> x $<?= number_format($iPrice, 2) ?></span>
                            <span class="font-bold text-stone-900">$<?= number_format($iSub, 2) ?> MXN</span>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="p-4 bg-stone-50 space-y-1.5 text-right font-medium">
                    <div class="flex justify-between text-stone-500">
                        <span>Costo de Envío:</span>
                        <span><?= (float)$oShipping === 0.0 ? 'Gratis' : '$' . number_format($oShipping, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-stone-500">
                        <span>Impuestos (16% IVA):</span>
                        <span>$<?= number_format($oTax, 2) ?></span>
                    </div>
                    <div class="flex justify-between text-stone-900 text-sm font-bold pt-2 border-t border-stone-200">
                        <span>Total Pagado:</span>
                        <span class="font-serif text-lg">$<?= number_format($oTotal, 2) ?> MXN</span>
                    </div>
                </div>
            </div>
        </div>

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
