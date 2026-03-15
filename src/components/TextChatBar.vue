<script setup>
    import { useTranslations } from '@/composables/useTranslations'
    const { t } = useTranslations()

</script>

<template>
    <div class="flex my-3 w-full">
        <div class="flex flex-row items-center border border-white rounded-[20px] p-[15px] w-full">
            <input type="text" class="ml-3 w-full outline-none text-base border-none bg-transparent flex-1 text-[#e7e9ea] placeholder-[#8b98a5] placeholder-font-normal" :placeholder="t.nav.search || 'Escribir un mensaje'" />
        </div>
    </div>
</template>