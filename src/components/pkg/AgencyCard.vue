<template>
  <div class="entity-card">
    <div class="entity-card__top">
      <div class="entity-card__avatar">
        <img v-if="img" :src="img" :alt="name" />
        <span v-else>{{ name[0] }}</span>
      </div>
      <div class="entity-card__info">
        <div class="entity-card__label">{{ entityLabel }}</div>
        <div class="entity-card__name">{{ name }}</div>
        <div class="entity-card__rating">
          <span class="star">★</span> {{ rating }}
          <span class="entity-card__reviews">· {{ reviews }} reviews</span>
        </div>
      </div>
    </div>

    <p class="entity-card__bio">{{ bio }}</p>

    <button class="entity-card__contact" @click="$emit('contact')">
      💬 Contact {{ entityLabel }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  name:        { type: String, required: true  },
  bio:         { type: String, default: ''     },
  img:         { type: String, default: ''     },
  rating:      { type: Number, required: true  },
  reviews:     { type: Number, required: true  },
  entityLabel: { type: String, default: 'Agency' },
})
defineEmits(['contact'])
</script>

<style scoped>
.entity-card {
  background: var(--gray-50); border-radius: var(--radius);
  padding: 24px; border: 1px solid var(--gray-200);
}

.entity-card__top {
  display: flex; align-items: center; gap: 16px; margin-bottom: 16px;
}

.entity-card__avatar {
  width: 60px; height: 60px; border-radius: 50%; overflow: hidden; flex-shrink: 0;
  background: linear-gradient(135deg, var(--teal), var(--indigo));
  display: flex; align-items: center; justify-content: center;
  font-family: 'Fraunces', serif; font-size: 1.4rem; font-weight: 700; color: #fff;
}
.entity-card__avatar img { width: 100%; height: 100%; object-fit: cover; }

.entity-card__label  { font-size: .72rem; font-weight: 700; color: var(--teal); text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
.entity-card__name   { font-family: 'Fraunces', serif; font-size: 1.1rem; font-weight: 700; color: var(--indigo); margin-bottom: 4px; }
.entity-card__rating { display: flex; align-items: center; gap: 4px; font-size: .84rem; font-weight: 600; color: var(--indigo); }
.entity-card__reviews{ color: var(--gray-400); font-weight: 400; }

.entity-card__bio {
  font-size: .88rem; color: var(--gray-600); line-height: 1.7; margin-bottom: 18px;
}

.entity-card__contact {
  width: 100%; padding: 12px; border: 1.5px solid var(--gray-200);
  border-radius: 12px; background: var(--white);
  font-family: 'DM Sans', sans-serif; font-size: .9rem; font-weight: 600;
  color: var(--indigo); cursor: pointer; transition: all var(--transition);
}
.entity-card__contact:hover { border-color: var(--teal); color: var(--teal); }
</style>