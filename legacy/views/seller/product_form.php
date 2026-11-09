<div class="max-w-4xl mx-auto space-y-8">

    <div class="flex items-center justify-between pb-6 border-b border-stone-200">
        <div>
            <h1 class="font-serif text-2xl font-bold text-stone-900">Publicar Nueva Prenda de Ropa</h1>
            <p class="text-xs text-stone-500 mt-1">Ingresa las especificaciones, imágenes y variantes de tallas para el catálogo</p>
        </div>
        <a href="/seller" class="text-xs font-semibold text-stone-600 hover:text-stone-900">
            &larr; Volver a Mis Prendas
        </a>
    </div>

    <form action="/seller/product/new" method="POST" enctype="multipart/form-data" class="space-y-8">

        <!-- 1. Información General de la Prenda -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-xs space-y-5">
            <h2 class="font-serif text-base font-bold text-stone-900 pb-3 border-b border-stone-100">
                1. Información General
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-stone-700 mb-1.5">Nombre del Producto / Prenda *</label>
                    <input type="text" name="name" required placeholder="Ej. Camisa Lino Manga Larga Slim Fit"
                           class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Categoría *</label>
                    <select name="category_id" required class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Precio Base (MXN) *</label>
                    <input type="number" step="0.01" min="1" name="base_price" required placeholder="89.50"
                           class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                </div>

                <div class="sm:col-span-2">
                    <label class="block font-semibold text-stone-700 mb-1.5">Descripción Detallada & Composición</label>
                    <textarea name="description" rows="4" placeholder="Describe los tejidos, corte, estilo y ocasiones de uso..."
                              class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900"></textarea>
                </div>
            </div>
        </div>

        <!-- 2. Imágenes de la Prenda -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-xs space-y-5">
            <h2 class="font-serif text-base font-bold text-stone-900 pb-3 border-b border-stone-100">
                2. Fotografía del Producto
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Subir Imagen desde Computadora</label>
                    <input type="file" name="image_file" accept="image/*"
                           class="w-full p-2.5 bg-stone-50 border border-stone-200 rounded-xl file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white hover:file:bg-stone-800">
                    <span class="text-[10px] text-stone-400 block mt-1">Formatos: JPG, PNG, WEBP (Máx. 8 MB)</span>
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">O URL de Imagen Directa (Unsplash, CDN, etc.)</label>
                    <input type="url" name="image_url" placeholder="https://images.unsplash.com/..."
                           class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                    <span class="text-[10px] text-stone-400 block mt-1">Si dejas ambos en blanco, se asignará una foto de estudio por defecto.</span>
                </div>
            </div>
        </div>

        <!-- 3. Variantes de Tallas y Colores (Stock) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-xs space-y-5">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <h2 class="font-serif text-base font-bold text-stone-900">
                    3. Variantes de Talla, Color y Existencias
                </h2>
                <button type="button" onclick="addVariantRow()" class="text-xs font-semibold text-stone-900 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-plus text-[10px]"></i> Añadir otra talla/color
                </button>
            </div>

            <div id="variantsRows" class="space-y-3">
                <!-- Fila 1 -->
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 p-3 bg-stone-50 rounded-2xl border border-stone-200 text-xs items-center">
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Talla</label>
                        <input type="text" name="variants_size[]" value="S" placeholder="S, M, L, 32..." class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Color</label>
                        <input type="text" name="variants_color[]" value="Negro" placeholder="Negro, Azul..." class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Muestra Color</label>
                        <input type="color" name="variants_color_code[]" value="#111111" class="w-full h-9 p-1 bg-white border border-stone-200 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Stock (Piezas)</label>
                        <input type="number" name="variants_stock[]" value="15" min="0" class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Precio Extra (+MXN)</label>
                        <input type="number" step="0.01" name="variants_price[]" value="0.00" class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                </div>

                <!-- Fila 2 -->
                <div class="grid grid-cols-1 sm:grid-cols-5 gap-3 p-3 bg-stone-50 rounded-2xl border border-stone-200 text-xs items-center">
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Talla</label>
                        <input type="text" name="variants_size[]" value="M" placeholder="S, M, L, 32..." class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Color</label>
                        <input type="text" name="variants_color[]" value="Negro" placeholder="Negro, Azul..." class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Muestra Color</label>
                        <input type="color" name="variants_color_code[]" value="#111111" class="w-full h-9 p-1 bg-white border border-stone-200 rounded-lg cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Stock (Piezas)</label>
                        <input type="number" name="variants_stock[]" value="20" min="0" class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-stone-500 mb-1">Precio Extra (+MXN)</label>
                        <input type="number" step="0.01" name="variants_price[]" value="0.00" class="w-full p-2 bg-white border border-stone-200 rounded-lg">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            <a href="/seller" class="px-6 py-3 border border-stone-300 text-stone-700 rounded-xl text-xs font-semibold hover:bg-stone-50">
                Cancelar
            </a>
            <button type="submit" class="px-8 py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md">
                Publicar Prenda en el Catálogo
            </button>
        </div>

    </form>

</div>

<script>
    function addVariantRow() {
        const container = document.getElementById('variantsRows');
        const row = document.createElement('div');
        row.className = "grid grid-cols-1 sm:grid-cols-5 gap-3 p-3 bg-stone-50 rounded-2xl border border-stone-200 text-xs items-center";
        row.innerHTML = `
            <div>
                <label class="block text-[10px] font-semibold text-stone-500 mb-1">Talla</label>
                <input type="text" name="variants_size[]" placeholder="L, XL..." class="w-full p-2 bg-white border border-stone-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-stone-500 mb-1">Color</label>
                <input type="text" name="variants_color[]" placeholder="Color..." class="w-full p-2 bg-white border border-stone-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-stone-500 mb-1">Muestra Color</label>
                <input type="color" name="variants_color_code[]" value="#333333" class="w-full h-9 p-1 bg-white border border-stone-200 rounded-lg cursor-pointer">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-stone-500 mb-1">Stock (Piezas)</label>
                <input type="number" name="variants_stock[]" value="10" min="0" class="w-full p-2 bg-white border border-stone-200 rounded-lg">
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-stone-500 mb-1">Precio Extra</label>
                <input type="number" step="0.01" name="variants_price[]" value="0.00" class="w-full p-2 bg-white border border-stone-200 rounded-lg">
            </div>
        `;
        container.appendChild(row);
    }
</script>
