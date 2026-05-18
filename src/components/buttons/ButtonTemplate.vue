
<script setup>
const props = defineProps({
  texto: {
    type: String,
    default: 'Seguir'
  },
  accion: {
    type: Function,
    default: () => console.log('Botón clickeado')
  }
})

const manejarClick = () => {
  props.accion()
}
</script>

<template>
  <button class="boton-unico cursor-pointer" @click="manejarClick">
    {{ props.texto }}
  </button>
</template>

<style scoped>

.boton-unico {
  padding: 0.375rem 1rem;
  border-radius: 9999px;
  border: 1px solid white;
  background: transparent;
  color: white;
  font-weight: 700;
  font-size: 0.75rem;
  flex-shrink: 0;
  transition: all 0.2s ease;
  cursor: pointer;
}

.boton-unico:hover {
  background-color: #eff6ff;
  color: #60a5fa;
  border: 1px solid #60a5fa;
}
</style>