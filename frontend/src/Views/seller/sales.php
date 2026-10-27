<div class="space-y-8">

    <div class="flex items-center justify-between pb-6 border-b border-stone-200">
        <div>
            <h1 class="font-serif text-2xl font-bold text-stone-900">Historial de Ventas y Pedidos</h1>
            <p class="text-xs text-stone-500 mt-1">Órdenes recibidas que contienen prendas de tu catálogo</p>
        </div>
        <a href="/seller" class="text-xs font-semibold text-stone-600 hover:text-stone-900">
            &larr; Volver al Panel
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-stone-200 shadow-xs overflow-hidden">
        <?php if (empty($orders)): ?>
            <div class="p-16 text-center text-xs text-stone-500">
                <i class="fa-solid fa-receipt text-3xl text-stone-300 mb-3 block"></i>
                <p>Aún no se registran compras para tus prendas.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-6">No. Pedido</th>
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4">Cliente</th>
                            <th class="py-3 px-4">Destino</th>
                            <th class="py-3 px-4">Estado</th>
                            <th class="py-3 px-4">Prendas</th>
                            <th class="py-3 px-6 text-right">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-stone-700">
                        <?php foreach ($orders as $o): 
                            $oNumber = is_array($o) ? $o['order_number'] : $o->orderNumber;
                            $oDate = is_array($o) ? $o['created_at'] : $o->createdAt;
                            $oCustName = is_array($o) ? $o['customer_name'] : $o->customerName;
                            $oCustEmail = is_array($o) ? $o['customer_email'] : $o->customerEmail;
                            $oAddress = is_array($o) ? ($o['shipping_address'] ?? []) : $o->shippingAddress;
                            $oBadge = is_array($o) ? ($o['status_badge'] ?? 'bg-emerald-100 text-emerald-800') : $o->status->badgeClass();
                            $oLabel = is_array($o) ? ($o['status_label'] ?? 'Pagado') : $o->status->label();
                            $oTotal = is_array($o) ? $o['total_amount'] : $o->totalAmount;
                            $oItems = is_array($o) ? ($o['items'] ?? []) : $o->items;
                        ?>
                            <tr class="hover:bg-stone-50/60 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-stone-900">
                                    <?= htmlspecialchars($oNumber) ?>
                                </td>
                                <td class="py-4 px-4 text-stone-500">
                                    <?= date('d/m/Y H:i', strtotime($oDate)) ?>
                                </td>
                                <td class="py-4 px-4">
                                    <strong class="text-stone-900 block"><?= htmlspecialchars($oCustName) ?></strong>
                                    <span class="text-[11px] text-stone-400"><?= htmlspecialchars($oCustEmail) ?></span>
                                </td>
                                <td class="py-4 px-4 text-stone-600">
                                    <?= htmlspecialchars($oAddress['city'] ?? '') ?>, <?= htmlspecialchars($oAddress['state'] ?? '') ?>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $oBadge ?>">
                                        <?= htmlspecialchars($oLabel) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <div class="space-y-1">
                                        <?php foreach ($oItems as $item): 
                                            $iQty = is_array($item) ? $item['quantity'] : $item->quantity;
                                            $iName = is_array($item) ? $item['product_name'] : $item->productName;
                                            $iSize = is_array($item) ? $item['size'] : $item->size;
                                        ?>
                                            <div class="text-[11px]">
                                                <strong><?= $iQty ?>x</strong> <?= htmlspecialchars($iName) ?> (<?= htmlspecialchars($iSize) ?>)
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right font-serif text-sm font-bold text-stone-900">
                                    $<?= number_format((float)$oTotal, 2) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>
