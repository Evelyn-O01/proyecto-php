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
                    <?php foreach ($users as $u): 
                        $uName = is_array($u) ? $u['name'] : $u->name;
                        $uEmail = is_array($u) ? $u['email'] : $u->email;
                        $uPhone = is_array($u) ? ($u['phone'] ?? null) : $u->phone;
                        $uRole = is_array($u) ? $u['role'] : $u->role->value;
                        $uRoleLabel = is_array($u) ? ($u['role_label'] ?? $u['role']) : $u->role->label();
                        $uActive = is_array($u) ? $u['is_active'] : $u->isActive;
                        $uId = is_array($u) ? $u['id'] : $u->id;
                    ?>
                        <tr class="hover:bg-stone-50/60 transition-colors">
                            <td class="py-4 px-6 font-bold text-stone-900 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-stone-100 border border-stone-200 flex items-center justify-center font-bold text-stone-700">
                                    <?= substr($uName, 0, 2) ?>
                                </div>
                                <?= htmlspecialchars($uName) ?>
                            </td>
                            <td class="py-4 px-4 font-mono text-[11px] text-stone-600">
                                <?= htmlspecialchars($uEmail) ?>
                            </td>
                            <td class="py-4 px-4 text-stone-500">
                                <?= htmlspecialchars($uPhone ?: 'No registrado') ?>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $uRole === 'ADMIN' ? 'bg-amber-100 text-amber-800' : ($uRole === 'SELLER' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800') ?>">
                                    <?= htmlspecialchars($uRoleLabel) ?>
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold <?= $uActive ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' ?>">
                                    <?= $uActive ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td class="py-4 px-6 text-right">
                                <form action="/admin/user/role" method="POST" class="inline-flex items-center gap-2">
                                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($uId) ?>">
                                    <select name="role" class="p-1.5 text-xs bg-stone-50 border border-stone-200 rounded-lg focus:outline-none focus:border-stone-900">
                                        <option value="CLIENT" <?= $uRole === 'CLIENT' ? 'selected' : '' ?>>Cliente</option>
                                        <option value="SELLER" <?= $uRole === 'SELLER' ? 'selected' : '' ?>>Vendedor</option>
                                        <option value="ADMIN" <?= $uRole === 'ADMIN' ? 'selected' : '' ?>>Administrador</option>
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
