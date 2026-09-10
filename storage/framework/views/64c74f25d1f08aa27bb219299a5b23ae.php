<style>
.banner-partner-strip {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  flex-wrap: wrap;
  padding: 16px 0 10px;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
}

.partner-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  border: 0;
  background: #fff;
  padding: 0;
  border-radius: 8px;
  transition: opacity .15s ease, transform .15s ease;
  line-height: 0;
}

.partner-btn:hover {
  opacity: 0.85;
  transform: translateY(-1px);
}

.partner-btn img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0,0,0,.06);
}

.partner-btn.eitaa {
  width: 42px;
  padding: 4px;
  border-radius: 8px;
  background: #f3f4f6;
}

.partner-btn.eitaa img {
  width: 34px;
  height: 34px;
}

@media (max-width: 760px) {
  .banner-partner-strip {
    gap: 10px;
    padding: 12px 0 8px;
  }
}
</style>

<div class="banner-partner-strip" role="navigation" aria-label="تبلیغات همکاران">
  <a class="partner-btn" href="https://shetabe.ir/" target="_blank" rel="noopener" aria-label="تبلیغات شتاب">
    <img loading="lazy" src="<?php echo e(asset('img/partners/ad-1.svg')); ?>" alt="" width="130" height="130">
  </a>
  <a class="partner-btn" href="https://shetabe.ir/sms/" target="_blank" rel="noopener" aria-label="تبلیغات پیامکی">
    <img loading="lazy" src="<?php echo e(asset('img/partners/send-sms-1.svg')); ?>" alt="" width="130" height="130">
  </a>
  <a class="partner-btn" href="https://shetabe.ir/%D8%A2%DA%AF%D9%87%DB%8C-%D8%A7%D9%86%D8%A8%D9%88%D9%87-%D8%AF%D8%B1-%D8%B3%D8%A7%DB%8C%D8%AA%D9%87%D8%A7%DB%8C-%D8%AA%D8%A8%D9%84%DB%8C%D8%BA%D8%A7%D8%AA%DB%8C/?aff=216" target="_blank" rel="noopener" aria-label="تبلیغات در سایت‌ها">
    <img loading="lazy" src="<?php echo e(asset('img/partners/ads-1a.svg')); ?>" alt="" width="130" height="130">
  </a>
  <a class="partner-btn" href="https://shetabe.ir/google-ads/" target="_blank" rel="noopener" aria-label="تبلیغات گوگل">
    <img loading="lazy" src="<?php echo e(asset('img/partners/google-ads-1.svg')); ?>" alt="" width="130" height="130">
  </a>
  <a class="partner-btn" href="https://crm.talashnet.com/aff.php?aff=209" target="_blank" rel="noopener" aria-label="هاست و دامین">
    <img loading="lazy" src="<?php echo e(asset('img/partners/host-domain-1.svg')); ?>" alt="" width="130" height="130">
  </a>
  <a class="partner-btn" href="https://iranyaft.ir/" target="_blank" rel="noopener" aria-label="ایران یافت">
    <img loading="lazy" src="<?php echo e(asset('img/partners/artsite.svg')); ?>" alt="" width="130" height="130">
  </a>
  <a class="partner-btn eitaa" href="https://eitaa.com/masodaghaie" target="_blank" rel="noopener" aria-label="ای‌تی‌آ">
    <img loading="lazy" src="<?php echo e(asset('img/partners/eita.png')); ?>" alt="" width="32" height="32">
  </a>
</div>
<?php /**PATH /home/hamidreza/Downloads/agahi-readme-update/resources/views/public/partner_buttons.blade.php ENDPATH**/ ?>