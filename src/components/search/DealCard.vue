<template>

  <div class="deal-card">
    <div class="deal-discount">
      <span class="num">{{ offer.tag }}</span>
    </div>
<div class="deal-img-placeholder"></div>
    <div class="deal-body">
      <h3>{{ offer.title }}</h3>
      <p>{{ offer.desc }}</p>
    </div>

    <div class="deal-footer">
      <button class="btn btn-teal" @click="$emit('select', offer)">
        View deal →
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  offer: Object
})
</script>

<style scoped>
.deal-card {
  background: var(--white);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
min-height: 320px;

  padding: 18px;

  display: flex;
  flex-direction: column;
  justify-content: space-between;

  transition: transform var(--transition), box-shadow var(--transition);
}

.deal-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-md);
}


.deal-discount {
  font-weight: bold;
  color: var(--coral);
  font-size: 1.4rem;
}
.deal-body {
  flex: 1
}

.deal-body h3 {
  font-size: 1.1rem;
  font-weight: 700;
}

.deal-body p {
  font-size: 0.9rem;
  color: var(--gray-500);
}


</style>