<script setup>
    import { useTranslations } from '@/composables/useTranslations'
    import TextChatBar from './TextChatBar.vue';
    const { t } = useTranslations()

</script>

<template>
    <div class="flex flex-col w-full h-full overflow-hidden">
        <div class="flex flex-col flex-1 min-h-0 pb-4 pr-2 overflow-y-auto">
            <div class="flex flex-col justify-end min-h-max space-y-4">
                <template v-for="n in 8" :key="n">
                    <div id="msgTeacher">
                        <p class="ml-2 mb-1 text-gray-300">Profesor</p>
                        <p id="msg" class="rounded-2xl w-[500px] bg-[#1e2e38] p-4 text-white">Mensaje del profesor</p>
                    </div>
            
                    <div id="msgStudent" class="self-end flex flex-col items-end">
                        <p class="mr-2 mb-1 text-gray-300">Alumno</p>
                        <p id="msg" class="rounded-2xl w-[500px] bg-[#2a4a5a] p-4 text-white">Mensaje del alumno</p>
                    </div>
                </template>
            </div>
        </div>

        <div class="pt-2 shrink-0">
            <TextChatBar />
        </div>
    </div>
</template>

<style scoped>

</style>