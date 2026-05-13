<x-admin.crud-form 
    :action="$oper == 'create' ? route('user.create') : ($oper == 'destroy' ? route('user.destroy', $user->id ?? 0) : route('user.edit.post', $user->id ?? 0))"
    :oper="$oper"
    :modelId="$user->id ?? ''"
    :datos="$datos ?? []"
    :disabled="$disabled"
    enctype="multipart/form-data"
    deleteText="Eliminar usuario"
>

        <!-- Campos de imagen y banner -->
        @if($oper !== 'create')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 bg-white/5 rounded-xl border border-white/10">
            <div>
                <label for="idprofile_picture" class="block text-sm font-medium text-white/70 mb-1">Foto de Perfil</label>
                <input {{ $disabled }} type="file" name="profile_picture" id="idprofile_picture" 
                    class="w-full text-sm text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-500/10 file:text-blue-400 hover:file:bg-blue-500/20 transition-all cursor-pointer">
                @if($user->profile_picture)
                    <p class="text-xs text-blue-400 mt-1">Tiene una imagen configurada</p>
                @endif
            </div>
            <div>
                <label for="idbanner" class="block text-sm font-medium text-white/70 mb-1">Banner</label>
                <input {{ $disabled }} type="file" name="banner" id="idbanner" 
                    class="w-full text-sm text-white/50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-500/10 file:text-purple-400 hover:file:bg-purple-500/20 transition-all cursor-pointer">
                @if($user->banner)
                    <p class="text-xs text-purple-400 mt-1">Tiene un banner configurado</p>
                @endif
            </div>
        </div>
        @endif

        <x-admin.form-template :disabled="$disabled" :fields="$fields" />


</x-admin.crud-form>

<script>
(function () {
    const centerSelect = document.getElementById('ideducational_center_id');
    const levelSelect = document.getElementById('ideducation_level');
    const levelHidden = levelSelect ? levelSelect.parentElement.querySelector('input[type="hidden"][name="education_level"]') : null;

    if (!centerSelect || !levelSelect) return;

    // Leer el mapa de centro_id => tipo desde el data attribute
    let centerTypes = {};
    try {
        centerTypes = JSON.parse(centerSelect.dataset.centerTypes || '{}');
    } catch (e) {
        console.error('Error parsing center types:', e);
    }

    const newCenterSelect = centerSelect.cloneNode(true);
    if (centerSelect.parentNode) {
        centerSelect.parentNode.replaceChild(newCenterSelect, centerSelect);
    }

    newCenterSelect.addEventListener('change', function () {
        const centerId = this.value;
        if (centerId && centerTypes[centerId]) {
            levelSelect.value = centerTypes[centerId];
            // Actualizar también el hidden input para que se envíe el valor
            if (levelHidden) {
                levelHidden.value = centerTypes[centerId];
            }
        } else {
            levelSelect.value = '';
            if (levelHidden) {
                levelHidden.value = '';
            }
        }
    });
})();
</script>
