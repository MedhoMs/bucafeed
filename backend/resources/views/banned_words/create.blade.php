<x-admin.crud-form
    :action="$oper == 'create' ? route('banned_words.create.post') : ($oper == 'destroy' ? route('banned_words.destroy.post', $bannedWord->id ?? 0) : route('banned_words.edit.post', $bannedWord->id ?? 0))"
    :oper="$oper"
    :modelId="$bannedWord->id ?? ''"
    :datos="$datos ?? []"
    :disabled="$disabled"
    deleteText="Eliminar palabra"
>        @if($oper != 'destroy')
            <x-admin.form-template :disabled="$disabled" :fields="$fields" />
        @else
            <p class="text-white text-base bg-red-500/10 border border-red-500/20 p-4 rounded-xl">
                ¿Estás seguro de que deseas eliminar la palabra vetada <strong class="text-red-400">"{{ $bannedWord->word }}"</strong>?
            </p>
        @endif

</x-admin.crud-form>
