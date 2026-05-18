<script setup>
import { ref, watch } from 'vue'
import ImageUpload from '@/components/common/forms/ImageUpload.vue'
import PrimaryButton from '@/components/common/PrimaryButton.vue'
import { useApi } from '@/composables/useApi'
import { useTranslations } from '@/composables/useTranslations'

const { t } = useTranslations()

const props = defineProps({
    show: Boolean,
    user: Object
})

const emit = defineEmits(['close', 'updated'])
const { post, loading } = useApi()

const formData = ref({
    name: '',
    last_name: '',
    profile_picture: null,
    banner: null
})

// Inicializar datos cuando se abre el modal
watch(() => props.show, (isShowing) => {
    if (isShowing && props.user) {
        // Dividir nombre y apellidos si vienen juntos en 'name' o usarlos por separado si existen
        // En ProfileView.vue vimos que se concatenan: data.name + ' ' + data.last_name
        // Para simplificar, asumiremos que props.user ya tiene los campos separados o los procesamos
        formData.value.name = props.user.name || ''
        formData.value.last_name = props.user.last_name || ''
        formData.value.profile_picture = null
        formData.value.banner = null
    }
})

const saveProfile = async () => {
    try {
        const data = new FormData()
        data.append('name', formData.value.name)
        data.append('last_name', formData.value.last_name)
        
        // Laravel requiere _method: PUT si enviamos archivos mediante POST
        data.append('_method', 'PUT')

        if (formData.value.profile_picture instanceof File) {
            data.append('profile_picture', formData.value.profile_picture)
        }
        if (formData.value.banner instanceof File) {
            data.append('banner', formData.value.banner)
        }

        // Endpoint: /api/users/{id}
        const response = await post(`users/${props.user.id}`, data)
        
        if (response) {
            emit('updated', response)
            emit('close')
        }
    } catch (e) {
        console.error("Error al actualizar perfil:", e)
    }
}
</script>

<template>
    <Transition name="fade">
        <div v-if="show" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/80 backdrop-blur-sm cursor-pointer" @click="emit('close')"></div>

            <!-- Modal -->
            <div class="relative w-full max-w-2xl bg-[#15202b] border border-white/10 rounded-[2.5rem] overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
                <header class="p-6 border-b border-white/10 flex justify-between items-center bg-[#1c2732]">
                    <h2 class="text-xl font-bold text-white">{{ t.profile?.editProfileModal?.title || 'Editar Perfil' }}</h2>
                    <button @click="emit('close')" class="text-white/40 hover:text-white transition-colors cursor-pointer">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </header>

                <div class="flex-1 overflow-y-auto p-8 space-y-8">
                    <!-- Banner Upload -->
                    <div class="space-y-3">
                        <label class="text-xs font-black uppercase tracking-widest text-white/40 ml-2">{{ t.profile?.editProfileModal?.bannerLabel || 'Imagen de Banner' }}</label>
                        <ImageUpload 
                            v-model="formData.banner" 
                            aspect="banner" 
                            :preview-url="user.bannerUrl"
                            :label="t.profile?.editProfileModal?.bannerBtn || 'Subir Banner'"
                        />
                    </div>

                    <!-- Profile Pic & Info Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                        <div class="space-y-3">
                            <label class="text-xs font-black uppercase tracking-widest text-white/40 ml-2">{{ t.profile?.editProfileModal?.profilePicLabel || 'Foto de Perfil' }}</label>
                            <ImageUpload 
                                v-model="formData.profile_picture" 
                                aspect="square" 
                                :preview-url="user.iconoUrl"
                                :label="t.profile?.editProfileModal?.profilePicBtn || 'Foto Perfil'"
                            />
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-white/40 ml-2">{{ t.profile?.editProfileModal?.nameLabel || 'Nombre' }}</label>
                                <input 
                                    v-model="formData.name"
                                    type="text" 
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-emerald-500 focus:outline-none transition-all"
                                    :placeholder="t.profile?.editProfileModal?.namePlaceholder || 'Tu nombre'"
                                >
                            </div>
                            <div class="space-y-2">
                                <label class="text-xs font-black uppercase tracking-widest text-white/40 ml-2">{{ t.profile?.editProfileModal?.lastNameLabel || 'Apellidos' }}</label>
                                <input 
                                    v-model="formData.last_name"
                                    type="text" 
                                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-emerald-500 focus:outline-none transition-all"
                                    :placeholder="t.profile?.editProfileModal?.lastNamePlaceholder || 'Tus apellidos'"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <footer class="p-6 border-t border-white/10 bg-[#1c2732] flex justify-end gap-3">
                    <button @click="emit('close')" class="px-6 py-2.5 rounded-xl text-white/60 hover:bg-white/5 transition-all cursor-pointer">
                        {{ t.profile?.editProfileModal?.cancel || 'Cancelar' }}
                    </button>
                    <PrimaryButton class="cursor-pointer" 
                        :text="t.profile?.editProfileModal?.saveChanges || 'Guardar Cambios'" 
                        :loading="loading"
                        @click="saveProfile"
                    />
                </footer>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
}
</style>
