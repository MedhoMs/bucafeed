<div class="container mx-auto pt-4 relative">
    @if($errors->any())
        <ul class="mb-4 bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            @foreach ($errors->all() as $error)
                <li class="text-red-400 text-sm list-disc list-inside">{{ $error }}</li>
            @endforeach
        </ul>
    @endif
    
    @if($datos['exito'])
        <p class="mb-4 bg-green-500/10 border border-green-500/20 text-green-400 p-4 rounded-lg"> {{ $datos['exito'] }} </p>
    @endif

    <form id="formGeneral" data-oper="{{ $oper }}" action="@if($oper == 'create'){{ url('/admin/banned-words/create') }}@elseif($oper == 'destroy'){{ url('/admin/banned-words/' . ($bannedWord->id ?? 0)) }}@endif" method="POST" class="space-y-6">
        @csrf

        @if($oper != 'destroy')
            <div>
                <label for="idword" class="block text-sm font-medium text-white/70 mb-1">Palabra Vetada</label>
                <input {{ $disabled }} value="{{ old('word', $bannedWord->word ?? '') }}" type="text" name="word" 
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-2.5 text-white placeholder-white/30 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 transition-all duration-200" 
                    id="idword" placeholder="Ej: insulto">
                @error('word')
                    <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        @else
            <p class="text-white text-base bg-red-500/10 border border-red-500/20 p-4 rounded-xl">
                ¿Estás seguro de que deseas eliminar la palabra vetada <strong class="text-red-400">"{{ $bannedWord->word }}"</strong>?
            </p>
        @endif

        <div class="flex items-center justify-end gap-3 mt-8">
            @if (!$disabled)
                <button type="submit" class="btn-primary px-6 py-2 rounded-xl font-semibold transition-all duration-200">
                    Guardar
                </button>
            @endif

            @if ($oper == 'destroy')
                <button type="submit" class="px-6 py-2 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-400 border border-red-500/30 transition-all duration-200">
                    Eliminar palabra
                </button>
            @endif
        </div>
    </form>

    <button type="button" class="mt-4 px-4 py-2 rounded-xl bg-slate-700/50 hover:bg-slate-700 text-white transition-all duration-200" data-bs-dismiss="modal" onclick="document.getElementById('default-modal').classList.add('hidden')">
        Volver
    </button>
</div>
