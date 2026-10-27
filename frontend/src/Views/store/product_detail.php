<?php
    $pId = is_array($product) ? $product['id'] : $product->id;
    $pName = is_array($product) ? $product['name'] : $product->name;
    $pSlug = is_array($product) ? $product['slug'] : $product->slug;
    $pDesc = is_array($product) ? $product['description'] : $product->description;
    $pBasePrice = is_array($product) ? $product['base_price'] : $product->basePrice;
    $pCatName = is_array($product) ? ($product['category_name'] ?? 'Moda') : ($product->categoryName ?? 'Moda');
    $pCatId = is_array($product) ? ($product['category_id'] ?? 1) : ($product->categoryId ?? 1);
    $pSeller = is_array($product) ? ($product['seller_name'] ?? 'Vendedor AURA') : ($product->sellerName ?? 'Vendedor AURA');
    $pPrimaryImg = is_array($product) ? ($product['primary_image'] ?? '') : $product->getPrimaryImage();
    $pVariants = is_array($product) ? ($product['variants'] ?? []) : $product->variants;
    $pImages = is_array($product) ? ($product['images'] ?? []) : $product->images;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <!-- Breadcrumb -->
    <nav class="flex items-center gap-2 text-xs text-stone-500 mb-8">
        <a href="/catalog" class="hover:text-stone-900">Catálogo</a>
        <span>/</span>
        <a href="/catalog?category_id=<?= $pCatId ?>" class="hover:text-stone-900"><?= htmlspecialchars($pCatName) ?></a>
        <span>/</span>
        <span class="text-stone-900 font-semibold truncate"><?= htmlspecialchars($pName) ?></span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">

        <!-- Galería de Imágenes (7 cols) -->
        <div class="lg:col-span-7 space-y-4">
            <div class="aspect-[4/5] bg-stone-100 rounded-3xl overflow-hidden border border-stone-200/80 shadow-md relative group">
                <img id="mainImage" 
                     src="<?= htmlspecialchars($pPrimaryImg) ?>" 
                     alt="<?= htmlspecialchars($pName) ?>" 
                     class="w-full h-full object-cover object-center transition-all duration-300">
                <div class="absolute bottom-4 right-4 bg-black/60 backdrop-blur-md text-white text-[10px] px-3 py-1.5 rounded-full uppercase tracking-wider font-semibold pointer-events-none">
                    AURA Boutique Collection
                </div>
            </div>

            <?php if (count($pImages) > 1): ?>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                    <?php foreach ($pImages as $idx => $img): 
                        $imgPath = is_array($img) ? $img['image_path'] : $img->imagePath;
                    ?>
                        <button type="button" 
                                onclick="document.getElementById('mainImage').src = '<?= htmlspecialchars($imgPath) ?>'"
                                class="aspect-square rounded-xl overflow-hidden border-2 border-stone-200 hover:border-stone-900 focus:border-stone-900 transition-all bg-stone-50 shadow-sm">
                            <img src="<?= htmlspecialchars($imgPath) ?>" alt="Vista <?= $idx + 1 ?>" class="w-full h-full object-cover">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Ficha de Selección y Compra (5 cols) -->
        <div class="lg:col-span-5 flex flex-col justify-between">
            <div class="space-y-6">

                <!-- Encabezado de la Prenda -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="px-3 py-1 text-xs font-bold uppercase tracking-wider bg-stone-100 text-stone-800 rounded-full">
                            <?= htmlspecialchars($pCatName) ?>
                        </span>
                        <span class="text-xs text-stone-400">
                            Vendido por <strong class="text-stone-700"><?= htmlspecialchars($pSeller) ?></strong>
                        </span>
                    </div>

                    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-stone-900 leading-tight mb-3">
                        <?= htmlspecialchars($pName) ?>
                    </h1>

                    <div class="flex items-baseline gap-3">
                        <span id="priceDisplay" class="font-serif text-3xl font-bold text-stone-900">
                            $<?= number_format($pBasePrice, 2) ?>
                        </span>
                        <span class="text-xs uppercase tracking-wider text-stone-500 font-semibold">MXN (IVA incluido)</span>
                    </div>
                </div>

                <!-- Descripción -->
                <div class="border-t border-b border-stone-200 py-4 text-xs sm:text-sm text-stone-600 leading-relaxed font-light">
                    <?= nl2br(htmlspecialchars($pDesc)) ?>
                </div>

                <!-- Formulario de Selección y Agregar a Bolsa -->
                <form action="/cart/add" method="POST" id="addToCartForm" class="space-y-6">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($pId) ?>">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($pName) ?>">
                    <input type="hidden" name="slug" value="<?= htmlspecialchars($pSlug) ?>">
                    <input type="hidden" name="image" value="<?= htmlspecialchars($pPrimaryImg) ?>">
                    <input type="hidden" name="unit_price" id="unitPriceField" value="<?= $pBasePrice ?>">
                    <input type="hidden" name="size" id="sizeField" value="">
                    <input type="hidden" name="color" id="colorField" value="">
                    <input type="hidden" name="max_stock" id="maxStockField" value="99">
                    <input type="hidden" name="redirect_to" value="/product/<?= htmlspecialchars($pSlug) ?>">

                    <!-- Selector de Tallas y Colores (Variantes) -->
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs uppercase tracking-wider font-bold text-stone-800">
                                1. Selecciona Talla y Color
                            </label>
                            <span id="variantStockInfo" class="text-xs text-emerald-700 font-medium"></span>
                        </div>

                        <?php if (empty($pVariants)): ?>
                            <p class="text-xs text-rose-600">Esta prenda se encuentra temporalmente agotada.</p>
                        <?php else: ?>
                            <div class="grid grid-cols-2 gap-2.5" id="variantsContainer">
                                <?php foreach ($pVariants as $idx => $v): 
                                    $vId = is_array($v) ? $v['id'] : $v->id;
                                    $vSize = is_array($v) ? $v['size'] : $v->size;
                                    $vColor = is_array($v) ? $v['color'] : $v->color;
                                    $vColorCode = is_array($v) ? ($v['color_code'] ?? '#000000') : ($v->colorCode ?: '#000000');
                                    $vStock = is_array($v) ? (int)$v['stock'] : $v->stock;
                                    $vAddPrice = is_array($v) ? (float)($v['additional_price'] ?? 0) : $v->additionalPrice;
                                ?>
                                    <label class="relative flex items-center justify-between p-3 rounded-xl border-2 cursor-pointer transition-all <?= $vStock > 0 ? 'border-stone-200 hover:border-stone-900 bg-white' : 'border-stone-100 bg-stone-50 opacity-40 cursor-not-allowed' ?>">
                                        <input type="radio" 
                                               name="variant_id" 
                                               value="<?= htmlspecialchars($vId) ?>" 
                                               data-price="<?= $pBasePrice + $vAddPrice ?>"
                                               data-stock="<?= $vStock ?>"
                                               data-size="<?= htmlspecialchars($vSize) ?>"
                                               data-color="<?= htmlspecialchars($vColor) ?>"
                                               <?= ($idx === 0 && $vStock > 0) ? 'checked' : '' ?>
                                               <?= $vStock === 0 ? 'disabled' : '' ?>
                                               class="hidden peer"
                                               onchange="updateVariantDetails(this)">
                                        
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-4 h-4 rounded-full border border-stone-300 flex-shrink-0" style="background-color: <?= htmlspecialchars($vColorCode) ?>"></span>
                                            <div>
                                                <span class="block text-xs font-bold text-stone-900 peer-checked:text-stone-900">
                                                    Talla <?= htmlspecialchars($vSize) ?>
                                                </span>
                                                <span class="block text-[10px] text-stone-500">
                                                    <?= htmlspecialchars($vColor) ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="text-right">
                                            <span class="text-[10px] font-semibold text-stone-600 block">
                                                Stock: <?= $vStock ?>
                                            </span>
                                            <?php if ($vAddPrice > 0): ?>
                                                <span class="text-[10px] text-amber-700 font-bold block">
                                                    +$<?= number_format($vAddPrice, 2) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="absolute inset-0 rounded-xl border-2 border-stone-900 pointer-events-none hidden peer-checked:block"></div>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Cantidad -->
                    <div class="flex items-center gap-4">
                        <div class="w-32">
                            <label class="block text-xs uppercase tracking-wider font-bold text-stone-800 mb-1.5">Cantidad</label>
                            <div class="flex items-center border border-stone-200 rounded-xl bg-white overflow-hidden shadow-sm">
                                <button type="button" onclick="decrementQty()" class="px-3 py-2 text-stone-600 hover:bg-stone-100 font-bold text-sm">-</button>
                                <input type="number" id="qtyInput" name="quantity" value="1" min="1" max="99" 
                                       class="w-full text-center text-xs font-bold py-2 border-none focus:outline-none bg-transparent">
                                <button type="button" onclick="incrementQty()" class="px-3 py-2 text-stone-600 hover:bg-stone-100 font-bold text-sm">+</button>
                            </div>
                        </div>

                        <div class="flex-1 pt-5">
                            <button type="submit" 
                                    class="w-full py-3.5 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs uppercase tracking-widest font-bold flex items-center justify-center gap-2 shadow-lg transition-all hover:shadow-xl">
                                <i class="fa-solid fa-bag-shopping"></i>
                                <span>Añadir a la Bolsa</span>
                            </button>
                        </div>
                    </div>
                </form>

                <div class="grid grid-cols-2 gap-4 pt-6 border-t border-stone-200 text-xs text-stone-600">
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-shield-check text-stone-900 text-base mt-0.5"></i>
                        <div>
                            <strong class="text-stone-900 block font-semibold">Garantía de Autenticidad</strong>
                            <span>Prendas inspeccionadas bajo control de calidad de confección.</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2.5">
                        <i class="fa-solid fa-rotate-left text-stone-900 text-base mt-0.5"></i>
                        <div>
                            <strong class="text-stone-900 block font-semibold">Cambios Sin Costo</strong>
                            <span>Hasta 30 días para cambio de talla sin complicaciones.</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>
    function updateVariantDetails(radio) {
        const price = parseFloat(radio.dataset.price);
        const stock = parseInt(radio.dataset.stock);
        const size = radio.dataset.size;
        const color = radio.dataset.color;

        document.getElementById('priceDisplay').textContent = '$' + price.toFixed(2);
        document.getElementById('unitPriceField').value = price.toFixed(2);
        document.getElementById('sizeField').value = size;
        document.getElementById('colorField').value = color;
        document.getElementById('maxStockField').value = stock;

        const stockInfo = document.getElementById('variantStockInfo');
        if (stock <= 5) {
            stockInfo.textContent = `¡Solo quedan ${stock} piezas en ${size} (${color})!`;
            stockInfo.className = 'text-xs text-amber-700 font-semibold';
        } else {
            stockInfo.textContent = `${stock} unidades disponibles`;
            stockInfo.className = 'text-xs text-emerald-700 font-medium';
        }

        const qtyInput = document.getElementById('qtyInput');
        qtyInput.max = stock;
        if (parseInt(qtyInput.value) > stock) {
            qtyInput.value = stock;
        }
    }

    function incrementQty() {
        const input = document.getElementById('qtyInput');
        const max = parseInt(input.max) || 99;
        if (parseInt(input.value) < max) {
            input.value = parseInt(input.value) + 1;
        }
    }

    function decrementQty() {
        const input = document.getElementById('qtyInput');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const activeRadio = document.querySelector('input[name="variant_id"]:checked');
        if (activeRadio) {
            updateVariantDetails(activeRadio);
        }
    });
</script>
