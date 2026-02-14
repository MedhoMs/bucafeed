<script setup>
import NavBar from './components/NavBar.vue'
</script>

<!--Desde la App se maneja todo lo que se va a ver, usando <router-view /> es lo que me permite elegir que vista va a ver el usuario-->
<!--Ir a /router/index.js para ver la estructura del router-->
<template>
    <div id="app">
        <main class="main-content">
            <router-view />
        </main>
    </div>
</template>

<style>
    ::-webkit-scrollbar { 
        display: none; 
    }
</style>