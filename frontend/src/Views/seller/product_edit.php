<?php
    $prodId = is_array($product) ? $product['id'] : $product->id;
    $prodName = is_array($product) ? $product['name'] : $product->name;
    $prodCatId = is_array($product) ? ($product['category_id'] ?? 1) : $product->categoryId;
    $prodPrice = is_array($product) ? $product['base_price'] : $product->basePrice;
    $prodStatus = is_array($product) ? ($product['status'] ?? 'ACTIVE') : $product->status;
    $prodDesc = is_array($product) ? ($product['description'] ?? '') : $product->description;
    $prodVariants = is_array($product) ? ($product['variants'] ?? []) : $product->variants;
?>
<div class="max-w-4xl mx-auto space-y-8">

    <div class="flex items-center justify-between pb-6 border-b border-stone-200">
        <div>
            <h1 class="font-serif text-2xl font-bold text-stone-900">Editar Prenda: <?= htmlspecialchars($prodName) ?></h1>
            <p class="text-xs text-stone-500 mt-1">Actualiza información de catálogo y ajusta el stock de cada variante</p>
        </div>
        <a href="/seller" class="text-xs font-semibold text-stone-600 hover:text-stone-900">
            &larr; Volver
        </a>
    </div>

    <form action="/seller/product/edit/<?= htmlspecialchars($prodId) ?>" method="POST" class="space-y-8">

        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-xs space-y-5">
            <h2 class="font-serif text-base font-bold text-stone-900 pb-3 border-b border-stone-100">
                Información de la Prenda
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-stone-700 mb-1.5">Nombre *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($prodName) ?>"
                           class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Categoría *</label>
                    <select name="category_id" required class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        <?php foreach ($categories as $cat): 
                            $cId = is_array($cat) ? $cat['id'] : $cat->id;
                            $cName = is_array($cat) ? $cat['name'] : $cat->name;
                        ?>
                            <option value="<?= $cId ?>" <?= $cId === (int)$prodCatId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Precio Base (MXN) *</label>
                    <input type="number" step="0.01" min="1" name="base_price" required value="<?= $prodPrice ?>"
                           class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Estado en Tienda</label>
                    <select name="status" class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        <option value="ACTIVE" <?= $prodStatus === 'ACTIVE' ? 'selected' : '' ?>>Activo (Visible en Catálogo)</option>
                        <option value="DRAFT" <?= $prodStatus === 'DRAFT' ? 'selected' : '' ?>>Borrador (Oculto)</option>
                        <option value="OUT_OF_STOCK" <?= $prodStatus === 'OUT_OF_STOCK' ? 'selected' : '' ?>>Agotado</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-semibold text-stone-700 mb-1.5">Descripción</label>
                    <textarea name="description" rows="4"
                              class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900"><?= htmlspecialchars($prodDesc) ?></textarea>
                </div>
            </div>
        </div>

        <!-- Ajuste de Stock por Variante -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-xs space-y-4">
            <h2 class="font-serif text-base font-bold text-stone-900 pb-3 border-b border-stone-100">
                Existencias por Talla y Color
            </h2>

            <div class="space-y-3">
                <?php foreach ($prodVariants as $v): 
                    $vId = is_array($v) ? $v['id'] : $v->id;
                    $vSize = is_array($v) ? $v['size'] : $v->size;
                    $vColor = is_array($v) ? $v['color'] : $v->color;
                    $vColorCode = is_array($v) ? ($v['color_code'] ?? '#111') : ($v->colorCode ?? '#111');
                    $vSku = is_array($v) ? $v['sku'] : $v->sku;
                    $vStock = is_array($v) ? $v['stock'] : $v->stock;
                ?>
                    <div class="flex items-center justify-between p-3.5 bg-stone-50 rounded-2xl border border-stone-200 text-xs">
                        <div class="flex items-center gap-3">
                            <span class="w-4 h-4 rounded-full border border-stone-300" style="background-color: <?= htmlspecialchars($vColorCode ?: '#111') ?>"></span>
                            <div>
                                <strong class="font-bold text-stone-900">Talla <?= htmlspecialchars($vSize) ?></strong>
                                <span class="text-stone-500">· Color <?= htmlspecialchars($vColor) ?></span>
                                <span class="text-[10px] text-stone-400 font-mono block">SKU: <?= htmlspecialchars($vSku) ?></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <label class="text-[11px] font-semibold text-stone-600">Stock:</label>
                            <input type="number" min="0" name="variant_stock[<?= htmlspecialchars($vId) ?>]" value="<?= $vStock ?>"
                                   class="w-24 p-2 text-center bg-white border border-stone-200 rounded-xl font-bold focus:outline-none focus:border-stone-900">
                            <span class="text-[11px] text-stone-400">piezas</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="/seller" class="px-6 py-3 border border-stone-300 text-stone-700 rounded-xl text-xs font-semibold hover:bg-stone-50">
                Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md">
                Guardar Cambios
            </button>
        </div>

    </form>

</div>
