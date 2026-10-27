<div class="space-y-8">

    <div class="flex items-center justify-between pb-6 border-b border-stone-200">
        <div>
            <h1 class="font-serif text-2xl font-bold text-stone-900">Gestión de Usuarios y Roles (RBAC)</h1>
            <p class="text-xs text-stone-500 mt-1">Supervisa y asigna permisos entre Administrador, Vendedor y Cliente</p>
        </div>
        <a href="/admin" class="text-xs font-semibold text-stone-600 hover:text-stone-900">
            &larr; Volver
        </a>
    </div>

    <div class="bg-white rounded-3xl border border-stone-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 border-b border-stone-200 text-stone-600 font-semibold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-6">Usuario</th>
                        <th class="py-3 px-4">Correo</th>
                        <th class="py-3 px-4">Teléfono</th>
                        <th class="py-3 px-4">Rol Actual</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-6 text-right">Modificar Rol</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 text-stone-700">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-stone-50/60 transition-colors">
                            <td class="py-4 px-6 font-bold text-stone-900 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-stone-100 border border-stone-200 flex items-center justify-center font-bold text-stone-700">
                                    <?= substr($u->name, 0, 2) ?>
                                </div>
                                <?= htmlspecialchars($u->name) ?>
                            </td>
                            <td class="py-4 px-4 font-mono text-[11px] text-stone-600">
                                <?= htmlspecialchars($u->email) ?>
                            </td>
                            <td class="py-4 px-4 text-stone-500">
                                <?= htmlspecialchars($u->phone ?: 'No registrado') ?>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $u->role->value === 'ADMIN' ? 'bg-amber-100 text-amber-800' : ($u->role->value === 'SELLER' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800') ?>">
                                    <?= $u->role->label() ?>
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $u->isActive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                    <?= $u->isActive ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <form action="/admin/user/role" method="POST" class="inline-flex items-center gap-2">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($u->id) ?>">
                                    <select name="role" class="p-1.5 text-xs bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
                                        <option value="CLIENT" <?= $u->role->value === 'CLIENT' ? 'selected' : '' ?>>Cliente</option>
                                        <option value="SELLER" <?= $u->role->value === 'SELLER' ? 'selected' : '' ?>>Vendedor</option>
                                        <option value="ADMIN" <?= $u->role->value === 'ADMIN' ? 'selected' : '' ?>>Administrador</option>
                                    </select>
                                    <button type="submit" class="px-3 py-1.5 bg-stone-900 hover:bg-stone-800 text-white rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                        Guardar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
