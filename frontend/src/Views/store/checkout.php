<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-8">
        <h1 class="font-serif text-3xl font-bold text-stone-900">Finalizar Compra Segura</h1>
        <p class="text-xs text-stone-500 mt-1">Completa los datos de envío y procesa tu pago mediante nuestra pasarela encriptada</p>
    </div>

    <form action="/checkout/process" method="POST" id="checkoutForm">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">

            <!-- Formularios de Envío y Pago (7 cols) -->
            <div class="lg:col-span-7 space-y-8">

                <!-- 1. Datos de Contacto y Envío -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-sm space-y-5">
                    <div class="flex items-center gap-3 pb-4 border-b border-stone-100">
                        <span class="w-7 h-7 rounded-full bg-stone-900 text-white text-xs font-bold flex items-center justify-center">1</span>
                        <h2 class="font-serif text-lg font-bold text-stone-900">Dirección de Entrega</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-stone-700 mb-1.5">Nombre Completo del Destinatario *</label>
                            <input type="text" name="customer_name" required value="<?= htmlspecialchars($user['name'] ?? 'Mariana Gómez') ?>"
                                   class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        </div>

                        <div>
                            <label class="block font-semibold text-stone-700 mb-1.5">Correo Electrónico *</label>
                            <input type="email" name="customer_email" required value="<?= htmlspecialchars($user['email'] ?? 'mariana@example.com') ?>"
                                   class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        </div>

                        <div>
                            <label class="block font-semibold text-stone-700 mb-1.5">Teléfono Móvil *</label>
                            <input type="tel" name="customer_phone" required value="<?= htmlspecialchars($user['phone'] ?? '+52 55 3344 5566') ?>"
                                   class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block font-semibold text-stone-700 mb-1.5">Calle, Número Exterior e Interior *</label>
                            <input type="text" name="street" required value="Paseo de la Reforma 450, Piso 12"
                                   class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        </div>

                        <div>
                            <label class="block font-semibold text-stone-700 mb-1.5">Ciudad *</label>
                            <input type="text" name="city" required value="Ciudad de México"
                                   class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        </div>

                        <div>
                            <label class="block font-semibold text-stone-700 mb-1.5">Estado / Provincia *</label>
                            <input type="text" name="state" required value="CDMX"
                                   class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        </div>

                        <div>
                            <label class="block font-semibold text-stone-700 mb-1.5">Código Postal *</label>
                            <input type="text" name="zip" required value="06600"
                                   class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        </div>
                    </div>
                </div>

                <!-- 2. Pasarela de Pago Integrada -->
                <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-full bg-stone-900 text-white text-xs font-bold flex items-center justify-center">2</span>
                            <h2 class="font-serif text-lg font-bold text-stone-900">Pasarela de Pago</h2>
                        </div>
                        <div class="flex items-center gap-2 text-stone-400 text-xl">
                            <i class="fa-brands fa-cc-visa text-blue-900"></i>
                            <i class="fa-brands fa-cc-mastercard text-rose-600"></i>
                            <i class="fa-brands fa-cc-amex text-cyan-700"></i>
                        </div>
                    </div>

                    <!-- Métodos de Pasarela -->
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-4 rounded-2xl border-2 border-stone-900 bg-stone-50/50 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="gateway_type" value="simulated" checked class="text-stone-900 focus:ring-0">
                                <div>
                                    <span class="block text-xs font-bold text-stone-900">Tarjeta de Crédito / Débito (Pasarela Encriptada)</span>
                                    <span class="block text-[11px] text-stone-500">Procesamiento seguro con autorización bancaria en tiempo real</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-lock text-stone-700 text-sm"></i>
                        </label>
                    </div>

                    <!-- Campos de Tarjeta -->
                    <div class="bg-stone-50 p-5 rounded-2xl border border-stone-200 space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-stone-700 mb-1.5">Número de Tarjeta *</label>
                            <div class="relative">
                                <input type="text" name="card_number" id="cardNumberInput" required placeholder="4242 4242 4242 4242"
                                       value="4242424242424242" maxlength="19"
                                       class="w-full p-3 pl-11 bg-white border border-stone-200 rounded-xl font-mono text-sm tracking-wider focus:outline-none focus:border-stone-900">
                                <i class="fa-regular fa-credit-card absolute left-3.5 top-3.5 text-stone-400 text-base"></i>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-stone-700 mb-1.5">Nombre del Titular de la Tarjeta *</label>
                            <input type="text" name="card_holder" required value="MARIANA GOMEZ" placeholder="Tal como figura en el plástico"
                                   class="w-full p-3 bg-white border border-stone-200 rounded-xl uppercase tracking-wider focus:outline-none focus:border-stone-900">
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block font-semibold text-stone-700 mb-1.5">Mes Exp. *</label>
                                <select name="exp_month" class="w-full p-3 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                                    <?php for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?= sprintf('%02d', $m) ?>" <?= $m === 12 ? 'selected' : '' ?>><?= sprintf('%02d', $m) ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-stone-700 mb-1.5">Año Exp. *</label>
                                <select name="exp_year" class="w-full p-3 bg-white border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                                    <?php for ($y = 2026; $y <= 2035; $y++): ?>
                                        <option value="<?= $y ?>" <?= $y === 2028 ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-stone-700 mb-1.5">CVV / CVC *</label>
                                <input type="password" name="cvv" required maxlength="4" value="123" placeholder="123"
                                       class="w-full p-3 bg-white border border-stone-200 rounded-xl font-mono text-center tracking-widest focus:outline-none focus:border-stone-900">
                            </div>
                        </div>

                        <!-- Ayuda de Tarjetas de Prueba -->
                        <div class="p-3 bg-amber-50/80 rounded-xl border border-amber-200 text-[11px] text-amber-900">
                            <strong class="font-bold block mb-1">💡 Modo Sandbox / Demostración:</strong>
                            <ul class="list-disc list-inside space-y-0.5">
                                <li>Tarjeta Aprobada Visa: <code class="bg-white px-1.5 py-0.5 rounded font-mono font-bold">4242 4242 4242 4242</code></li>
                                <li>Tarjeta Aprobada Mastercard: <code class="bg-white px-1.5 py-0.5 rounded font-mono font-bold">5555 5555 5555 5555</code></li>
                                <li>Tarjeta Declinada para pruebas de error: <code class="bg-white px-1.5 py-0.5 rounded font-mono font-bold">4000 0000 0000 0000</code></li>
                            </ul>
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-4 bg-stone-900 hover:bg-stone-800 text-white text-xs uppercase tracking-widest font-bold rounded-2xl flex items-center justify-center gap-2 shadow-xl hover:shadow-2xl transition-all">
                        <i class="fa-solid fa-lock"></i>
                        <span>Confirmar y Pagar $<?= number_format($total, 2) ?> MXN</span>
                    </button>
                </div>

            </div>

            <!-- Resumen Lateral de la Orden (5 cols) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-3xl border border-stone-200 p-6 sm:p-8 shadow-sm sticky top-28 space-y-6">
                    <h2 class="font-serif text-xl font-bold text-stone-900 pb-4 border-b border-stone-100">
                        Detalle de tu Pedido
                    </h2>

                    <!-- Lista de ítems compacta -->
                    <div class="divide-y divide-stone-100 max-h-72 overflow-y-auto pr-1">
                        <?php foreach ($items as $item): ?>
                            <div class="py-3 flex items-center justify-between gap-3 text-xs">
                                <div class="flex items-center gap-3">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="" class="w-12 h-14 object-cover rounded-lg border bg-stone-50">
                                    <div>
                                        <strong class="text-stone-900 font-bold block line-clamp-1"><?= htmlspecialchars($item['name']) ?></strong>
                                        <span class="text-stone-500 text-[10px]">Talla <?= htmlspecialchars($item['size']) ?> · Cantidad: <?= $item['quantity'] ?></span>
                                    </div>
                                </div>
                                <span class="font-semibold text-stone-900">$<?= number_format($item['subtotal'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pt-4 border-t border-stone-200 space-y-2.5 text-xs">
                        <div class="flex justify-between text-stone-600">
                            <span>Subtotal</span>
                            <span class="font-semibold text-stone-900">$<?= number_format($subtotal, 2) ?> MXN</span>
                        </div>
                        <div class="flex justify-between text-stone-600">
                            <span>IVA (16% desglose legal)</span>
                            <span class="font-semibold text-stone-900">$<?= number_format($tax, 2) ?> MXN</span>
                        </div>
                        <div class="flex justify-between text-stone-600">
                            <span>Costo de Envío Express</span>
                            <span class="font-bold <?= $shipping === 0.0 ? 'text-emerald-700 uppercase' : 'text-stone-900' ?>">
                                <?= $shipping === 0.0 ? 'Gratis' : '$' . number_format($shipping, 2) . ' MXN' ?>
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-stone-200 flex justify-between items-baseline">
                        <span class="text-xs uppercase tracking-wider font-bold text-stone-900">Total a Debitar</span>
                        <span class="font-serif text-2xl font-bold text-stone-900">
                            $<?= number_format($total, 2) ?> <span class="text-xs font-normal text-stone-500">MXN</span>
                        </span>
                    </div>

                    <div class="rounded-xl bg-stone-50 p-4 border border-stone-200 text-[11px] text-stone-500 space-y-1">
                        <p class="flex items-center gap-2 text-stone-800 font-semibold">
                            <i class="fa-solid fa-certificate text-amber-600"></i> Compra Garantizada AURA
                        </p>
                        <p>Recibirás un correo con la confirmación de la orden y tu número de guía de paquetería.</p>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
