<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('title');        // {"uk": "...", "en": "..."}
            $table->json('content');      // {"uk": "...", "en": "..."}
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->timestamps();
        });

        $pages = [
            [
                'slug' => 'shipping-payment',
                'title' => ['uk' => 'Оплата та доставка', 'en' => 'Payment & Shipping'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Умови доставки та оплати в FilkxTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Ми прагнемо зробити процес покупки максимально простим і комфортним. Доставка товарів здійснюється по всій території України надійними поштовими операторами.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Способи доставки</h3>
    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
      <li><strong>Нова Пошта:</strong> Доставка у відділення або поштомат за 1–2 дні. Також доступна адресна доставка кур\'єром.</li>
      <li><strong>Укрпошта:</strong> Доставка у відділення протягом 2–4 робочих днів. Економічний варіант.</li>
      <li><strong>Самовивіз:</strong> Безкоштовно з наших фірмових магазинів FilkxTech у Києві, Львові та Одесі.</li>
    </ul>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Способи оплати</h3>
    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
      <li><strong>Оплата карткою на сайті:</strong> Миттєва та безпечна оплата через Monobank (Visa / Mastercard / Apple Pay / Google Pay).</li>
      <li><strong>Накладений платіж:</strong> Оплата готівкою або карткою при отриманні посилки у відділенні перевізника.</li>
      <li><strong>Безготівковий розрахунок:</strong> Оплата за рахунком-фактурою для юридичних та фізичних осіб.</li>
    </ul>
  </div>
</div>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">FilkxTech Delivery & Payment Terms</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">We strive to make the purchasing process as simple and comfortable as possible. Delivery is carried out throughout Ukraine by reliable postal services.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Shipping Methods</h3>
    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
      <li><strong>Nova Poshta:</strong> Delivery to branch or post machine in 1–2 days. Courier home delivery is also available.</li>
      <li><strong>Ukrposhta:</strong> Delivery to branch in 2–4 business days. Cost-effective choice.</li>
      <li><strong>In-store Pickup:</strong> Free pickup from our FilkxTech stores in Kyiv, Lviv, and Odesa.</li>
    </ul>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Payment Methods</h3>
    <ul class="space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
      <li><strong>Online Card Payment:</strong> Instant and secure checkout via Monobank (Visa / Mastercard / Apple Pay / Google Pay).</li>
      <li><strong>Cash on Delivery (COD):</strong> Pay in cash or by card upon receiving the parcel at the courier branch.</li>
      <li><strong>Bank Invoice Transfer:</strong> Payment via invoice details (IBAN) for legal entities and individuals.</li>
    </ul>
  </div>
</div>'
                ]
            ],
            [
                'slug' => 'warranty-returns',
                'title' => ['uk' => 'Гарантія та обмін', 'en' => 'Warranty & Returns'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Гарантійні зобов\'язання та повернення товарів</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Всі товари, придбані в FilkxTech, постачаються з офіційною гарантією від виробника та нашого магазину. Ми дбаємо про ваш спокій, тому гарантуємо легкий процес обміну та повернення.</p>
<div class="space-y-6 mb-8">
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Офіційна гарантія</h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Термін гарантійного обслуговування більшості товарів становить від 12 до 24 місяців. Гарантійний талон додається до кожного замовлення. У разі несправності ви можете звернутися до офіційних сервісних центрів або безпосередньо до нас.</p>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Повернення протягом 14 днів</h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Відповідно до Закону України «Про захист прав споживачів», ви маєте право повернути або обміняти товар протягом 14 днів з моменту покупки, якщо:</p>
    <ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mt-2.5">
      <li>Товар не має слідів використання та пошкоджень.</li>
      <li>Збережено товарний вигляд, ярлики, заводські плівки та пломби.</li>
      <li>Товар повністю укомплектований та в оригінальній неушкодженій упаковці.</li>
      <li>Збережено розрахунковий документ (чек або накладна).</li>
    </ul>
  </div>
</div>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Warranty & Return Policies</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">All products purchased from FilkxTech carry an official warranty from the manufacturer and our shop. We prioritize your peace of mind and offer hassle-free return and exchange procedures.</p>
<div class="space-y-6 mb-8">
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Official Warranty Coverage</h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">The warranty period for most items ranges between 12 and 24 months. A warranty card is shipped with every package. In case of malfunctions, you can bring the device to certified partner service centers or directly to us.</p>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">14-Day Returns & Exchanges</h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Under Ukrainian Consumer Protection Law, you can exchange or return a product within 14 days of purchase under the following conditions:</p>
    <ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mt-2.5">
      <li>The product shows no signs of wear, usage, or damage.</li>
      <li>Original package style, tags, labels, and protective films are intact.</li>
      <li>The product is fully complete in its original undamaged box.</li>
      <li>Sales receipt, check, or invoice documentation is provided.</li>
    </ul>
  </div>
</div>'
                ]
            ],
            [
                'slug' => 'service',
                'title' => ['uk' => 'Сервісний центр', 'en' => 'Service Center'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Професійний сервіс та ремонт FilkxTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Наш власний авторизований сервісний центр здійснює гарантійний та післягарантійний ремонт будь-якої складності. Ми використовуємо оригінальні запчастини та надаємо гарантію на всі види ремонтних робіт.</p>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
  <div class="p-5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl text-center">
    <span class="material-symbols-outlined text-[36px] text-[#00a046] mb-2">build</span>
    <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Швидка діагностика</h4>
    <p class="text-xs text-zinc-500">Визначаємо причину несправності протягом 24 годин.</p>
  </div>
  <div class="p-5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl text-center">
    <span class="material-symbols-outlined text-[36px] text-[#00a046] mb-2">check_circle</span>
    <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Оригінальні деталі</h4>
    <p class="text-xs text-zinc-500">Прямі поставки комплектуючих від світових брендів.</p>
  </div>
  <div class="p-5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl text-center">
    <span class="material-symbols-outlined text-[36px] text-[#00a046] mb-2">shield</span>
    <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Гарантія на ремонт</h4>
    <p class="text-xs text-zinc-500">Надаємо додаткову гарантію на замінені деталі до 6 місяців.</p>
  </div>
</div>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">FilkxTech Certified Repair Service</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Our in-house authorized service center offers warranty and post-warranty repairs of any complexity. We utilize original parts and supply a warranty for all completed repair operations.</p>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
  <div class="p-5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl text-center">
    <span class="material-symbols-outlined text-[36px] text-[#00a046] mb-2">build</span>
    <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Quick Diagnostics</h4>
    <p class="text-xs text-zinc-500">We pinpoint the cause of the breakdown within 24 hours.</p>
  </div>
  <div class="p-5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl text-center">
    <span class="material-symbols-outlined text-[36px] text-[#00a046] mb-2">check_circle</span>
    <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Original Components</h4>
    <p class="text-xs text-zinc-500">Direct component supply from leading global tech giants.</p>
  </div>
  <div class="p-5 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl text-center">
    <span class="material-symbols-outlined text-[36px] text-[#00a046] mb-2">shield</span>
    <h4 class="font-bold text-zinc-900 dark:text-white mb-2">Repair Warranty</h4>
    <p class="text-xs text-zinc-500">Enjoy up to 6 months of additional warranty on replaced components.</p>
  </div>
</div>'
                ]
            ],
            [
                'slug' => 'services',
                'title' => ['uk' => 'Додаткові послуги', 'en' => 'Additional Services'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Професійні послуги від фахівців FilkxTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Окрім продажу першокласної техніки, ми надаємо комплекс додаткових послуг для налаштування, захисту та оптимізації роботи ваших нових пристроїв.</p>
<ul class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400 mb-8">
  <li class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Встановлення та налаштування ПЗ:</strong> Професійне налаштування операційних систем, офісних програм та спеціалізованого софту на ваші пристрої.
  </li>
  <li class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Поклейка захисного скла та плівок:</strong> Ідеальне нанесення захисних аксесуарів на смартфони, планшети чи смарт-годинники без пилу та бульбашок.
  </li>
  <li class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Перенесення даних:</strong> Безпечне та повне перенесення ваших контактів, фотографій, налаштувань та додатків зі старого телефону на новий.
  </li>
</ul>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">FilkxTech Expert Services</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">In addition to selling top-tier equipment, we offer a range of specialized services to customize, protect, and optimize your new tech assets.</p>
<ul class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400 mb-8">
  <li class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Software Configuration:</strong> Professional installation of operating systems, office packages, and productivity software tools.
  </li>
  <li class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Screen Protectors & Films Application:</strong> Perfect dust-free installation of protective glass onto smartphones, tablets, or smartwatches.
  </li>
  <li class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Data Migration:</strong> Complete and safe migration of contacts, documents, multimedia files, and configurations from your old device to the new one.
  </li>
</ul>'
                ]
            ],
            [
                'slug' => 'installments',
                'title' => ['uk' => 'Розстрочка та кредит', 'en' => 'Installments & Credit'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Вигідна розстрочка та оплата частинами</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Купуйте техніку та електроніку сьогодні, а сплачуйте поступово. FilkxTech пропонує вигідні кредитні умови спільно з провідними українськими банками.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Оплата частинами від Monobank</h3>
    <ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400">
      <li>0% переплати та прихованих комісій.</li>
      <li>Оформлення за 1 хвилину у мобільному додатку.</li>
      <li>Розстрочка на термін до 12 місяців.</li>
      <li>Потрібен лише ліміт по Оплаті частинами.</li>
    </ul>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Покупка частинами від ПриватБанк</h3>
    <ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400">
      <li>Швидке схвалення без документів і довідок.</li>
      <li>Розподіл платежу на 3–24 рівні частини.</li>
      <li>Перший внесок списується в день покупки.</li>
    </ul>
  </div>
</div>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Flexible Installment & Credit Plans</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Purchase premium electronics today and pay gradually. FilkxTech offers low-interest financing solutions in partnership with leading Ukrainian banks.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">Monobank Purchase in Installments</h3>
    <ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400">
      <li>0% interest rate and no hidden fees.</li>
      <li>Apply in 1 minute inside the Monobank app.</li>
      <li>Terms available for up to 12 months.</li>
      <li>Requires active credit limit.</li>
    </ul>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-lg font-bold text-zinc-900 dark:text-white mb-3">PrivatBank Payment in Installments</h3>
    <ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400">
      <li>Instant authorization with no papers required.</li>
      <li>Split payments into 3 to 24 equal installments.</li>
      <li>The first payment is charged on the purchase day.</li>
    </ul>
  </div>
</div>'
                ]
            ],
            [
                'slug' => 'filkx-exchange',
                'title' => ['uk' => 'Filkx Обмін (Trade-in)', 'en' => 'Filkx Exchange (Trade-in)'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Програма вигідного обміну Filkx Обмін</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Здавайте старі смартфони, планшети чи ноутбуки та отримуйте знижку до 90% на придбання нових гаджетів у нашому магазині за програмою Trade-in.</p>
<div class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400 mb-8">
  <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Крок 1. Оцінка:</strong> Принесіть пристрій у будь-який магазин FilkxTech. Наш експерт оцінить його стан за 5 хвилин.
  </div>
  <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Крок 2. Знижка:</strong> Отримайте сертифікат або пряму знижку, еквівалентну оціночній вартості пристрою.
  </div>
  <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Крок 3. Покупка:</strong> Виберіть будь-який новий товар у нашому магазині та оплатіть різницю.
  </div>
</div>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Filkx Trade-in Exchange Program</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Exchange your older smartphone, tablet, or laptop and secure up to a 90% discount on brand new tech devices using our Trade-in program.</p>
<div class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400 mb-8">
  <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Step 1. Appraisal:</strong> Bring your device to any FilkxTech retail store. Our tech assistants evaluate its value in 5 minutes.
  </div>
  <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Step 2. Discount:</strong> Receive a promotional certificate equivalent to the trade-in valuation of the appraised model.
  </div>
  <div class="p-4 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-zinc-150 dark:border-zinc-800">
    <strong>Step 3. Purchase:</strong> Choose any new product on the catalog shelf and pay only the difference.
  </div>
</div>'
                ]
            ],
            [
                'slug' => 'contacts',
                'title' => ['uk' => 'Контакти', 'en' => 'Contacts'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Зв\'яжіться з нами</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Ми завжди раді допомогти вам з будь-якими запитаннями. Зв\'яжіться з нами телефоном, електронною поштою або завітайте до нашого головного офісу.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
  <div class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400">
    <p><strong>Адреса головного офісу:</strong> вул. Хрещатик, 15, Київ, 01001, Україна</p>
    <p><strong>Гаряча лінія (Безкоштовно):</strong> 0 800 300 100</p>
    <p><strong>Для юридичних осіб:</strong> +380 (44) 123-45-67</p>
    <p><strong>Email служби підтримки:</strong> support@tech.filkx.com</p>
    <p><strong>Email для партнерів:</strong> b2b@tech.filkx.com</p>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-base font-bold text-zinc-900 dark:text-white mb-3">Графік роботи</h3>
    <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
      <li><strong>Служба підтримки:</strong> щодня з 08:00 до 22:00</li>
      <li><strong>Головний офіс:</strong> Пн-Пт з 09:00 до 18:00</li>
      <li><strong>Магазини:</strong> щодня з 10:00 до 21:00</li>
    </ul>
  </div>
</div>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Contact Us</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">We are always happy to assist you with any inquiries. Get in touch with us by phone, email, or visit our central office.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
  <div class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400">
    <p><strong>Headquarters Address:</strong> Khreshchatyk St, 15, Kyiv, 01001, Ukraine</p>
    <p><strong>Hotline Support (Free):</strong> 0 800 300 100</p>
    <p><strong>Corporate Inquiries:</strong> +380 (44) 123-45-67</p>
    <p><strong>Support Email:</strong> support@tech.filkx.com</p>
    <p><strong>Partnership Email:</strong> b2b@tech.filkx.com</p>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-base font-bold text-zinc-900 dark:text-white mb-3">Opening Hours</h3>
    <ul class="space-y-2 text-sm text-zinc-600 dark:text-zinc-400">
      <li><strong>Hotline support:</strong> daily 08:00 - 22:00</li>
      <li><strong>Central Office:</strong> Mon-Fri 09:00 - 18:00</li>
      <li><strong>Retail stores:</strong> daily 10:00 - 21:00</li>
    </ul>
  </div>
</div>'
                ]
            ],
            [
                'slug' => 'about',
                'title' => ['uk' => 'Про нас', 'en' => 'About Us'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Про компанію FilkxTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Заснована у 2010 році, компанія FilkxTech виросла з невеликого інтернет-магазину в одного з провідних рітейлерів електроніки та побутової техніки в Україні. Наша місія — надавати клієнтам першокласну оригінальну техніку з безкомпромісним рівнем обслуговування.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Ми віримо, що технології повинні полегшувати життя та робити його яскравішим. Саме тому ми відбираємо тільки перевірені бренди, забезпечуємо повну офіційну гарантію, організовуємо надшвидку логістику та власну кваліфіковану службу підтримки клієнтів.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">About FilkxTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Founded in 2010, FilkxTech grew from a small startup online e-store into one of the leading consumer electronics retailers in Ukraine. Our ultimate mission is supplying premium authentic technology combined with unmatched support.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">We believe that technology should empower people and make life more exciting. This is why we partner with premium manufacturers, provide extensive guarantees, organize super-fast shipping, and run our own customer care hotline.</p>'
                ]
            ],
            [
                'slug' => 'terms',
                'title' => ['uk' => 'Умови використання сайту', 'en' => 'Terms of Use'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Угода користувача та правила сайту</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Дана Угода користувача встановлює правила та умови використання веб-сайту FilkxTech. Користуючись нашим ресурсом, ви погоджуєтеся з цими умовами в повному обсязі.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ми залишаємо за собою право в будь-який час вносити зміни до цін, асортименту та контенту нашого магазину без попереднього повідомлення. Всі опубліковані матеріали захищені законами про інтелектуальну власність.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Terms & Conditions of Website Usage</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">This User Agreement defines the conditions for browsing and utilizing the FilkxTech e-commerce website. By navigating this site, you accept these terms in full.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">We reserve the right to amend catalog prices, stock availability details, and textual materials at any moment without warning. All copyrighted elements are protected under intellectual property acts.</p>'
                ]
            ],
            [
                'slug' => 'careers',
                'title' => ['uk' => 'Вакансії', 'en' => 'Careers'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Кар\'єра в FilkxTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Ми постійно зростаємо та шукаємо талановитих людей, закоханих у технології. У нашій команді ви знайдете простір для професійного зростання, цікаві завдання та дружню атмосферу.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">Якщо ви хочете приєднатися до нас — надсилайте своє резюме на адресу <strong>hr@tech.filkx.com</strong>.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Work at FilkxTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">We are constantly expanding and looking for talented individuals passionate about consumer technology. In our team, you will find opportunities for professional development, ambitious goals, and an supportive ecosystem.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">If you wish to join us, send your CV to <strong>hr@tech.filkx.com</strong>.</p>'
                ]
            ],
            [
                'slug' => 'franchising',
                'title' => ['uk' => 'Франчайзинг', 'en' => 'Franchising'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Франшиза FilkxTech: побудуйте бізнес разом з нами</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Відкрийте фірмовий магазин FilkxTech у своєму місті. Ми пропонуємо перевірену бізнес-модель, прямі поставки техніки та повну маркетингову й технічну підтримку франчайзі.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">З питань франчайзингу пишіть на <strong>franchise@tech.filkx.com</strong>.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">FilkxTech Franchising Partnership</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Launch a certified FilkxTech store in your area. We provide a validated business model, direct electronics distribution, and complete marketing and operational guidance.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 leading-relaxed">For franchise proposals, email us at <strong>franchise@tech.filkx.com</strong>.</p>'
                ]
            ],
            [
                'slug' => 'promo-rules',
                'title' => ['uk' => 'Офіційні правила акцій', 'en' => 'Official Promotion Rules'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Правила проведення акцій та розіграшів</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Всі акційні пропозиції, знижки та розіграші в мережі FilkxTech проводяться відповідно до встановлених офіційних правил. Організатором акцій виступає ТОВ «ФілксТех Преміум».</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Умови нарахування бонусів, подарунків чи надання спеціальних цін вказуються в детальній оферті кожної окремої рекламної кампанії на головній сторінці.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Official Promotion & Sweepstakes Rules</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">All promotional discount codes, rewards, and product giveaways across the FilkxTech network are governed by established rules. The official host is FilkxTech Premium LLC.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Specific terms regarding bonus points, gift products, or special promo pricing are provided on the details page of each active advertising campaign.</p>'
                ]
            ],
            [
                'slug' => 'privacy',
                'title' => ['uk' => 'Конфіденційність', 'en' => 'Privacy Policy'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Політика конфіденційності та захисту даних</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ми цінуємо вашу приватність і гарантуємо безпеку персональних даних. Дана Політика пояснює, як ми збираємо, обробляємо та захищаємо вашу особисту інформацію при користуванні сайтом.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Збір даних здійснюється виключно з метою обробки замовлень та надання персоналізованих пропозицій. Ми не передаємо ваші дані третім особам без вашої прямої згоди.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Privacy Policy & Personal Data Protection</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">We value your privacy and guarantee the security of your personal database records. This policy document explains how we collect, process, and protect your information when utilizing our services.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Data collection is done purely to complete your orders and supply customized discounts. We never transfer your details to third parties without your explicit permission.</p>'
                ]
            ],
            [
                'slug' => 'oferta',
                'title' => ['uk' => 'Публічна оферта', 'en' => 'Public Offer'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Договір публічної оферти</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Цей договір є офіційною пропозицією (публічною офертою) інтернет-магазину FilkxTech щодо продажу товарів дистанційним шляхом за допомогою засобів електронного зв\'язку.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Моментом безумовного прийняття умов договору користувачем (акцептом оферти) вважається факт оформлення та підтвердження замовлення на веб-сайті магазину.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Public Offer Agreement</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">This agreement constitutes the official public offer of the FilkxTech online store for purchasing products remotely through electronic channels.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The moment of unconditional acceptance of these conditions by the user (acceptance of the offer) is defined as placing and validating an order on this website.</p>'
                ]
            ],
            [
                'slug' => 'cookies',
                'title' => ['uk' => 'Політика Cookies', 'en' => 'Cookies Policy'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Використання файлів cookie</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Наш сайт використовує файли cookie для покращення досвіду користувача, аналізу трафіку та надання релевантних рекламних оголошень.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Файли cookie допомагають нам зберігати ваші налаштування мови та товари в кошику. Ви можете в будь-який час вимкнути файли cookie в налаштуваннях вашого браузера.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Cookies Usage Policy</h2>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Our website utilizes cookie files to enhance your browsing experience, analyze traffic patterns, and display relevant commercial ads.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Cookies help us preserve your language selections and remember cart items. You can block cookie files at any moment inside your browser options.</p>'
                ]
            ]
        ];

        foreach ($pages as $p) {
            DB::table('static_pages')->insert([
                'slug' => $p['slug'],
                'title' => json_encode($p['title'], JSON_UNESCAPED_UNICODE),
                'content' => json_encode($p['content'], JSON_UNESCAPED_UNICODE),
                'status' => 'published',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('static_pages');
    }
};
