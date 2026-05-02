<template>
  <div class="auth-left">
    <div class="auth-left__inner">
      <div class="auth-card">
        <img
          src="https://images.pexels.com/photos/4198029/pexels-photo-4198029.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1"
          alt="Travel destination"
          class="auth-card__img"
        />
        <!-- https://images.pexels.com/photos/2850287/pexels-photo-2850287.jpeg -->
        <div class="auth-card__overlay" />
      </div>
    </div>
  </div>
</template>

<script setup>
// Presentational background component
</script>

<style scoped>
.auth-left {
  background: var(--indigo);
  position: relative;
  overflow: hidden;
  display: flex;
  height: 100%;
  width: 100%;
}

.auth-left__inner {
  position: relative;
  width: 100%;
  height: 100%;
}

.auth-card {
  width: 100%;
  height: 100%;
  position: relative;
}

.auth-card__img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.auth-card__overlay {
  position: absolute;
  inset: 0;
  /* More intense on the left for text visibility */
  background: linear-gradient(to right, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.6) 30%, rgba(15, 23, 42, 0.2) 100%);
  z-index: 1;
}
</style>
