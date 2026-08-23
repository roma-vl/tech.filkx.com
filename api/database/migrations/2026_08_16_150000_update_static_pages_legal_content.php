<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rewrites the legal/business-identity static pages seeded by the original
     * create_static_pages_table migration. That seed data was placeholder/demo
     * content: it named two different, non-existent legal entities across
     * different pages, invented a street address, hotline, physical stores in
     * three cities, and bank installment partnerships that were never
     * integrated. This migration replaces it with the real entity name and
     * clearly-marked placeholders for facts we don't have (registered address,
     * EDRPOU, phone), and substantially expands terms/privacy/oferta/cookies
     * to an actual legal-document level of detail. It is still a draft — has
     * not been reviewed by a lawyer — and should be before relying on it.
     */
    public function up(): void
    {
        $entity = 'ТОВ «FilkxTech»';
        $entityEn = 'FilkxTech LLC';

        $pages = [
            'terms' => [
                'title' => ['uk' => 'Умови використання сайту', 'en' => 'Terms of Use'],
                'content' => [
                    'uk' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Угода користувача</h2>
<p class="text-xs text-zinc-400 mb-6">Останнє оновлення: {$this->today('uk')}. Це чернетка документа — перед публічним запуском рекомендуємо перевірку юристом.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ця Угода регулює користування сайтом tech.filkx.com (далі — «Сайт»), який належить {$entity} (далі — «Продавець», «ми»). Реєструючись, оформлюючи замовлення або іншим чином користуючись Сайтом, ви (далі — «Користувач», «ви») погоджуєтесь з умовами цієї Угоди в повному обсязі. Якщо ви не погоджуєтесь з будь-яким положенням — будь ласка, припиніть користування Сайтом.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. Про Продавця</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">{$entity}, юридична адреса: [ЮРИДИЧНА АДРЕСА], код ЄДРПОУ: [ЄДРПОУ]. Контакти для звернень: [EMAIL ПІДТРИМКИ], [ТЕЛЕФОН ПІДТРИМКИ].</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Реєстрація та обліковий запис</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Оформити замовлення можна як гостем, так і зареєструвавши обліковий запис. Ви відповідаєте за конфіденційність свого пароля та за всі дії, здійснені під вашим обліковим записом. За підозри на несанкціонований доступ негайно повідомте нас та змініть пароль; для додаткового захисту ви можете увімкнути двофакторну автентифікацію в налаштуваннях кабінету.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">3. Товари, ціни та наявність</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ми прагнемо, щоб опис, зображення та ціна товару на Сайті були точними, проте не гарантуємо повну відсутність технічних помилок. У разі виявлення явної помилки в ціні до моменту підтвердження замовлення ми повідомимо вас і надамо можливість підтвердити замовлення за фактичною ціною або скасувати його. Ми залишаємо за собою право змінювати асортимент та ціни без попереднього повідомлення; це не стосується вже підтверджених замовлень.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">4. Оформлення замовлення</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Порядок оформлення замовлення, оплати, доставки та повернення товару регулюється <a href="/pages/oferta" class="text-[#00a046] underline">Публічною офертою</a>, яка є невід'ємною частиною цієї Угоди.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">5. Інтелектуальна власність</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Тексти, зображення, логотипи та інші матеріали Сайту є об'єктами права інтелектуальної власності {$entity} або використовуються на законних підставах. Копіювання, відтворення чи інше використання цих матеріалів без письмової згоди забороняється, за винятком використання в особистих некомерційних цілях.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">6. Заборонені дії</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Забороняється: використовувати Сайт у протиправних цілях; намагатись отримати несанкціонований доступ до облікових записів інших користувачів чи інфраструктури Сайту; використовувати автоматизовані засоби (боти, скрапери) без нашого письмового дозволу; розміщувати недостовірні відгуки чи шахрайські замовлення.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">7. Обмеження відповідальності</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Сайт надається «як є». Ми не несемо відповідальності за тимчасову недоступність Сайту через технічні роботи чи обставини поза нашим контролем. Наша відповідальність за будь-якими вимогами, пов'язаними з користуванням Сайтом, обмежується вартістю відповідного замовлення, за винятком випадків, коли інше прямо передбачено законодавством України.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">8. Захист персональних даних</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Обробка персональних даних Користувачів здійснюється відповідно до нашої <a href="/pages/privacy" class="text-[#00a046] underline">Політики конфіденційності</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">9. Застосовне право</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ця Угода регулюється законодавством України. Спори, що виникають у зв'язку з користуванням Сайтом, вирішуються шляхом переговорів, а за неможливості досягнення згоди — у судовому порядку відповідно до чинного законодавства України.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">10. Зміни до Угоди</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ми можемо оновлювати цю Угоду. Актуальна версія завжди доступна на цій сторінці із зазначеною датою останнього оновлення. Продовження користування Сайтом після публікації змін означає їх прийняття.</p>
HTML,
                    'en' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Terms of Use</h2>
<p class="text-xs text-zinc-400 mb-6">Last updated: {$this->today('en')}. This is a draft document — have it reviewed by a lawyer before relying on it in production.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">These Terms govern your use of tech.filkx.com (the "Site"), operated by {$entityEn} ("Seller", "we"). By registering, placing an order, or otherwise using the Site, you ("User", "you") agree to be bound by these Terms in full. If you do not agree, please discontinue use of the Site.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. About the Seller</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">{$entityEn}, registered address: [REGISTERED ADDRESS], company registration number: [REGISTRATION NUMBER]. Contact: [SUPPORT EMAIL], [SUPPORT PHONE].</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Account & Registration</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">You may check out as a guest or create an account. You are responsible for keeping your password confidential and for all activity under your account. If you suspect unauthorized access, notify us immediately and change your password; you can also enable two-factor authentication in your account settings for extra protection.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">3. Products, Pricing & Availability</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">We try to keep product descriptions, images, and prices accurate, but we do not guarantee they are free of error. If a clear pricing error is discovered before your order is confirmed, we will contact you to confirm the order at the correct price or cancel it. We may change our catalog and prices without prior notice; this does not affect orders already confirmed.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">4. Placing an Order</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The order, payment, delivery, and return process is governed by our <a href="/pages/oferta" class="text-[#00a046] underline">Public Offer</a>, which forms an integral part of these Terms.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">5. Intellectual Property</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">All text, images, logos, and other materials on the Site are the intellectual property of {$entityEn} or used under license. Copying, reproducing, or otherwise using these materials without written consent is prohibited, except for personal, non-commercial use.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">6. Prohibited Use</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">You may not: use the Site for unlawful purposes; attempt unauthorized access to other users' accounts or Site infrastructure; use bots or scrapers without our written permission; post fraudulent reviews or place fraudulent orders.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">7. Limitation of Liability</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The Site is provided "as is". We are not liable for temporary unavailability due to maintenance or circumstances beyond our control. Our liability for any claim related to use of the Site is limited to the value of the relevant order, except where mandatory law provides otherwise.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">8. Personal Data</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Personal data is processed in accordance with our <a href="/pages/privacy" class="text-[#00a046] underline">Privacy Policy</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">9. Governing Law</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">These Terms are governed by the laws of Ukraine. Disputes arising from use of the Site will be resolved through negotiation, and failing that, in accordance with applicable Ukrainian law.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">10. Changes</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">We may update these Terms. The current version, with its last-updated date, is always available on this page. Continued use of the Site after changes are published constitutes acceptance.</p>
HTML,
                ],
            ],

            'privacy' => [
                'title' => ['uk' => 'Політика конфіденційності', 'en' => 'Privacy Policy'],
                'content' => [
                    'uk' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Політика конфіденційності</h2>
<p class="text-xs text-zinc-400 mb-6">Останнє оновлення: {$this->today('uk')}. Це чернетка документа — перед публічним запуском рекомендуємо перевірку юристом.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">{$entity} (далі — «ми», «Оператор») є розпорядником персональних даних, які ви надаєте на сайті tech.filkx.com. Ця Політика пояснює, які дані ми збираємо, з якою метою, кому їх передаємо і які права ви маєте. Обробка здійснюється відповідно до Закону України «Про захист персональних даних».</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. Які дані ми збираємо</h3>
<ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mb-4">
  <li>Реєстраційні дані: ім'я, email, телефон, пароль (зберігається у хешованому вигляді, нам недоступний у відкритому вигляді).</li>
  <li>Дані замовлення: адреса доставки, спосіб доставки та оплати, склад замовлення.</li>
  <li>Платіжні дані: номери карток ми не бачимо і не зберігаємо — оплата карткою обробляється платіжною системою LiqPay на її захищеній сторінці; нам доступний лише статус і сума операції.</li>
  <li>Технічні дані: IP-адреса, тип пристрою та браузера — використовуються для захисту акаунта (наприклад, сповіщення про вхід з нового пристрою) та для cookies (див. окрему <a href="/pages/cookies" class="text-[#00a046] underline">Політику cookies</a>).</li>
  <li>За вашою згодою — дані для двофакторної автентифікації (секрет автентифікатора та резервні коди зберігаються у зашифрованому вигляді).</li>
</ul>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Мета обробки</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Оформлення та виконання замовлень; ідентифікація та захист облікового запису; надання підтримки; виконання вимог податкового та бухгалтерського законодавства; за вашою окремою згодою — розсилка новин та пропозицій (від якої можна відписатися в будь-який момент).</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">3. Кому ми передаємо дані</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ми не продаємо персональні дані третім особам. Дані можуть передаватися виключно постачальникам послуг, необхідним для виконання замовлення чи роботи Сайту:</p>
<ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mb-4">
  <li>Служби доставки (Нова Пошта, Укрпошта) — ім'я, телефон та адреса одержувача.</li>
  <li>Платіжна система LiqPay — для обробки онлайн-оплати карткою.</li>
  <li>Постачальники технічної інфраструктури (хостинг, email-розсилки, моніторинг помилок) — виключно для забезпечення роботи Сайту.</li>
</ul>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">4. Термін зберігання</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Дані облікового запису зберігаються, доки він активний. Дані завершених замовлень зберігаються відповідно до строків, встановлених податковим законодавством України. Після видалення акаунта дані позначаються як видалені й приховуються з активного використання; технічне видалення відбувається згідно з внутрішнім регламентом.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">5. Ваші права</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ви маєте право: отримати доступ до своїх даних; вимагати їх виправлення чи видалення; відкликати згоду на обробку; заперечити проти обробки в маркетингових цілях. Для реалізації цих прав звертайтесь на [EMAIL ПІДТРИМКИ]. Ви також маєте право звернутися зі скаргою до Уповноваженого Верховної Ради України з прав людини (у сфері захисту персональних даних).</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">6. Безпека</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">З'єднання із Сайтом шифрується (HTTPS). Паролі зберігаються у хешованому вигляді, секрети двофакторної автентифікації та резервні коди — у зашифрованому вигляді. Доступ до персональних даних мають лише авторизовані співробітники в межах їхніх службових обов'язків.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">7. Діти</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Сайт не призначений для осіб віком до 16 років. Ми свідомо не збираємо персональні дані дітей.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">8. Зміни до Політики</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">У разі суттєвих змін ми повідомимо про це на Сайті. Дата останнього оновлення завжди вказана на початку цієї сторінки.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">9. Контакти</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">З питань обробки персональних даних звертайтесь: [EMAIL ПІДТРИМКИ], [ЮРИДИЧНА АДРЕСА].</p>
HTML,
                    'en' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Privacy Policy</h2>
<p class="text-xs text-zinc-400 mb-6">Last updated: {$this->today('en')}. This is a draft document — have it reviewed by a lawyer before relying on it in production.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">{$entityEn} ("we", "the Controller") is the data controller for personal data you provide on tech.filkx.com. This Policy explains what data we collect, why, who we share it with, and your rights. Processing is carried out under Ukraine's Law "On Personal Data Protection".</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. What We Collect</h3>
<ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mb-4">
  <li>Account data: name, email, phone, password (stored hashed — never accessible to us in plain text).</li>
  <li>Order data: delivery address, chosen delivery and payment method, order contents.</li>
  <li>Payment data: we never see or store card numbers — card payments are processed by LiqPay on their own secured page; we only receive the transaction status and amount.</li>
  <li>Technical data: IP address, device and browser type — used for account security (e.g. new-device login alerts) and cookies (see our separate <a href="/pages/cookies" class="text-[#00a046] underline">Cookies Policy</a>).</li>
  <li>With your consent — two-factor authentication data (the authenticator secret and recovery codes are stored encrypted).</li>
</ul>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Why We Process It</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">To place and fulfill orders; identify and secure your account; provide support; comply with tax and accounting obligations; and, with your separate opt-in, to send newsletters and offers (you can unsubscribe at any time).</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">3. Who We Share Data With</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">We do not sell personal data. Data is only shared with service providers necessary to fulfill your order or run the Site:</p>
<ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mb-4">
  <li>Delivery carriers (Nova Poshta, Ukrposhta) — recipient name, phone, and address.</li>
  <li>LiqPay payment system — to process online card payments.</li>
  <li>Technical infrastructure providers (hosting, transactional email, error monitoring) — solely to operate the Site.</li>
</ul>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">4. Retention</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Account data is kept while your account is active. Completed-order data is retained per Ukrainian tax record-keeping requirements. After account deletion, data is marked deleted and hidden from active use; technical erasure follows our internal retention schedule.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">5. Your Rights</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">You may request access to your data, request correction or deletion, withdraw consent, or object to marketing use. Contact [SUPPORT EMAIL] to exercise these rights. You may also lodge a complaint with the Ukrainian Parliament Commissioner for Human Rights (personal data protection function).</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">6. Security</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The Site connection is encrypted (HTTPS). Passwords are stored hashed; two-factor secrets and recovery codes are stored encrypted. Only authorized staff can access personal data, within the scope of their duties.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">7. Children</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The Site is not directed at anyone under 16. We do not knowingly collect personal data from children.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">8. Changes</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">We will announce material changes on the Site. The last-updated date is always shown at the top of this page.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">9. Contact</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">For data protection inquiries: [SUPPORT EMAIL], [REGISTERED ADDRESS].</p>
HTML,
                ],
            ],

            'oferta' => [
                'title' => ['uk' => 'Публічна оферта', 'en' => 'Public Offer'],
                'content' => [
                    'uk' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Договір публічної оферти</h2>
<p class="text-xs text-zinc-400 mb-6">Останнє оновлення: {$this->today('uk')}. Це чернетка документа — перед публічним запуском рекомендуємо перевірку юристом.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Цей документ є офіційною пропозицією (публічною офертою) {$entity} щодо укладення договору роздрібної купівлі-продажу товарів дистанційним способом через сайт tech.filkx.com. Оформлення та підтвердження замовлення на Сайті є повним і безумовним прийняттям (акцептом) умов цієї оферти відповідно до статті 633 та 641 Цивільного кодексу України.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. Предмет договору</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Продавець зобов'язується передати у власність Покупця товар, обраний на Сайті, а Покупець — прийняти та оплатити цей товар на умовах, зазначених у відповідному замовленні.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Порядок оформлення замовлення</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Замовлення оформлюється через кошик Сайту із зазначенням контактних даних та адреси доставки. Після оформлення Покупець отримує підтвердження замовлення з унікальним номером. Продавець залишає за собою право зв'язатися з Покупцем для уточнення деталей замовлення. Загальні правила реєстрації облікового запису та користування Сайтом визначені <a href="/pages/terms" class="text-[#00a046] underline">Угодою користувача</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">3. Ціна та оплата</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ціна товару вказується у гривнях на сторінці товару і фіксується на момент оформлення замовлення. Доступні способи оплати:</p>
<ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mb-4">
  <li>Оплата карткою онлайн через платіжну систему LiqPay (Visa/Mastercard).</li>
  <li>Оплата готівкою або карткою кур'єру/у відділенні перевізника при отриманні (накладений платіж).</li>
  <li>Банківський переказ за реквізитами для юридичних та фізичних осіб.</li>
</ul>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Персональні дані, надані під час оформлення та оплати замовлення, обробляються відповідно до нашої <a href="/pages/privacy" class="text-[#00a046] underline">Політики конфіденційності</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">4. Доставка</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Доставка здійснюється службами Нова Пошта та Укрпошта на всю територію України. Строк доставки залежить від обраної служби та населеного пункту й орієнтовно повідомляється при оформленні замовлення. Право власності та ризик випадкової втрати товару переходять до Покупця з моменту отримання товару у перевізника.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">5. Право на повернення та обмін</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Покупець має право повернути або обміняти товар належної якості протягом 14 днів з моменту отримання відповідно до Закону України «Про захист прав споживачів», за умови збереження товарного вигляду. Детальний порядок описаний на сторінці <a href="/pages/warranty-returns" class="text-[#00a046] underline">«Гарантія та обмін»</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">6. Права та обов'язки сторін</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Продавець зобов'язується передати товар належної якості та комплектності. Покупець зобов'язується надати достовірні контактні дані та прийняти й оплатити замовлений товар. Продавець має право відмовити в обслуговуванні при обґрунтованій підозрі на шахрайське замовлення.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">7. Відповідальність та форс-мажор</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Сторони звільняються від відповідальності за часткове чи повне невиконання зобов'язань, якщо це стало наслідком обставин непереборної сили (форс-мажору), які виникли після укладення цього договору.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">8. Вирішення спорів</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Усі спори вирішуються шляхом переговорів; за недосягнення згоди — у судовому порядку відповідно до законодавства України.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">9. Реквізити Продавця</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">{$entity}<br>Код ЄДРПОУ: [ЄДРПОУ]<br>Юридична адреса: [ЮРИДИЧНА АДРЕСА]<br>Банківські реквізити: [IBAN / БАНК]<br>Email: [EMAIL ПІДТРИМКИ]</p>
HTML,
                    'en' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Public Offer Agreement</h2>
<p class="text-xs text-zinc-400 mb-6">Last updated: {$this->today('en')}. This is a draft document — have it reviewed by a lawyer before relying on it in production.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">This document is the official public offer of {$entityEn} to enter into a retail sale contract for products purchased remotely via tech.filkx.com. Placing and confirming an order on the Site constitutes full and unconditional acceptance of this offer under Articles 633 and 641 of the Civil Code of Ukraine.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. Subject</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The Seller agrees to transfer ownership of the selected product to the Buyer, and the Buyer agrees to accept and pay for it under the terms specified in the corresponding order.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Placing an Order</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Orders are placed via the Site's cart with contact details and delivery address. The Buyer receives an order confirmation with a unique order number. The Seller may contact the Buyer to clarify order details. General rules for account registration and use of the Site are set out in our <a href="/pages/terms" class="text-[#00a046] underline">Terms of Use</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">3. Price & Payment</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Prices are shown in hryvnia on the product page and fixed at the moment the order is placed. Available payment methods:</p>
<ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-600 dark:text-zinc-400 mb-4">
  <li>Online card payment via LiqPay (Visa/Mastercard).</li>
  <li>Cash or card on delivery, paid to the courier or at the carrier's branch.</li>
  <li>Bank transfer to the invoice details, for both individuals and legal entities.</li>
</ul>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Personal data provided when placing and paying for an order is processed in accordance with our <a href="/pages/privacy" class="text-[#00a046] underline">Privacy Policy</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">4. Delivery</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Delivery is carried out by Nova Poshta and Ukrposhta across Ukraine. Delivery time depends on the chosen carrier and destination, and an estimate is shown during checkout. Title and risk of accidental loss pass to the Buyer once the item is handed to the carrier.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">5. Returns & Exchanges</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The Buyer may return or exchange an undamaged product within 14 days of receipt under Ukraine's Consumer Protection Law, provided its original condition is preserved. Full details are on the <a href="/pages/warranty-returns" class="text-[#00a046] underline">Warranty & Returns</a> page.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">6. Rights & Obligations</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">The Seller agrees to deliver goods of proper quality and complete configuration. The Buyer agrees to provide accurate contact details and accept and pay for the ordered goods. The Seller may decline service where fraud is reasonably suspected.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">7. Liability & Force Majeure</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Neither party is liable for partial or full non-performance caused by force majeure arising after this agreement is concluded.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">8. Dispute Resolution</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Disputes are resolved through negotiation, and failing that, in accordance with applicable Ukrainian law.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">9. Seller's Details</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">{$entityEn}<br>Registration number: [REGISTRATION NUMBER]<br>Registered address: [REGISTERED ADDRESS]<br>Bank details: [IBAN / BANK]<br>Email: [SUPPORT EMAIL]</p>
HTML,
                ],
            ],

            'cookies' => [
                'title' => ['uk' => 'Політика Cookies', 'en' => 'Cookies Policy'],
                'content' => [
                    'uk' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Використання файлів cookie</h2>
<p class="text-xs text-zinc-400 mb-6">Останнє оновлення: {$this->today('uk')}. Це чернетка документа — перед публічним запуском рекомендуємо перевірку юристом.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Cookies — невеликі текстові файли, що зберігаються у вашому браузері. Ми використовуємо лише ті cookies, які необхідні для роботи Сайту; станом на зараз ми не використовуємо рекламні чи аналітичні cookies третіх сторін. Загальні принципи обробки персональних даних описані в нашій <a href="/pages/privacy" class="text-[#00a046] underline">Політиці конфіденційності</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. Які cookies ми використовуємо</h3>
<table class="w-full text-sm text-left text-zinc-600 dark:text-zinc-400 mb-6 border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden">
  <thead class="bg-zinc-50 dark:bg-zinc-900 text-xs uppercase">
    <tr>
      <th class="px-4 py-3">Тип</th>
      <th class="px-4 py-3">Призначення</th>
    </tr>
  </thead>
  <tbody>
    <tr class="border-t border-zinc-150 dark:border-zinc-800">
      <td class="px-4 py-3 font-semibold">Обов'язкові</td>
      <td class="px-4 py-3">Ідентифікація сесії кошика для гостей, авторизація, вибір мови інтерфейсу. Без них Сайт не працюватиме коректно.</td>
    </tr>
    <tr class="border-t border-zinc-150 dark:border-zinc-800">
      <td class="px-4 py-3 font-semibold">Функціональні</td>
      <td class="px-4 py-3">Запам'ятовування налаштувань (наприклад, теми оформлення), списку переглянутих товарів.</td>
    </tr>
  </tbody>
</table>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Керування cookies</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Ви можете видалити або заблокувати cookies в налаштуваннях вашого браузера. Врахуйте, що вимкнення обов'язкових cookies може порушити роботу кошика та авторизації на Сайті.</p>
HTML,
                    'en' => <<<HTML
<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Cookies Policy</h2>
<p class="text-xs text-zinc-400 mb-6">Last updated: {$this->today('en')}. This is a draft document — have it reviewed by a lawyer before relying on it in production.</p>

<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">Cookies are small text files stored by your browser. We only use cookies required to operate the Site; we do not currently use third-party advertising or analytics cookies. General principles for processing personal data are described in our <a href="/pages/privacy" class="text-[#00a046] underline">Privacy Policy</a>.</p>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">1. Cookies We Use</h3>
<table class="w-full text-sm text-left text-zinc-600 dark:text-zinc-400 mb-6 border border-zinc-150 dark:border-zinc-800 rounded-xl overflow-hidden">
  <thead class="bg-zinc-50 dark:bg-zinc-900 text-xs uppercase">
    <tr>
      <th class="px-4 py-3">Type</th>
      <th class="px-4 py-3">Purpose</th>
    </tr>
  </thead>
  <tbody>
    <tr class="border-t border-zinc-150 dark:border-zinc-800">
      <td class="px-4 py-3 font-semibold">Strictly necessary</td>
      <td class="px-4 py-3">Guest cart session identification, authentication, interface language selection. The Site will not function correctly without these.</td>
    </tr>
    <tr class="border-t border-zinc-150 dark:border-zinc-800">
      <td class="px-4 py-3 font-semibold">Functional</td>
      <td class="px-4 py-3">Remembering preferences (e.g. theme) and recently viewed products.</td>
    </tr>
  </tbody>
</table>

<h3 class="text-lg font-bold text-zinc-900 dark:text-white mt-8 mb-3">2. Managing Cookies</h3>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed">You can delete or block cookies in your browser settings. Note that disabling strictly necessary cookies may break cart and login functionality on the Site.</p>
HTML,
                ],
            ],

            'contacts' => [
                'title' => ['uk' => 'Контакти', 'en' => 'Contacts'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Зв\'яжіться з нами</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Ми завжди раді допомогти вам з будь-якими запитаннями. Зв\'яжіться з нами електронною поштою або через форму підтримки в особистому кабінеті.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
  <div class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400">
    <p><strong>Юридична особа:</strong> ТОВ «FilksTech»</p>
    <p><strong>Юридична адреса:</strong> [ЮРИДИЧНА АДРЕСА]</p>
    <p><strong>Телефон підтримки:</strong> [ТЕЛЕФОН ПІДТРИМКИ]</p>
    <p><strong>Email служби підтримки:</strong> support@tech.filkx.com</p>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-base font-bold text-zinc-900 dark:text-white mb-3">Графік роботи підтримки</h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400">[ГРАФІК РОБОТИ]</p>
  </div>
</div>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Contact Us</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">We are always happy to help with any questions. Reach us by email or through the support form in your account.</p>
<div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
  <div class="space-y-4 text-sm text-zinc-600 dark:text-zinc-400">
    <p><strong>Legal entity:</strong> FilksTech LLC</p>
    <p><strong>Registered address:</strong> [REGISTERED ADDRESS]</p>
    <p><strong>Support phone:</strong> [SUPPORT PHONE]</p>
    <p><strong>Support email:</strong> support@tech.filkx.com</p>
  </div>
  <div class="p-6 bg-zinc-50 dark:bg-zinc-900 border border-zinc-150 dark:border-zinc-800 rounded-xl">
    <h3 class="text-base font-bold text-zinc-900 dark:text-white mb-3">Support Hours</h3>
    <p class="text-sm text-zinc-600 dark:text-zinc-400">[SUPPORT HOURS]</p>
  </div>
</div>',
                ],
            ],

            'about' => [
                'title' => ['uk' => 'Про нас', 'en' => 'About Us'],
                'content' => [
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Про компанію FilksTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">FilksTech — інтернет-магазин електроніки та побутової техніки. Наша місія — надавати клієнтам оригінальну техніку з прозорими умовами та якісною підтримкою.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Ми відбираємо перевірені бренди, надаємо офіційну гарантію на всі товари та швидку доставку по всій Україні.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">About FilksTech</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">FilksTech is an online consumer electronics store. Our mission is to supply authentic technology with transparent terms and quality support.</p>
<p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">We partner with trusted brands, provide an official warranty on every product, and deliver fast across Ukraine.</p>',
                ],
            ],

            'promo-rules' => [
                'title' => ['uk' => 'Офіційні правила акцій', 'en' => 'Official Promotion Rules'],
                'content' => [
                    'uk' => "<h2 class=\"text-2xl font-black text-zinc-900 dark:text-white mb-6\">Правила проведення акцій</h2>\n<p class=\"text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed\">Всі акційні пропозиції та знижки на сайті FilksTech проводяться відповідно до встановлених офіційних правил. Організатором акцій виступає {$entity}.</p>\n<p class=\"text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed\">Умови нарахування знижок чи надання спеціальних цін вказуються в описі кожної окремої акції на сайті.</p>",
                    'en' => "<h2 class=\"text-2xl font-black text-zinc-900 dark:text-white mb-6\">Official Promotion Rules</h2>\n<p class=\"text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed\">All promotional offers and discounts on the FilksTech site are governed by these official rules. The organizer of all promotions is {$entityEn}.</p>\n<p class=\"text-sm text-zinc-600 dark:text-zinc-400 mb-4 leading-relaxed\">Terms for specific discounts or special pricing are stated in the description of each individual promotion on the site.</p>",
                ],
            ],
        ];

        foreach ($pages as $slug => $p) {
            DB::table('static_pages')->where('slug', $slug)->update([
                'title' => json_encode($p['title'], JSON_UNESCAPED_UNICODE),
                'content' => json_encode($p['content'], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }

        // These pages named a payment/installment partner (Monobank/PrivatBank) and physical
        // stores that were never actually integrated or opened - soften to what's real.
        $shippingPayment = DB::table('static_pages')->where('slug', 'shipping-payment')->first();
        if ($shippingPayment) {
            $content = json_decode($shippingPayment->content, true);
            foreach (['uk', 'en'] as $locale) {
                $content[$locale] = str_ireplace(
                    ['через Monobank (Visa / Mastercard / Apple Pay / Google Pay)', 'via Monobank (Visa / Mastercard / Apple Pay / Google Pay)'],
                    [$locale === 'uk' ? 'карткою Visa/Mastercard' : 'by Visa/Mastercard card', $locale === 'uk' ? 'карткою Visa/Mastercard' : 'by Visa/Mastercard card'],
                    $content[$locale]
                );
                $content[$locale] = str_ireplace(
                    'Безкоштовно з наших фірмових магазинів FilkxTech у Києві, Львові та Одесі.',
                    'За наявності пункту видачі у вашому місті — уточнюйте при оформленні замовлення.',
                    $content[$locale]
                );
                $content[$locale] = str_ireplace(
                    'Free pickup from our FilkxTech stores in Kyiv, Lviv, and Odesa.',
                    'Available where a pickup point exists in your city — confirmed at checkout.',
                    $content[$locale]
                );
            }
            DB::table('static_pages')->where('slug', 'shipping-payment')->update([
                'content' => json_encode($content, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }

        $installments = DB::table('static_pages')->where('slug', 'installments')->first();
        if ($installments) {
            DB::table('static_pages')->where('slug', 'installments')->update([
                'content' => json_encode([
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Розстрочка та оплата частинами</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Розстрочка наразі не інтегрована безпосередньо в оформлення замовлення на сайті. Якщо вам цікава оплата частинами — напишіть нам, і ми підкажемо актуальні варіанти на момент вашої покупки.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Installments</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Installment payment is not currently integrated directly into checkout. If you are interested in paying in installments, contact us and we will let you know what is available at the time of your purchase.</p>',
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }

        $tradeIn = DB::table('static_pages')->where('slug', 'filkx-exchange')->first();
        if ($tradeIn) {
            DB::table('static_pages')->where('slug', 'filkx-exchange')->update([
                'content' => json_encode([
                    'uk' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Trade-in обмін</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Програма обміну старої техніки наразі не автоматизована на сайті. Якщо ви хочете здати пристрій в рахунок нової покупки — напишіть нам на [EMAIL ПІДТРИМКИ], і ми оцінимо його індивідуально.</p>',
                    'en' => '<h2 class="text-2xl font-black text-zinc-900 dark:text-white mb-6">Trade-in Exchange</h2>
<p class="text-base text-zinc-600 dark:text-zinc-400 mb-6 leading-relaxed">Our trade-in program is not yet automated on the site. If you would like to trade in an old device toward a new purchase, email us at [SUPPORT EMAIL] and we will provide an individual valuation.</p>',
                ], JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Intentionally no-op: this migration corrects seeded demo content in place.
        // Reverting would restore the placeholder business facts it was written to remove.
    }

    private function today(string $locale): string
    {
        return $locale === 'uk' ? now()->format('d.m.Y') : now()->format('Y-m-d');
    }
};
