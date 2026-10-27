<div class="max-w-md mx-auto px-4 py-16">

    <div class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm space-y-6">
        
        <div class="text-center space-y-2">
            <span class="font-serif text-3xl font-bold tracking-tight text-stone-900">AURA</span>
            <h1 class="font-serif text-xl font-bold text-stone-800">Crear Nueva Cuenta</h1>
            <p class="text-xs text-stone-500">Únete a nuestra plataforma como comprador o abre tu tienda como vendedor</p>
        </div>

        <form action="/register" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-stone-700 mb-1.5">Nombre Completo *</label>
                <input type="text" name="name" required placeholder="Ej. Camila Morales"
                       class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
            </div>

            <div>
                <label class="block font-semibold text-stone-700 mb-1.5">Correo Electrónico *</label>
                <input type="email" name="email" required placeholder="tu@correo.com"
                       class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
            </div>

            <div>
                <label class="block font-semibold text-stone-700 mb-1.5">Teléfono Móvil</label>
                <input type="tel" name="phone" placeholder="+52 55 1234 5678"
                       class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
            </div>

            <div>
                <label class="block font-semibold text-stone-700 mb-1.5">Contraseña (Mínimo 6 caracteres) *</label>
                <input type="password" name="password" required minlength="6" placeholder="••••••••"
                       class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
            </div>

            <!-- Selección de Rol -->
            <div>
                <label class="block font-semibold text-stone-700 mb-1.5">Tipo de Cuenta *</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="p-3 border-2 border-stone-200 rounded-xl cursor-pointer hover:border-stone-900 transition-all flex flex-col gap-1">
                        <input type="radio" name="role" value="CLIENT" checked class="text-stone-900 focus:ring-0">
                        <span class="font-bold text-stone-900">Cliente</span>
                        <span class="text-[10px] text-stone-500 leading-tight">Comprar prendas y rastrear pedidos</span>
                    </label>

                    <label class="p-3 border-2 border-stone-200 rounded-xl cursor-pointer hover:border-stone-900 transition-all flex flex-col gap-1">
                        <input type="radio" name="role" value="SELLER" class="text-stone-900 focus:ring-0">
                        <span class="font-bold text-stone-900">Vendedor</span>
                        <span class="text-[10px] text-stone-500 leading-tight">Publicar catálogo y gestionar stock</span>
                    </label>
                </div>
            </div>

            <button type="submit" 
                    class="w-full py-3.5 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs uppercase tracking-widest font-bold shadow-md transition-all">
                Registrar Cuenta
            </button>
        </form>

        <div class="pt-4 border-t border-stone-100 text-center text-xs text-stone-500">
            ¿Ya tienes una cuenta? 
            <a href="/login" class="font-bold text-stone-900 hover:underline">Inicia sesión</a>
        </div>

    </div>

</div>
