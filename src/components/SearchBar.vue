<script setup>
    import { useTranslations } from '../composables/useTranslations'
    const { t } = useTranslations()

</script>

<template>
    <div id="searchBar">
        <div id="bar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
            <input type="text" :placeholder="t.nav.search || 'Buscar'">
        </div>
    </div>
</template>

<style scoped>
    #bar {
        display: flex;
        flex-direction: row;
        background-color: #2a4a5a;
        border-radius: 20px; 
        padding: 15px 15px;
        min-width: 1500px;
        align-items: center;
    }

    #bar:hover {
        background-color: #334d5e; 
        border-color: #334d5e;
    }

    #bar:focus-within {
        background-color: #334d5e;
        border-color: #1da1f2; 
        outline: 1px solid #1da1f2;
    }

    #searchBar {
        display: flex;
        margin: 12px 0px;
        width: 100%;
    }

    input {
        margin-left: 12px;
        width: 100%;
        outline: none;
        font-size: 16px;
        border: none;
        background: transparent;
        flex: 1;
        color: #e7e9ea; /* Texto principal */
    }

    input::placeholder {
        color: #8b98a5;
        font-weight: normal;
    }

    .icon {
        flex-shrink: 0;
        color: #8b98a5; /* Icono gris */
    }
</style>