<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The ProductsFromSotaSeeder only ever links each product to its top-level parent
     * category (e.g. "tablets-laptops-pc"), never to a specific child ("tablets",
     * "laptops", ...) - so browsing/filtering by a *child* category on the storefront
     * returns nothing, even though the parent page works (CategoryRepository::
     * resolveCategoryIdsBySlug includes a parent's direct children, but not the other
     * way around). This reassigns every seeded product from its parent-only link to the
     * correct child, identified by slug (not id - the previous
     * 2026_08_21_150100_fix_miscategorized_seeded_products migration hardcoded product
     * ids that turned out not to be stable across environments and silently moved the
     * wrong product on production; see the repair for id 3 below).
     *
     * "smart-gadgets" has no child categories at all yet (all 20 of its seeded products
     * are smartwatches), so this also adds a "smartwatches" child, the same way the
     * earlier migration added a missing "laptops" child under "tablets-laptops-pc".
     */
    private const SMARTWATCHES_CATEGORY = [
        'parent_slug' => 'smart-gadgets',
        'slug' => 'smartwatches',
        'name' => ['uk' => 'Смарт-годинники', 'en' => 'Smartwatches'],
        'order' => 1,
    ];

    /**
     * Confirmed live on production: the previous migration's hardcoded product id 3 was
     * assumed to be a "Lenovo Legion 5 Pro" laptop - on this database it's actually
     * "Nothing Phone (3)", which that migration moved into "laptops". Repaired back to
     * the "smartphones" parent (no "Nothing" brand child exists, unlike Apple/Samsung/
     * Google etc., so it stays at the parent level like Nothing Phone (4a) already does).
     */
    private const REPAIR_SLUG = 'smartfon-nothing-phone-3-12-256gb-white-227330';

    private const REPAIR_FROM = 'laptops';

    private const REPAIR_TO = 'smartphones';

    // product slug => target child category slug
    private const RECATEGORIZATIONS = [
        'google-pixel-7-8-128gb-snow-119799' => 'smartphones-google',
        'smartfon-google-pixel-9-pro-16-256gb-hazel-192612' => 'smartphones-google',
        'telefon-maxcom-mm142-black-215875' => 'feature-phones',
        'telefon-maxcom-mm35d-black-215881' => 'feature-phones',
        'telefon-nomi-i1820-black-ua-227575' => 'feature-phones',
        'telefon-nomi-i1820-red-ua-227576' => 'feature-phones',
        'nomi-i1850-black-ua-147160' => 'feature-phones',
        'nomi-i1890-blue-ua-147163' => 'feature-phones',
        'nomi-i220-red-ua-68442' => 'feature-phones',
        'nomi-i2403-black-ua-151657' => 'feature-phones',
        'nomi-i2403-red-ua-151659' => 'feature-phones',
        'smart-godinnik-apple-watch-series-10-gps-cellular-42mm-jet-black-aluminum-case-with-black-189930' => 'smartwatches',
        'smart-godinnik-apple-watch-series-10-gps-cellular-46mm-natural-titanium-case-with-stone-gr-192878' => 'smartwatches',
        'smart-godinnik-apple-watch-series-9-gps-cellular-45mm-midnight-aluminum-case-with-midnight-146530' => 'smartwatches',
        'smart-godinnik-apple-watch-series-9-gps-45mm-starlight-aluminum-case-with-pure-platinum-ni-157369' => 'smartwatches',
        'smart-godinnik-apple-watch-ultra-3-gps-cellular-49mm-black-titanium-case-with-black-alpine-227245' => 'smartwatches',
        'smart-godinnik-apple-watch-se-3-gps-40mm-midnight-aluminum-case-with-midnight-sport-band-s-228648' => 'smartwatches',
        'apple-watch-se-gps-44mm-midnight-aluminum-case-with-midnight-sport-band-s-m-mre73-147990' => 'smartwatches',
        'apple-watch-se-gps-cellular-40mm-gold-aluminum-case-with-maize-white-sport-loop-mkqp3-109985' => 'smartwatches',
        'rozumnij-godinnik-magic-p10-silver-kospet-ua-228612' => 'smartwatches',
        'rozumnij-godinnik-magic-r10-black-kospet-ua-228611' => 'smartwatches',
        'rozumnij-godinnik-magic-r10-silver-kospet-ua-228610' => 'smartwatches',
        'smart-godinnik-amazfit-active-2r-premium-w2437gl1n-black-ua-253272' => 'smartwatches',
        'smart-godinnik-amazfit-active-2s-premium-w2440gl3n-black-eu-218842' => 'smartwatches',
        'smart-godinnik-amazfit-active-3-premium-w2559gl1n-silver-ua-253269' => 'smartwatches',
        'smart-godinnik-amazfit-active-3-premium-w2559gl2n-blue-ua-253270' => 'smartwatches',
        'smart-godinnik-amazfit-active-3-premium-w2559gl3n-white-ua-253271' => 'smartwatches',
        'smart-godinnik-amazfit-balance-2-black-w2430gl1n-ua-239795' => 'smartwatches',
        'smart-godinnik-amazfit-t-rex-3-pro-44mm-black-gold-w2549gl1n-ua-240133' => 'smartwatches',
        'smart-godinnik-amazfit-t-rex-3-pro-44mm-tactical-black-w2549gl5n-ua-241231' => 'smartwatches',
        'smart-godinnik-amazfit-t-rex-3-pro-48mm-black-gold-w2444ov5n-ua-234596' => 'smartwatches',
        'planshet-apple-ipad-air-13-2025-wi-fi-128gb-space-gray-mcnh4-208604' => 'tablets',
        'apple-ipad-air-11-2024-wi-fi-512gb-starlight-muwn3-169774' => 'tablets',
        'apple-ipad-pro-11-2024-wi-fi-512gb-silver-mvvd3-170901' => 'tablets',
        'apple-ipad-pro-11-2024-wi-fi-512gb-space-black-mvvc3-170902' => 'tablets',
        'planshet-apple-ipad-pro-11-2025-wi-fi-cellular-256gb-space-black-me2n4-234619' => 'tablets',
        'planshet-apple-ipad-pro-11-2025-wi-fi-512gb-space-black-mdwm4-235680' => 'tablets',
        'planshet-samsung-galaxy-tab-s10-ultra-12-256gb-5g-moonstone-gray-sm-x926bzar-193223' => 'tablets',
        'planshet-samsung-galaxy-tab-s11-ultra-5g-12-256gb-silver-sm-x936bzsr-237680' => 'tablets',
        'apple-ipad-10-9-2022-wi-fi-cellular-64gb-blue-mq6k3-120542' => 'tablets',
        'apple-ipad-10-9-2022-wi-fi-cellular-64gb-silver-mq6j3-120540' => 'tablets',
        'planshet-apple-ipad-11-2025-wi-fi-cellular-256gb-silver-md7k4-209427' => 'tablets',
        'planshet-apple-ipad-11-2025-wi-fi-128gb-pink-md4e4-208620' => 'tablets',
        'apple-ipad-pro-11-2022-wi-fi-cellular-1tb-space-gray-mnyj3-120270' => 'tablets',
        'planshet-xiaomi-pad-8-pro-wifi-12-512gb-blue-vhu6549eu-ua-247409' => 'tablets',
        'planshet-xiaomi-pad-8-pro-wifi-12-512gb-gray-vhu6575eu-ua-247410' => 'tablets',
        'planshet-xiaomi-pad-8-pro-wifi-12-512gb-pine-green-vhu6555eu-ua-247411' => 'tablets',
        'planshet-xiaomi-pad-8-pro-wifi-8-256gb-blue-vhu6508eu-ua-247406' => 'tablets',
        'planshet-xiaomi-pad-8-pro-wifi-8-256gb-gray-vhu6534eu-ua-247407' => 'tablets',
        'planshet-xiaomi-pad-8-wifi-8-128gb-blue-vhu6361eu-ua-247404' => 'tablets',
        'planshet-xiaomi-pad-8-wifi-8-128gb-gray-vhu6389eu-ua-247403' => 'tablets',
        'televizor-xiaomi-tv-a-32-2026-l32mb-ame-ua-238374' => 'tvs',
        'televizor-xiaomi-tv-a-43-fhd-2026-l43mb-afme-ua-242122' => 'tvs',
        'televizor-xiaomi-tv-a-50-2026-ua-214819' => 'tvs',
        'televizor-xiaomi-tv-a-55-2026-l55mb-ame-ua-217139' => 'tvs',
        'televizor-xiaomi-tv-a-pro-43-2026-l43mb-apme-ua-213838' => 'tvs',
        'televizor-xiaomi-tv-s-pro-mini-led-55-2026-l55mb-sme-ua-241158' => 'tvs',
        'televizor-43-samsung-led-4k-50hz-smart-tizen-black-ue43u8000fuxua-ua-217462' => 'tvs',
        'televizor-55-samsung-led-4k-50hz-smart-tizen-black-ue55u8000fuxua-ua-214158' => 'tvs',
        'televizor-65-samsung-led-4k-50hz-smart-tizen-black-ue65u8000fuxua-ua-214159' => 'tvs',
        'televizor-75-tcl-miniled-4k-144hz-smart-google-tv-titan-onkyo-sound-75c61ks-ua-252224' => 'tvs',
        'televizor-romsat-50ush1950t2-ua-225725' => 'tvs',
        'televizor-65-samsung-qled-4k-50hz-smart-tizen-black-qe65q7f5auxua-ua-213068' => 'tvs',
        'televizor-32-2e-led-fhd-60hz-smart-webos-black-2e-32a07kw-161791' => 'tvs',
        'televizor-43-2e-qled-4k-60hz-smart-google-tv-black-2e-43a77q-ua-192885' => 'tvs',
        'televizor-32-samsung-led-full-hd-50hz-smart-tizen-black-qe32ls03cbuxua-141722' => 'tvs',
        'televizor-thomson-google-tv-55-miniled-uhd-55mg7c15-ua-221867' => 'tvs',
        'televizor-kivi-50-uhd-optima-l5-hdr-50u710qb-ua-237935' => 'tvs',
        'televizor-xiaomi-tv-a-32-2025-eu-244126' => 'tvs',
        'televizor-xiaomi-tv-a-50-2026-eu-217570' => 'tvs',
        'televizor-xiaomi-tv-a-55-2026-l55mb-ame-eu-217571' => 'tvs',
        'pralna-mashina-ardesto-frontalna-black-mars-8kg-1200-a-55sm-displej-para-invert-217969' => 'large-appliances',
        'pralna-mashina-beko-b3wfu47215w-ua-249426' => 'large-appliances',
        'pralna-mashina-beko-bm1wfsu36233wpbb-ua-242409' => 'large-appliances',
        'pralna-mashina-beko-bm1wfsu37233wb-ua-242929' => 'large-appliances',
        'pralna-mashina-beko-bm3wfsu47215wb-ua-244435' => 'large-appliances',
        'pralna-mashina-beko-bm3wfsu48415wb-ua-244436' => 'large-appliances',
        'pralna-mashina-candy-frontalna-5kg-1200-a-42sm-displej-invertor-lyuk-chornij-visota-248029' => 'large-appliances',
        'pralna-mashina-candy-frontalna-8kg-1400-a-59sm-displej-para-invertor-bilij-gd-248033' => 'large-appliances',
        'pralna-mashina-candy-vertikalna-7kg-1200-a-60sm-displej-criblyastij-tcas274tmr5-250084' => 'large-appliances',
        'pralna-mashina-candy-prowash-300-frontalna-7kg-1200-a-42-4sm-displej-para-invertor-255582' => 'large-appliances',
        'pralna-mashina-candy-prowash-300-frontalna-9kg-1400-a-54-8sm-displej-para-invertor-257028' => 'large-appliances',
        'pralna-mashina-candy-prowash-300-frontalna-8kg-1400-a-48sm-displej-para-invertor-255583' => 'large-appliances',
        'pralna-mashina-candy-prowash-500-frontalna-8kg-1400-a-53sm-displej-para-invertor-255584' => 'large-appliances',
        'pralna-mashina-candy-prowash-550-frontalna-8kg-1400-a-42-7sm-displej-para-invertor-255585' => 'large-appliances',
        'pralna-mashina-candy-vertikalna-6kg-1000-a-60sm-bilij-eytc106l2-s-ua-250082' => 'large-appliances',
        'pralna-mashina-electrolux-ew6f1481u-ua-236185' => 'large-appliances',
        'pralna-mashina-electrolux-ew6t506u-ua-237262' => 'large-appliances',
        'pralna-mashina-electrolux-ew8t337u-ua-236183' => 'large-appliances',
        'pralna-mashina-electrolux-ew8w7607qu-ua-212339' => 'large-appliances',
        'pralna-mashina-electrolux-ews6227cu-ua-236184' => 'large-appliances',
        'holodilna-kamera-candy-85x44-5x47-5-90l-1-dv-e-st-bilij-chasd4385ewc-ua-257030' => 'kitchen-large',
        'holodilna-kamera-snaige-173x60h60-350l-1dv-a-st-bilij-cc35dm-p600fd-ua-219118' => 'kitchen-large',
        'holodilnik-ardesto-bagatodvernij-184x83-3h65-3-4dv-a-nf-zona-nulova-chornij-skl-255565' => 'kitchen-large',
        'holodilnik-beko-b3bcna294hs-ua-246635' => 'kitchen-large',
        'holodilnik-beko-b5bcna325hs-ua-240846' => 'kitchen-large',
        'holodilnik-beko-b5rcne565hxp-ua-242411' => 'kitchen-large',
        'holodilnik-beko-bssa315k4sn-ua-252533' => 'kitchen-large',
        'holodilnik-beko-gn163140sn-ua-250151' => 'kitchen-large',
        'holodilnik-beko-rche325k40w-ua-253188' => 'kitchen-large',
        'holodilnik-beko-rcna366i40wn-ua-247151' => 'kitchen-large',
        'holodilnik-beko-rcna366k40xbn-ua-247460' => 'kitchen-large',
        'holodilnik-beko-rcna406e40zxbrn-ua-247461' => 'kitchen-large',
        'holodilnik-beko-rcna406i40wn-ua-247153' => 'kitchen-large',
        'holodilnik-beko-rcna406i40xbrn-ua-247383' => 'kitchen-large',
        'holodilnik-beko-rcsa240k40wn-ua-242410' => 'kitchen-large',
        'holodilnik-beko-rcsa270k40wn-ua-247782' => 'kitchen-large',
        'holodilnik-beko-rcsa366k40wn-ua-244422' => 'kitchen-large',
        'holodilnik-beko-rdsa240k20s-ua-249423' => 'kitchen-large',
        'holodilnik-beko-rdsa240k40wn-ua-250150' => 'kitchen-large',
        'holodilnik-beko-rdsa280k40wn-ua-244437' => 'kitchen-large',
        'fen-dreame-gusto-g40-spacegrey-ahg40a-sg-ua-227343' => 'hair-care',
        'fen-dreame-gusto-g40-titanium-ahg40a-ti-ua-233557' => 'hair-care',
        'fen-dreame-hair-mini-pink-ahg11ap-ua-227291' => 'hair-care',
        'fen-dreame-hair-mini-purple-ahg11appl-ua-227282' => 'hair-care',
        'fen-dreame-hair-mini-white-ahg11aw-ua-227289' => 'hair-care',
        'fen-dreame-miracle-pro-ahx30-ua-236015' => 'hair-care',
        'fen-dreame-pilot-ahx01a-ua-245341' => 'hair-care',
        'fen-philips-7000-series-1800vt-4-rezhimi-dc-motor-difuzor-ioniz-ya-hol-obduv-sirij-b-239989' => 'hair-care',
        'fen-dlya-volossya-jimmy-f7-white-jimmy-f7-white-ua-227405' => 'hair-care',
        'fen-stajler-dreame-pocket-pro-3in1-grey-ahd51pro-g-ua-244150' => 'hair-care',
        'fen-stajler-dreame-pocket-pro-3in1-titanium-ahd51pro-t-ua-244151' => 'hair-care',
        'fen-stajler-dreame-pocket-ultra-ahd52-rosy-white-ahd52-rwh-ua-233558' => 'hair-care',
        'fen-dreame-glory-mix-gold-ahd18-gold-ua-215416' => 'hair-care',
        'fen-dreame-hair-dryer-gleam-purple-ahd12a-ppl-ua-199581' => 'hair-care',
        'fen-dreame-pocket-high-speed-hair-dryer-space-gray-ahd51-ua-196561' => 'hair-care',
        'fen-dreame-pocket-high-speed-hair-dryer-titanium-ahd51-ti-ua-215297' => 'hair-care',
        'fen-remington-ac9140-60828' => 'hair-care',
        'fen-rotex-rff200-b-ua-187356' => 'hair-care',
        'fen-dlya-volos-s-ionizaciej-xiaomi-mi-ionic-hair-dryer-h300-eu-117175' => 'hair-care',
        'fen-ardesto-pearl-cream-hd-y700perl-1800-2000vt-2-shvidkosti-3-temp-rezhimi-bezhevij-ua-217972' => 'hair-care',
        'konstruktor-lego-botanicals-buket-tyulpaniv-11501-ua-243521' => 'lego',
        'konstruktor-lego-city-arktichnij-doslidnickij-ekspres-60470-ua-219062' => 'lego',
        'konstruktor-lego-disney-princess-krizhanij-palac-elzi-ta-vesele-katannya-na-sanchatah-4328-241412' => 'lego',
        'konstruktor-lego-duplo-town-pozhezhna-mashina-zi-shlangom-i-pozhezhnikom-10473-ua-241424' => 'lego',
        'konstruktor-lego-editions-football-kilian-mbappe-najkrashchi-momenti-futbolnih-matchiv-430-253428' => 'lego',
        'konstruktor-lego-editions-football-krishtianu-ronaldu-legenda-futbolu-43016-ua-253429' => 'lego',
        'konstruktor-lego-editions-football-krishtianu-ronaldu-najkrashchi-momenti-futbolnih-matchi-253430' => 'lego',
        'konstruktor-lego-editions-football-lionel-messi-najkrashchi-momenti-futbolnih-matchiv-430-253431' => 'lego',
        'konstruktor-lego-editions-football-oficijnij-trofej-chempionatu-svitu-z-futbolu-fifa-43020-253219' => 'lego',
        'konstruktor-lego-editions-football-futbolnij-m-yach-43019-248202' => 'lego',
        'konstruktor-lego-editions-formula-1-sholom-sharlya-leklera-z-komandi-scuderia-ferrari-hp-4-253434' => 'lego',
        'konstruktor-lego-iconic-kit-udachi-40813-ua-241448' => 'lego',
        'konstruktor-lego-super-heroes-legendarna-bitva-lyudina-pavuk-vs-pishchana-lyudina-76334-ua-241491' => 'lego',
        'igrushka-nation-xiaomi-bunny-142797' => 'toys',
        'igrashkovij-nabir-instrumentiv-bambi-hz001-234077' => 'toys',
        'konstruktor-lego-botanicals-milij-rozhevij-buket-kvitiv-10342-ua-199296' => 'lego',
        'konstruktor-lego-creator-diki-tvarini-pandovi-31165-ua-199315' => 'lego',
        'konstruktor-lego-creator-charivne-krolenya-31162-ua-199319' => 'lego',
        'konstruktor-lego-star-wars-zoryanij-vinishchuvach-n-1-mando-j-ro-u-75410-ua-199377' => 'lego',
        'mashinka-transformer-flip-cars-2-v-1-eu463875-ua-190674' => 'toys',
        'bigova-dorizhka-kingsmith-treadmill-z3-hybrid-black-wp400p42-eu-206740' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walking-pad-a1pro-a1plus-black-wpa1fpro-ua-255102' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-mx8-h-mx8h-ua-260799' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-r3-justwalk-204954' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-treadmill-r3-hybrid-204952' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-treadmill-x218-204951' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-z1f-204950' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-z3-just-walk-wp400f42-eu-206741' => 'gym-equipment',
        'bigova-dorizhka-oyeet-walking-running-tm-yt104-ua-249855' => 'gym-equipment',
        'elektrichna-bigova-dorizhka-kingsmith-treadmill-ers510t-rver-10100-ua-253259' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-trmx16f-eu-196812' => 'gym-equipment',
        'begovaya-dorozhka-kingsmith-walkingpad-s2-black-wps1f-eu-192767' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-treadmill-cls460-black-ua-243552' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-treadmill-mx10-ua-248752' => 'gym-equipment',
        'bigova-dorizhka-kingsmith-walkingpad-treadmill-x25-eu-218948' => 'gym-equipment',
        'bigova-dorizhka-merach-mr-t10b2-ua-238307' => 'gym-equipment',
        'bigova-dorizhka-merach-mr-t14-ua-238308' => 'gym-equipment',
        'bigova-dorizhka-merach-mr-t25b2-ua-257131' => 'gym-equipment',
        'bigova-dorizhka-merach-mr-t26b1-eu-ua-257132' => 'gym-equipment',
        'okai-es10-neon-lite-black-ua-140539' => 'e-scooters',
        'elektrosamokat-xiaomi-electric-scooter-6-max-bhr08qlgl-ua-253278' => 'e-scooters',
        'elektrosamokat-acer-nitro-es-series-4-select-nes034-gp-esc11-02h-ua-254688' => 'e-scooters',
        'elektrosamokat-acer-predator-es-storm-pes035-gp-esc11-024-ua-254690' => 'e-scooters',
        'elektrosamokat-acer-predator-es-thunder-pes027-gp-esc11-031-ua-254689' => 'e-scooters',
        'elektrosamokat-procraft-ds500l-ua-246631' => 'e-scooters',
        'elektrosamokat-segway-ninebot-e2-e-ii-chornij-aa-05-14-01-0004-ua-213662' => 'e-scooters',
        'elektrosamokat-segway-ninebot-e3-sirij-aa-05-19-01-0003-ua-240658' => 'e-scooters',
        'elektrosamokat-segway-ninebot-e3-pro-e-sirij-aa-05-19-02-0003-ua-221857' => 'e-scooters',
        'elektrosamokat-segway-ninebot-f2-ii-e-chornij-aa-05-12-01-0010-209854' => 'e-scooters',
        'elektrosamokat-segway-ninebot-f2-pro-e-ii-chornij-aa-05-12-03-0007-ua-215237' => 'e-scooters',
        'elektrosamokat-segway-ninebot-f3-e-sirij-aa-05-17-01-0005-ua-220279' => 'e-scooters',
        'elektrosamokat-segway-ninebot-f3-pro-e-sirij-aa-05-17-02-0003-ua-240659' => 'e-scooters',
        'elektrosamokat-segway-ninebot-max-g3-e-sirij-aa-05-16-01-0004-ua-253448' => 'e-scooters',
        'elektrosamokat-segway-ninebot-zt3-pro-e-chornij-aa-05-18-01-0001-210886' => 'e-scooters',
        'elektrosamokat-segway-ninebot-dityachij-c2-lite-sinij-aa-10-05-01-0003-207146' => 'e-scooters',
        'elektrosamokat-sencor-s21-ua-251887' => 'e-scooters',
        'elektrosamokat-sencor-s31-ua-251889' => 'e-scooters',
        'elektrosamokat-sencor-s71-dark-ua-255359' => 'e-scooters',
        'elektrosamokat-sencor-s80-ua-218528' => 'e-scooters',
        'b-v-apple-iphone-17-256gb-esim-lavender-idealnij-stan-260176' => 'used-iphone',
        'b-v-apple-iphone-17-256gb-esim-mist-blue-idealnij-stan-260419' => 'used-iphone',
        'b-v-apple-iphone-17-256gb-esim-sage-idealnij-stan-260435' => 'used-iphone',
        'b-v-apple-iphone-17-pro-1tb-silver-idealnij-stan-251647' => 'used-iphone',
        'b-v-apple-iphone-17-pro-256gb-cosmic-orange-idealnij-stan-251653' => 'used-iphone',
        'b-v-apple-iphone-17-pro-256gb-esim-cosmic-orange-idealnij-stan-260445' => 'used-iphone',
        'b-v-apple-iphone-17-pro-256gb-esim-deep-blue-idealnij-stan-260446' => 'used-iphone',
        'b-v-apple-iphone-17-pro-256gb-esim-silver-idealnij-stan-260447' => 'used-iphone',
        'b-v-apple-iphone-17-pro-512gb-esim-cosmic-orange-idealnij-stan-260448' => 'used-iphone',
        'b-v-apple-iphone-17-pro-512gb-esim-deep-blue-idealnij-stan-260449' => 'used-iphone',
        'b-v-apple-iphone-17-pro-max-1tb-cosmic-orange-garnij-stan-251688' => 'used-iphone',
        'b-v-apple-iphone-17-pro-max-256gb-deep-blue-garnij-stan-251696' => 'used-iphone',
        'b-v-apple-iphone-air-256gb-light-gold-idealnij-stan-251728' => 'used-iphone',
        'b-v-apple-iphone-16-pro-128gb-black-titanium-idealnij-stan-235205' => 'used-iphone',
        'b-v-apple-iphone-16-pro-128gb-desert-titanium-idealnij-stan-235206' => 'used-iphone',
        'b-v-apple-iphone-16-pro-128gb-natural-titanium-garnij-stan-251074' => 'used-iphone',
        'b-v-apple-iphone-16-pro-128gb-white-titanium-idealnij-stan-235204' => 'used-iphone',
        'b-v-apple-iphone-16-pro-256gb-natural-titanium-idealnij-stan-234136' => 'used-iphone',
        'b-v-apple-iphone-16-pro-256gb-white-titanium-idealnij-stan-235203' => 'used-iphone',
        'b-v-apple-iphone-16-pro-max-256gb-black-titanium-garnij-stan-245911' => 'used-iphone',
    ];

    public function up(): void
    {
        $now = now();

        $parentId = DB::table('categories')->where('slug', self::SMARTWATCHES_CATEGORY['parent_slug'])->value('id');

        if ($parentId && ! DB::table('categories')->where('slug', self::SMARTWATCHES_CATEGORY['slug'])->exists()) {
            DB::table('categories')->insert([
                'parent_id' => $parentId,
                'slug' => self::SMARTWATCHES_CATEGORY['slug'],
                'name' => json_encode(self::SMARTWATCHES_CATEGORY['name'], JSON_UNESCAPED_UNICODE),
                'order' => self::SMARTWATCHES_CATEGORY['order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $this->moveProductBySlug(self::REPAIR_SLUG, self::REPAIR_FROM, self::REPAIR_TO);

        foreach (self::RECATEGORIZATIONS as $slug => $childSlug) {
            $product = DB::table('products')->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }

            $currentParentSlug = DB::table('product_category')
                ->join('categories', 'categories.id', '=', 'product_category.category_id')
                ->where('product_category.product_id', $product->id)
                ->whereNull('categories.parent_id')
                ->value('categories.slug');

            $this->moveProductBySlug($slug, $currentParentSlug, $childSlug);
        }
    }

    public function down(): void
    {
        foreach (self::RECATEGORIZATIONS as $slug => $childSlug) {
            $product = DB::table('products')->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }

            $parentSlug = DB::table('categories')
                ->where('slug', $childSlug)
                ->value('parent_id');
            $parentSlug = DB::table('categories')->where('id', $parentSlug)->value('slug');

            $this->moveProductBySlug($slug, $childSlug, $parentSlug);
        }

        $this->moveProductBySlug(self::REPAIR_SLUG, self::REPAIR_TO, self::REPAIR_FROM);

        DB::table('categories')->where('slug', self::SMARTWATCHES_CATEGORY['slug'])->delete();
    }

    private function moveProductBySlug(string $productSlug, ?string $fromSlug, ?string $toSlug): void
    {
        $productId = DB::table('products')->where('slug', $productSlug)->value('id');

        if (! $productId || ! $toSlug) {
            return;
        }

        $toId = DB::table('categories')->where('slug', $toSlug)->value('id');
        if (! $toId) {
            return;
        }

        if ($fromSlug) {
            $fromId = DB::table('categories')->where('slug', $fromSlug)->value('id');
            if ($fromId) {
                DB::table('product_category')
                    ->where('product_id', $productId)
                    ->where('category_id', $fromId)
                    ->delete();
            }
        }

        $alreadyLinked = DB::table('product_category')
            ->where('product_id', $productId)
            ->where('category_id', $toId)
            ->exists();

        if (! $alreadyLinked) {
            DB::table('product_category')->insert([
                'product_id' => $productId,
                'category_id' => $toId,
            ]);
        }
    }
};
