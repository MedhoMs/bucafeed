<script setup>
    import { ref } from 'vue'
    import { useTranslations } from '@/composables/useTranslations'
    
    const { t } = useTranslations()
    const message = ref('')
    const fileInput = ref(null)
    const selectedFile = ref(null)
    
    const emit = defineEmits(['sendMessage'])

    function triggerFileInput() {
        fileInput.value.click()
    }

    function onFileChange(e) {
        const file = e.target.files[0]
        if (!file) return
        
        const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf']
        if (!validTypes.includes(file.type)) {
            alert('Solo se permiten imágenes y PDFs')
            fileInput.value.value = ''
            return
        }
        
        selectedFile.value = file
    }

    function sendMessage() {
        const text = message.value.trim()
        
        if (selectedFile.value) {
            const fileUrl = URL.createObjectURL(selectedFile.value)
            const type = selectedFile.value.type.startsWith('image/') ? 'image' : 'pdf'
            
            emit('sendMessage', { 
                content: fileUrl, 
                type: type, 
                fileName: selectedFile.value.name 
            })
            
            selectedFile.value = null
            fileInput.value.value = '' 
        } else if (text) {
            emit('sendMessage', { 
                content: text, 
                type: 'text' 
            })
            message.value = ''
        }
    }
</script>

<template>
    <div class="flex my-3 w-full">
        <div class="flex items-center border border-white rounded-[20px] p-3.75 w-50 lg:w-full">
            <input 
                ref="fileInput"
                type="file" 
                id="telamofile" 
                name="telamofile" 
                class="hidden"
                accept="image/*,application/pdf"
                @change="onFileChange"
            >
            <svg @click="triggerFileInput" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="hover:bg-[#152027] rounded-12 cursor-pointer icon icon-tabler icons-tabler-filled icon-tabler-plus"><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M12 4a1 1 0 0 1 1 1v6h6a1 1 0 0 1 0 2h-6v6a1 1 0 0 1 -2 0v-6h-6a1 1 0 0 1 0 -2h6v-6a1 1 0 0 1 1 -1" /></svg>
            
            <input 
                v-model="message"
                type="text" 
                id="chatBar" 
                class="ml-3 w-full outline-hidden text-base border-none bg-transparent flex-1 text-[#e7e9ea] placeholder-[#8b98a5] placeholder-font-normal" 
                :placeholder="selectedFile ? `Archivo: ${selectedFile.name}` : (t.nav.search || 'Escribir un mensaje')"
                @keydown.enter="sendMessage"
            />
            <svg @click="sendMessage" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-square-arrow-right cursor-pointer "><path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M19 2a3 3 0 0 1 3 3v14a3 3 0 0 1 -3 3h-14a3 3 0 0 1 -3 -3v-14a3 3 0 0 1 3 -3zm-6.387 5.21a1 1 0 0 0 -1.32 .083l-.083 .094a1 1 0 0 0 .083 1.32l2.292 2.293h-5.585l-.117 .007a1 1 0 0 0 .117 1.993h5.585l-2.292 2.293l-.083 .094a1 1 0 0 0 1.497 1.32l4 -4l.073 -.082l.074 -.104l.052 -.098l.044 -.11l.03 -.112l.017 -.126l.003 -.075l-.007 -.118l-.029 -.148l-.035 -.105l-.054 -.113l-.071 -.111a1.008 1.008 0 0 0 -.097 -.112l-4 -4z" /></svg>
        </div>
    </div>
</template>