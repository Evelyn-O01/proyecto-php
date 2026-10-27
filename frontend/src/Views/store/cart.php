<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between pb-6 border-b border-stone-200 mb-8">
        <div>
            <h1 class="font-serif text-3xl font-bold text-stone-900">Bolsa de Compras</h1>
            <p class="text-xs text-stone-500 mt-1">Tienes <strong class="text-stone-900"><?= $itemCount ?></strong> prendas en tu pedido actual</p>
        </div>
        <a href="/catalog" class="text-xs font-semibold text-stone-700 hover:text-stone-900 flex items-center gap-1.5">
            <i class="fa-solid fa-arrow-left text-[10px]"></i> Seguir Explorando
        </a>
    </div>

    <?php if (empty($items)): ?>
        <div class="bg-white rounded-3xl border border-stone-200 p-16 text-center my-6 max-w-xl mx-auto shadow-sm">
            <div class="w-20 h-20 rounded-full bg-stone-100 text-stone-400 flex items-center justify-center mx-auto mb-5 text-3xl">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <h2 class="font-serif text-2xl font-bold text-stone-900 mb-2">Tu bolsa está vacía</h2>
            <p class="text-xs text-stone-500 mb-8 max-w-sm mx-auto leading-relaxed">
                Añade piezas de nuestras colecciones exclusivas para comenzar tu experiencia de compra.
            </p>
            <a href="/catalog" class="inline-block px-8 py-3.5 bg-stone-900 text-white text-xs uppercase tracking-widest font-bold rounded-xl hover:bg-stone-800 shadow-md">
                Explorar Catálogo
            </a>
        </div>
    <?php else: ?>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- Lista de Artículos (8 cols) -->
            <div class="lg:col-span-8 space-y-4">
                
                <!-- Barra de Envío Gratuito -->
                <?php $remainingForFreeShipping = max(0, 999.00 - $subtotal); ?>
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-amber-900 text-xs flex items-center gap-3">
                    <i class="fa-solid fa-truck text-amber-700 text-base"></i>
                    <?php if ($remainingForFreeShipping > 0): ?>
                        <span>¡Agrega <strong class="font-bold">$<?= number_format($remainingForFreeShipping, 2) ?> MXN</strong> más a tu bolsa para obtener <strong>Envío Express Gratis</strong>!</span>
                    <?php else: ?>
                        <span class="font-semibold text-emerald-800">🎉 ¡Felicidades! Calificas para <strong>Envío Express Gratuito</strong> en esta orden.</span>
                    <?php endif; ?>
                </div>

                <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
                    <div class="divide-y divide-stone-100">
                        <?php foreach ($items as $item): ?>
                            <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 hover:bg-stone-50/50 transition-colors">
                                
                                <div class="flex items-center gap-4">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                                         class="w-20 h-24 object-cover rounded-xl border border-stone-200 bg-stone-100 flex-shrink-0">
                                    
                                    <div>
                                        <h3 class="font-serif font-bold text-stone-900 text-base mb-1">
                                            <a href="/product/<?= htmlspecialchars($item['slug']) ?>" class="hover:underline">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                        </h3>
                                        <div class="flex items-center gap-2 text-xs text-stone-500 mb-2">
                                            <span class="px-2 py-0.5 rounded bg-stone-100 font-semibold text-stone-700">Talla: <?= htmlspecialchars($item['size']) ?></span>
                                            <span>Color: <?= htmlspecialchars($item['color']) ?></span>
                                        </div>
                                        <span class="text-xs font-bold text-stone-900">
                                            $<?= number_format($item['unit_price'], 2) ?> MXN
                                        </span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end w-full sm:w-auto gap-8">
                                    <!-- Controles de Cantidad -->
                                    <form action="/cart/update" method="POST" class="flex items-center border border-stone-200 rounded-xl overflow-hidden bg-white">
                                        <input type="hidden" name="variant_id" value="<?= htmlspecialchars($item['variant_id']) ?>">
                                        <button type="submit" name="quantity" value="<?= $item['quantity'] - 1 ?>" class="px-3 py-1.5 text-stone-600 hover:bg-stone-100 font-bold text-sm">-</button>
                                        <span class="px-3 text-xs font-bold"><?= $item['quantity'] ?></span>
                                        <button type="submit" name="quantity" value="<?= $item['quantity'] + 1 ?>" class="px-3 py-1.5 text-stone-600 hover:bg-stone-100 font-bold text-sm">+</button>
                                    </form>

                                    <!-- Subtotal de Línea -->
                                    <div class="text-right min-w-[90px]">
                                        <span class="block text-[10px] text-stone-400 uppercase font-semibold">Subtotal</span>
                                        <span class="font-serif text-base font-bold text-stone-900">
                                            $<?= number_format($item['subtotal'], 2) ?>
                                        </span>
                                    </div>

                                    <!-- Eliminar -->
                                    <a href="/cart/remove/<?= htmlspecialchars($item['variant_id']) ?>" 
                                       class="text-stone-400 hover:text-rose-600 p-2 text-sm transition-colors" title="Retirar prenda">
                                        <i class="fa-regular fa-trash-can"></i>
                                    </a>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- Resumen de Compra (4 cols) -->
            <div class="lg:col-span-4">
                <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm sticky top-28 space-y-6">
                    <h2 class="font-serif text-xl font-bold text-stone-900 pb-4 border-b border-stone-100">
                        Resumen del Pedido
                    </h2>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-stone-600">
                            <span>Subtotal (<?= $itemCount ?> prendas)</span>
                            <span class="font-semibold text-stone-900">$<?= number_format($subtotal, 2) ?> MXN</span>
                        </div>
                        <div class="flex justify-between text-stone-600">
                            <span>Impuestos estimados (16% IVA)</span>
                            <span class="font-semibold text-stone-900">$<?= number_format($tax, 2) ?> MXN</span>
                        </div>
                        <div class="flex justify-between text-stone-600">
                            <span>Costo de Envío</span>
                            <?php if ($shipping === 0.0): ?>
                                <span class="font-bold text-emerald-700 uppercase">Gratis</span>
                            <?php else: ?>
                                <span class="font-semibold text-stone-900">$<?= number_format($shipping, 2) ?> MXN</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-200 flex justify-between items-baseline">
                        <div>
                            <span class="block text-xs uppercase tracking-wider font-bold text-stone-900">Total a Pagar</span>
                            <span class="text-[10px] text-stone-400">Incluye todos los impuestos</span>
                        </div>
                        <span class="font-serif text-2xl font-bold text-stone-900">
                            $<?= number_format($total, 2) ?> <span class="text-xs font-normal text-stone-500">MXN</span>
                        </span>
                    </div>

                    <a href="/checkout" 
                       class="w-full py-4 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs uppercase tracking-widest font-bold flex items-center justify-center gap-2 shadow-lg transition-all">
                        <i class="fa-solid fa-lock text-[11px]"></i>
                        <span>Continuar al Pago Seguro</span>
                    </a>

                    <div class="text-center pt-2">
                        <p class="text-[11px] text-stone-400">
                            <i class="fa-solid fa-shield-halved text-stone-500 mr-1"></i>
                            Pago 100% encriptado y protegido con pasarela certificada
                        </p>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>
</div>
