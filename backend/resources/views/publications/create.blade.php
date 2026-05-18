<x-admin.crud-form
    :oper="$oper"
    :action="$oper == 'create' ? route('publication.store') : ($oper == 'destroy' ? route('publication.destroy.post', $publication->id ?? 0) : route('publication.update', $publication->id ?? 0))"
    :title="'Gestión de Publicación'"
    :description="'Visualiza o modifica los datos de esta publicación del centro educativo.'"
    :datos="$datos"
    :disabled="$disabled"
    enctype="multipart/form-data"
>
    <x-admin.form-template :disabled="$disabled" :fields="$fields" />

    @if($oper === 'show' && isset($publication) && $publication->image)
        <div class="mt-8 border-t border-white/10 pt-6">
            <h3 class="text-white text-lg font-bold mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-indigo-400"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><line x1="15" y1="8" x2="15.01" y2="8" /><rect x="4" y="4" width="16" height="16" rx="3" /><path d="M4 15l4 -4a3 5 0 0 1 3 0l5 5" /><path d="M14 14l1 -1a3 5 0 0 1 3 0l2 2" /></svg>
                Imagen adjunta
            </h3>
            <div class="bg-black/20 border border-white/5 rounded-xl p-4 flex justify-center">
                <img src="{{ $publication->image_url }}" alt="Imagen de publicación" class="max-w-full max-h-96 rounded-lg object-contain">
            </div>
        </div>
    @endif
</x-admin.crud-form>
