<script setup>
/**
 * GenericForm.vue - Plantilla maestra de formularios para TelamoNet
 * Permite renderizar formularios dinámicos basados en una definición de campos.
 */
import { ref, watch } from 'vue'
import ImageUpload from './ImageUpload.vue'

const props = defineProps({
    modelValue: { type: Object, required: true },
    fields: { type: Array, required: true },
    loading: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue', 'submit'])
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 pt-2">
        <div v-for="field in fields" :key="field.id" 
            :class="[field.full !== false ? 'col-span-full' : 'col-span-1', 'space-y-2']">
            
            <!-- LABEL -->
            <label v-if="field.type !== 'info'" class="block text-[10px] font-black uppercase text-white/60 tracking-[0.2em] ml-1">
                {{ field.label }}
                <span v-if="field.required" class="text-error-normal ml-1">*</span>
            </label>

            <!-- INPUT TEXT / EMAIL / DATE / TIME -->
            <input v-if="['text', 'email', 'date', 'time', 'number'].includes(field.type)" 
                :type="field.type"
                :value="modelValue[field.id]"
                @input="emit('update:modelValue', { ...modelValue, [field.id]: $event.target.value })"
                :placeholder="field.placeholder" 
                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white !text-white text-sm focus:border-brand-net outline-none transition-all shadow-inner"
            >

            <!-- TEXTAREA -->
            <textarea v-if="field.type === 'textarea'"
                :value="modelValue[field.id]"
                @input="emit('update:modelValue', { ...modelValue, [field.id]: $event.target.value })"
                :placeholder="field.placeholder"
                rows="3"
                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white !text-white text-sm focus:border-brand-net outline-none transition-all shadow-inner resize-none"
            ></textarea>

            <!-- SELECT SIMPLE -->
            <select v-if="field.type === 'select'" 
                :value="modelValue[field.id]"
                @change="emit('update:modelValue', { ...modelValue, [field.id]: $event.target.value })"
                class="custom-select w-full bg-background text-white border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary-normal outline-none">
                <option :value="null" disabled>{{ field.placeholder || 'Seleccionar...' }}</option>
                <option v-for="opt in field.options" :key="opt.id || opt.code" :value="opt.id || opt.code">
                    {{ opt.name }}
                </option>
            </select>

            <!-- SELECT AGRUPADO (Especial para ciclos/materias) -->
            <select v-if="field.type === 'select-grouped'" 
                :value="modelValue[field.id]"
                @change="emit('update:modelValue', { ...modelValue, [field.id]: $event.target.value })"
                class="custom-select w-full bg-background text-white border border-white/10 rounded-xl px-4 py-3 text-sm focus:border-secondary-normal outline-none font-bold">
                <optgroup v-for="g in field.groups" :key="g.id" :label="g.name" class="bg-black/80 text-secondary-normal uppercase text-[10px]">
                    <option v-for="tag in g.tags" :key="tag.id" :value="tag.id" class="text-white normal-case font-normal">{{ tag.name }}</option>
                </optgroup>
            </select>

            <!-- IMAGE UPLOAD -->
            <ImageUpload v-if="field.type === 'file'"
                :modelValue="modelValue[field.id]"
                @update:modelValue="emit('update:modelValue', { ...modelValue, [field.id]: $event })"
                :label="field.label"
                :previewUrl="field.previewUrl"
                :aspect="field.aspect || 'square'"
            />

            <!-- CHECKLIST (Especial alumnos) -->
            <div v-if="field.type === 'checklist'" class="max-h-72 overflow-y-auto space-y-2 pr-2 custom-scrollbar border border-white/5 p-2 rounded-xl bg-black/10">
                <label v-for="s in field.options" :key="s.id" 
                    :class="['flex items-center gap-3 p-4 rounded-xl border transition-all cursor-pointer group', (modelValue[field.id] || []).includes(s.id) ? 'bg-secondary-normal/20 border-secondary-normal/40' : 'bg-white/5 border-white/5 hover:bg-white/10']">
                    <input type="checkbox" :value="s.id" 
                        :checked="(modelValue[field.id] || []).includes(s.id)"
                        @change="(e) => {
                            const ids = [...(modelValue[field.id] || [])]
                            if(e.target.checked) ids.push(s.id)
                            else ids.splice(ids.indexOf(s.id), 1)
                            emit('update:modelValue', { ...modelValue, [field.id]: ids })
                        }"
                        class="w-4 h-4 accent-secondary-normal rounded">
                    <div class="flex flex-col">
                        <span class="text-xs text-white/90 font-black uppercase tracking-tight group-hover:translate-x-1 transition-transform">{{ s.name }}</span>
                        <span class="text-[9px] text-dimmed font-bold tracking-wider">{{ s.email }}</span>
                    </div>
                </label>
            </div>

            <!-- INFO BOX (SÓLO LECTURA) -->
            <div v-if="field.type === 'info'" class="p-4 bg-secondary-normal/10 rounded-xl border border-secondary-normal/20 group">
                <p class="text-[10px] text-dimmed font-black uppercase tracking-widest mb-1 group-hover:text-secondary-normal transition-colors">{{ field.label }}</p>
                <p class="text-sm font-black text-white uppercase">{{ field.value }}</p>
            </div>

        </div>
    </div>
</template>

<style scoped>
.custom-select { color-scheme: dark; }
.custom-select option { background-color: var(--background); color: white; padding: 12px; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: var(--secondary-normal); border-radius: 10px; }
</style>
