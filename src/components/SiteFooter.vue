<template>
  <footer class="site-footer">
    <div class="footer-grid">

      <!-- Brand column -->
      <div class="footer-brand">
        <div class="logo">Voya<span>go</span></div>
        <p>A modern travel marketplace connecting tourists, agencies, and service providers worldwide.</p>
        <div class="footer-socials">
          <a class="social-btn" href="#" aria-label="Twitter">𝕏</a>
          <a class="social-btn" href="#" aria-label="LinkedIn">in</a>
          <a class="social-btn" href="#" aria-label="Facebook">📘</a>
          <a class="social-btn" href="#" aria-label="Instagram">📸</a>
        </div>
      </div>

      <!-- Link columns -->
      <div v-for="col in columns" :key="col.title">
        <h4 class="footer-col-title">{{ col.title }}</h4>
        <ul class="footer-links">
          <li v-for="link in col.links" :key="link"><a href="#">{{ link }}</a></li>
        </ul>
      </div>

    </div>

    <div class="footer-bottom">
      <span>© {{ year }} Voyago. All rights reserved.</span>
      <div class="footer-bottom-links">
        <a href="#">Privacy</a>
        <a href="#">Terms</a>
        <a href="#">Cookies</a>
      </div>
    </div>
  </footer>
</template>

<script setup>
const year = new Date().getFullYear()

const columns = [
  {
    title: 'Explore',
    links: ['Destinations', 'Travel Packages', 'Services', 'Special Offers', 'Reviews'],
  },
  {
    title: 'For Partners',
    links: ['Register Agency', 'Add Services', 'Partner Dashboard', 'Approval Process', 'Pricing'],
  },
  {
    title: 'Company',
    links: ['About Us', 'Careers', 'Press', 'Contact', 'Help Center'],
  },
]
</script>

<style scoped>
.site-footer {
  background: var(--indigo);
  color: rgba(255,255,255,.70);
  padding: 72px 5% 40px;
}

.footer-grid {
  display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px;
  padding-bottom: 56px; border-bottom: 1px solid rgba(255,255,255,.10);
}

/* Brand */
.logo {
  font-family: 'Fraunces', serif;
  font-size: 1.6rem; font-weight: 700; color: #fff; letter-spacing: -.5px;
  display: block; margin-bottom: 16px;
}
.logo span { color: var(--coral); }

.footer-brand p { font-size: .88rem; line-height: 1.7; max-width: 260px; }

.footer-socials { display: flex; gap: 12px; margin-top: 24px; }
.social-btn {
  width: 36px; height: 36px; border-radius: 10px;
  background: rgba(255,255,255,.10);
  display: flex; align-items: center; justify-content: center;
  font-size: .95rem; cursor: pointer;
  transition: background var(--transition);
  text-decoration: none; color: inherit;
}
.social-btn:hover { background: rgba(255,255,255,.20); }

/* Link columns */
.footer-col-title {
  font-family: 'Fraunces', serif; font-size: 1rem; font-weight: 600;
  color: #fff; margin-bottom: 20px;
}
.footer-links { list-style: none; display: flex; flex-direction: column; gap: 10px; }
.footer-links a { font-size: .88rem; transition: color var(--transition); }
.footer-links a:hover { color: var(--teal); }

/* Bottom */
.footer-bottom {
  padding-top: 32px;
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 16px; font-size: .82rem;
}
.footer-bottom-links { display: flex; gap: 24px; }
.footer-bottom-links a { transition: color var(--transition); }
.footer-bottom-links a:hover { color: var(--teal); }

@media (max-width: 900px)  { .footer-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px)  { .footer-grid { grid-template-columns: 1fr; } }
</style>
