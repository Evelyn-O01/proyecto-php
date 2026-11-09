<div class="max-w-md mx-auto px-4 py-16">

    <div class="bg-white rounded-3xl border border-stone-200 p-8 shadow-sm space-y-6">
        
        <div class="text-center space-y-2">
            <span class="font-serif text-3xl font-bold tracking-tight text-stone-900">AURA</span>
            <h1 class="font-serif text-xl font-bold text-stone-800">Iniciar Sesión</h1>
            <p class="text-xs text-stone-500">Ingresa a tu cuenta para gestionar tus pedidos o tu boutique</p>
        </div>

        <!-- Acceso Rápido de Prueba para los 3 Roles -->
        <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs space-y-2.5">
            <div class="flex items-center gap-2 text-amber-900 font-bold">
                <i class="fa-solid fa-bolt text-amber-600"></i>
                <span>Acceso Rápido por Rol (Demostración)</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <a href="/auth/quick/admin" class="py-2 px-1 text-center bg-white border border-amber-300 rounded-lg font-bold text-[11px] text-amber-900 hover:bg-amber-100 transition-colors shadow-xs">
                    👑 Admin
                </a>
                <a href="/auth/quick/seller" class="py-2 px-1 text-center bg-white border border-amber-300 rounded-lg font-bold text-[11px] text-amber-900 hover:bg-amber-100 transition-colors shadow-xs">
                    🛍️ Vendedor
                </a>
                <a href="/auth/quick/client" class="py-2 px-1 text-center bg-white border border-amber-300 rounded-lg font-bold text-[11px] text-amber-900 hover:bg-amber-100 transition-colors shadow-xs">
                    👤 Cliente
                </a>
            </div>
        </div>

        <form action="/login" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-stone-700 mb-1.5">Correo Electrónico</label>
                <input type="email" name="email" required placeholder="tu@correo.com"
                       class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
            </div>

            <div>
                <label class="block font-semibold text-stone-700 mb-1.5">Contraseña</label>
                <input type="password" name="password" required placeholder="••••••••"
                       class="w-full p-3 bg-stone-50 border border-stone-200 rounded-xl focus:outline-none focus:border-stone-900">
            </div>

            <button type="submit" 
                    class="w-full py-3.5 bg-stone-900 hover:bg-stone-800 text-white rounded-xl text-xs uppercase tracking-widest font-bold shadow-md transition-all">
                Ingresar al Sistema
            </button>
        </form>

        <div class="pt-4 border-t border-stone-100 text-center text-xs text-stone-500">
            ¿Aún no tienes cuenta? 
            <a href="/register" class="font-bold text-stone-900 hover:underline">Regístrate gratis</a>
        </div>

    </div>

</div>
