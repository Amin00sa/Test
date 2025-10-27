<template>
  <div>
    <div v-if="loading" class="spinner">
      <!-- ton spinner ici -->
      <p>Chargement... {{ remaining }}s</p>
    </div>
    <button @click="startSpinner">Démarrer spinner (60s)</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      loading: false,
      remaining: 60,
      startTime: null,
      timer: null
    }
  },
  methods: {
    startSpinner() {
      this.loading = true
      this.startTime = Date.now()
      this.remaining = 60

      this.timer = setInterval(() => {
        const elapsed = Math.floor((Date.now() - this.startTime) / 1000)
        this.remaining = 60 - elapsed
        if (this.remaining <= 0) {
          clearInterval(this.timer)
          this.loading = false
          this.remaining = 0
        }
      }, 1000)
    }
  },
  beforeDestroy() {
    if (this.timer) clearInterval(this.timer)
  }
}
</script>

<style scoped>
.spinner {
  animation: spin 1s linear infinite;
}
@keyframes spin {
  100% {
    transform: rotate(360deg);
  }
}
</style>
