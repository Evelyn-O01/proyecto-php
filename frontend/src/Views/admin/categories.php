<div class="space-y-8">

    <div class="flex items-center justify-between pb-6 border-b border-stone-200">
        <div>
            <h1 class="font-serif text-2xl font-bold text-stone-900">Categorías de Moda & Ropa</h1>
            <p class="text-xs text-stone-500 mt-1">Estructura del catálogo y taxonomía de prendas</p>
        </div>
        <a href="/admin" class="text-xs font-semibold text-stone-600 hover:text-stone-900">
            &larr; Volver
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Lista de Categorías (8 cols) -->
        <div class="lg:col-span-8 bg-white rounded-3xl border border-stone-200 shadow-xs overflow-hidden">
            <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-serif text-base font-bold text-stone-900">Categorías Activas</h2>
                <span class="text-xs text-stone-400"><?= count($categories) ?> departamentos</span>
            </div>

            <div class="divide-y divide-stone-100">
                <?php foreach ($categories as $cat): 
                    $catImg = is_array($cat) ? ($cat['image_url'] ?? '') : $cat->imageUrl;
                    $catName = is_array($cat) ? $cat['name'] : $cat->name;
                    $catSlug = is_array($cat) ? $cat['slug'] : $cat->slug;
                    $catDesc = is_array($cat) ? ($cat['description'] ?? '') : $cat->description;
                ?>
                    <div class="p-4 flex items-center justify-between hover:bg-stone-50/50 text-xs">
                        <div class="flex items-center gap-4">
                            <?php if (!empty($catImg)): ?>
                                <img src="<?= htmlspecialchars($catImg) ?>" alt="" class="w-12 h-12 object-cover rounded-xl border bg-stone-100">
                            <?php else: ?>
                                <div class="w-12 h-12 rounded-xl bg-stone-100 flex items-center justify-center text-stone-400">
                                    <i class="fa-solid fa-tag"></i>
                                </div>
                            <?php endif; ?>
                            <div>
                                <strong class="font-bold text-stone-900 text-sm block"><?= htmlspecialchars($catName) ?></strong>
                                <span class="text-stone-400 font-mono text-[11px] block">/catalog?category=<?= htmlspecialchars($catSlug) ?></span>
                                <p class="text-stone-500 text-[11px] mt-0.5"><?= htmlspecialchars($catDesc) ?></p>
                            </div>
                        </div>

                        <a href="/catalog?category=<?= htmlspecialchars($catSlug) ?>" target="_blank" 
                           class="text-xs font-semibold text-stone-700 hover:text-stone-900 flex items-center gap-1">
                            <span>Ver</span> <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Formulario de Nueva Categoría (4 cols) -->
        <div class="lg:col-span-4 bg-white rounded-3xl border border-stone-200 p-6 shadow-xs space-y-4">
            <h2 class="font-serif text-base font-bold text-stone-900 pb-3 border-b border-stone-100">
                Añadir Nueva Categoría
            </h2>

            <form action="/admin/category/new" method="POST" class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Nombre de la Categoría *</label>
                    <input type="text" name="name" required placeholder="Ej. Trajes & Sastrería"
                           class="w-full p-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">URL de Imagen Representativa</label>
                    <input type="url" name="image_url" placeholder="https://images.unsplash.com/..."
                           class="w-full p-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
                </div>

                <div>
                    <label class="block font-semibold text-stone-700 mb-1.5">Descripción</label>
                    <textarea name="description" rows="3" placeholder="Breve descripción del estilo de ropa..."
                              class="w-full p-2.5 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900"></textarea>
                </div>

                <button type="submit" class="w-full py-3 bg-stone-900 hover:bg-stone-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider shadow-sm">
                    Crear Categoría
                </button>
            </form>
        </div>

    </div>

</div>
