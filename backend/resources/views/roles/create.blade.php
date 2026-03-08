<div class="container mx-auto pt-4 sticky top-0">
    @if($errors->any())
        <ul class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            @foreach ($errors->all() as $error)
                <li class="text-red-400 text-sm list-disc list-inside">{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    
    @if(!empty($datos['exito']))
        <p class="mb-4 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg"> {{ $datos['exito'] }} </p>
    @endif

    <form id="formGeneral"
    data-oper="{{ $oper }}"
    action="@if($oper == 'create'){{ route('role.store') }}@elseif($oper == 'destroy'){{ route('role.destroy.post', $role->id ?? 0) }}@else{{ route('role.update', $role->id ?? 0) }}@endif"   
    method="POST"
    class="space-y-6"
    >
        @csrf

        <input name="id" type="hidden" value="{{ $role->id ?? '' }}" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nombre -->
            <div>
                <label for="idname" class="block text-sm font-medium text-white/70 mb-1">Nombre del Rol</label>
                <input {{ $disabled }} value="{{ old('name', $role->name ?? '') }}" type="text" name="name" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idname" placeholder="Ej: Editor">
                @error('name')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Código -->
            <div>
                <label for="idcode" class="block text-sm font-medium text-white/70 mb-1">Código del Rol (Unico)</label>
                <input {{ $disabled }} value="{{ old('code', $role->code ?? '') }}" type="text" name="code" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idcode" placeholder="Ej: EDTR">
                @error('code')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 mt-8">
            @if (!$disabled || $oper == 'edit')
                <button type="submit" class="btn-primary px-6 py-2 rounded-xl font-semibold transition-all duration-200">
                    {{ ($oper == 'edit') ? 'Actualizar Rol' : 'Guardar Rol' }}
                </button>
            @endif

            @if ($oper == 'destroy' && empty($datos['exito']))
                <button type="submit" class="px-6 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 transition-all duration-200">
                    Confirmar Eliminación
                </button>
            @endif
        </div>
    </form>
</div>
