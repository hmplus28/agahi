<style>
.partner-strip {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: .5rem;
  flex-wrap: wrap;
  padding: .625rem 0;
  background: #f9fafb;
  border-bottom: 1px solid #f3f4f6;
}
.partner-strip a {
  display: inline-flex;
  align-items: center;
  gap: .3rem;
  padding: .4rem .85rem;
  border-radius: 999px;
  font-size: .78rem;
  font-weight: 600;
  text-decoration: none;
  color: #fff;
  white-space: nowrap;
  position: relative;
  overflow: hidden;
  animation: partnerPop .4s ease-out both;
}
.partner-strip a:nth-child(1) { background: #0d9488; animation-delay: .0s; }
.partner-strip a:nth-child(2) { background: #2563eb; animation-delay: .06s; }
.partner-strip a:nth-child(3) { background: #7c3aed; animation-delay: .12s; }
.partner-strip a:nth-child(4) { background: #ea580c; animation-delay: .18s; }
.partner-strip a:nth-child(5) { background: #16a34a; animation-delay: .24s; }
.partner-strip a:nth-child(6) { background: #dc2626; animation-delay: .30s; }
.partner-strip a:nth-child(7) { background: #0891b2; animation-delay: .36s; }

.partner-strip a::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: linear-gradient(135deg, rgba(255,255,255,.25) 0%, transparent 50%);
  opacity: 0;
  transition: opacity .25s;
}
.partner-strip a:hover::after { opacity: 1; }

.partner-strip a:hover {
  transform: translateY(-2px) scale(1.04);
  box-shadow: 0 4px 14px rgba(0,0,0,.22);
  filter: brightness(1.1);
}
.partner-strip a:active {
  transform: translateY(0) scale(.97);
  box-shadow: 0 1px 4px rgba(0,0,0,.15);
}

.partner-strip a svg {
  width: 14px;
  height: 14px;
  flex-shrink: 0;
  transition: transform .25s;
}
.partner-strip a:hover svg {
  transform: rotate(-8deg) scale(1.15);
}

@keyframes partnerPop {
  0% { opacity: 0; transform: translateY(8px) scale(.9); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 760px) {
  .partner-strip { gap: .35rem; padding: .5rem 0; }
  .partner-strip a { font-size: .7rem; padding: .3rem .6rem; }
}
</style>

<nav class="partner-strip" aria-label="تبلیغات همکاران">
    <a href="https://shetabe.ir/" target="_blank" rel="noopener sponsored">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        تبلیغات شتاب
    </a>
    <a href="https://40sms.ir/" target="_blank" rel="noopener sponsored">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
        تبلیغات پیامکی
    </a>
    <a href="https://shetabe.ir/%D8%A2%DA%AF%D9%87%DB%8C-%D8%A7%D9%86%D8%A8%D9%88%D9%87-%D8%AF%D8%B1-%D8%B3%D8%A7%DB%8C%D8%AA%D9%87%D8%A7%DB%8C-%D8%AA%D8%A8%D9%84%DB%8C%D8%BA%D8%A7%D8%AA%DB%8C/?aff=216" target="_blank" rel="noopener sponsored">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="m8 21 4-4 4 4"/></svg>
        تبلیغات در سایت‌ها
    </a>
    <a href="https://crm.talashnet.com/aff.php?aff=209" target="_blank" rel="noopener sponsored">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M3 7v1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7m0 1a3 3 0 0 0 6 0V7H3l2-4h14l2 4"/><path d="M5 21V10.85M19 21V10.85"/></svg>
        هاست و دامین
    </a>
    <a href="https://iranyaft.ir/" target="_blank" rel="noopener sponsored">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        ایران یافت
    </a>
    <a href="https://brahmat.ir/" target="_blank" rel="noopener sponsored">
        📿 برحمت
    </a>
    <a href="https://modirap.ir/" target="_blank" rel="noopener sponsored">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
        مدیراپ
    </a>
</nav>
