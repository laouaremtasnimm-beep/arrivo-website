<template>
  <div class="review-card">
    <div class="review-card__top">
      <div class="review-card__author">
        <div class="review-card__avatar">{{ review.name[0] }}</div>
        <div>
          <div class="review-card__name">{{ review.name }}</div>
          <div class="review-card__location">{{ review.location }}</div>
        </div>
      </div>
      <div class="review-card__right">
        <div class="review-card__stars">
          <span class="star" v-for="i in review.rating" :key="i">★</span>
          <span class="star star--empty" v-for="i in 5 - review.rating" :key="'e'+i">★</span>
        </div>
        <div class="review-card__date">{{ review.date }}</div>
      </div>
    </div>
    <p class="review-card__text">"{{ review.text }}"</p>
  </div>
</template>

<script setup>
defineProps({
  review: { type: Object, required: true },
})
</script>

<style scoped>
.review-card {
  background: var(--white); border-radius: var(--radius);
  padding: 22px; box-shadow: var(--shadow);
  transition: transform var(--transition);
}
.review-card:hover { transform: translateY(-2px); }

.review-card__top {
  display: flex; align-items: flex-start; justify-content: space-between;
  gap: 12px; margin-bottom: 14px;
}
.review-card__author { display: flex; align-items: center; gap: 12px; }
.review-card__avatar {
  width: 40px; height: 40px; border-radius: 50%; flex-shrink: 0;
  background: var(--sand); color: var(--indigo);
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
}
.review-card__name     { font-weight: 600; font-size: .9rem; color: var(--indigo); }
.review-card__location { font-size: .76rem; color: var(--gray-400); margin-top: 2px; }

.review-card__right { text-align: right; flex-shrink: 0; }
.review-card__stars { display: flex; gap: 2px; justify-content: flex-end; margin-bottom: 3px; }
.star       { color: #FFB400; font-size: .82rem; }
.star--empty{ color: var(--gray-200); }
.review-card__date { font-size: .74rem; color: var(--gray-400); }

.review-card__text {
  font-size: .88rem; color: var(--gray-600); line-height: 1.7; font-style: italic;
}
</style>