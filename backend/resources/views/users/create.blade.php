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
