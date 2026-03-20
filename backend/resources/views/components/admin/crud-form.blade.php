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

        <div class="flex items-center justify-end gap-3 mt-8">
            @if (!$disabled || $oper == 'edit')
                <button type="submit" class="btn-primary px-6 py-2 rounded-xl font-semibold transition-all duration-200">
                    {{ ($oper == 'edit') ? $editText : $saveText }}
                </button>
            @endif

            @if ($oper == 'destroy' && empty($datos['exito']))
                <button type="submit" class="px-6 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 transition-all duration-200">
                    {{ $deleteText }}
                </button>
            @endif
        </div>
    </form>

    @if($showCancel)
        <button type="button" class="mt-4 px-4 py-2 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-white transition-all duration-200" data-bs-dismiss="modal" onclick="document.getElementById('default-modal').classList.add('hidden')">
            {{ $cancelText }}
        </button>
    @endif
</div>
