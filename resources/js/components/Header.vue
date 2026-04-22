<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'

const screenWidth = ref(window.innerWidth)

const updateScreenWidth = () => {
  screenWidth.value = window.innerWidth
}

onMounted(() => {
  window.addEventListener('resize', updateScreenWidth)
})

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateScreenWidth)
})
</script>

<template>
  <nav id="app__header" class=" select-none flex-no-wrap relative flex w-full shadow-lg bg-primary-foreground z-2">
    <div class="flex items-center px-8 py-4 md:px-9 md:py-4">
      <div 
        class="flex-shrink-0"
        :class="screenWidth > 580 ? 'w-20 h-20' : 'w-14 h-14'"
      >
        <img 
          src="../../assets/temp-logo.svg" 
          alt="Logo"
          class="w-full h-full object-contain"
        />
      </div>
      
      <div 
        v-if="screenWidth >= 369"
        class="flex flex-col ml-3 cursor-pointer leading-tight text-accent"
      >
        <div class="text-left font-sans font-bold text-4xl">
          <span class=" text-primary">Vend</span>
          <span class=" text-accent">Aid</span>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
@media (max-width: 580px) {
  header > div {
    padding: 0.5rem 1.25rem;
  }
}
</style>