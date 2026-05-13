@props([
    'action',
    'oper',
    'modelId' => null,
    'datos' => [],
    'disabled' => false,
    'enctype' => null,
    'saveText' => 'Guardar',
    'editText' => 'Actualizar',
    'deleteText' => 'Confirmar Eliminación',
    'showCancel' => true,
    'cancelText' => 'Volver',
])

<div class="w-full pt-4 sticky top-0 px-2 lg:px-0">
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
        action="{{ $action }}"   
        method="POST"
        @if($enctype) enctype="{{ $enctype }}" @endif
        class="space-y-6"
    >
        @csrf

        <input name="id" type="hidden" value="{{ $modelId }}" />

        {{ $slot }}

        <div class="flex items-center justify-between mt-8">
            @if($showCancel)
                <button type="button" class="px-8 py-3 rounded-2xl bg-white/5 hover:bg-white/10 text-white/60 hover:text-white border border-white/10 transition-all duration-300 text-sm font-bold uppercase tracking-widest flex items-center gap-2" data-bs-dismiss="modal" onclick="document.getElementById('default-modal').classList.add('hidden')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    {{ $cancelText }}
                </button>
            @else
                <div></div>
            @endif
<div class="flex items-center gap-3">
                @if (($oper != 'destroy' && $oper != 'show') && (!$disabled || $oper == 'edit'))
                    <button type="submit" class="w-full md:w-auto bg-linear-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white px-8 py-3 rounded-xl font-bold transition-all duration-300 shadow-lg shadow-cyan-900/20 active:scale-95 flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        {{ ($oper == 'edit') ? $editText : $saveText }}
                    </button>
                @endif
                @if ($oper == 'destroy' && empty($datos['exito']))
                    <button type="submit" class="px-6 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 transition-all duration-200">
                        {{ $deleteText }}
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>
