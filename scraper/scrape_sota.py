#!/usr/bin/env python3
"""
Scraper for sota.store/ua - extracts 20 products per category.
Outputs: scraper/products.json

Real product pages on sota.store have FLAT URLs:
  /ua/smartfon-apple-iphone-17-256gb-esim-sage-227140.html
  /ua/pralna-mashina-beko-b3wfu47215w-ua-249426.html

Category/subcategory listing pages have path-style URLs:
  /ua/smartphone/apple-40/iphone-17-1143   ← category listing (appears in nav on every page)
  /ua/household/stiralnie-mashini-734       ← subcategory listing

Strategy:
  1. For each category, find flat .html product links (category-specific).
  2. If < 20 found on the main page, discover subcategory listing URLs and recurse.
  3. Subcategory listing URLs: depth > category URL depth, same prefix, small IDs (<= 5000)
     OR no numeric suffix (slug-only subcategories).
"""

import re
import json
import time
import random
import requests

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'uk-UA,uk;q=0.9',
    'Accept': 'text/html,application/xhtml+xml,*/*;q=0.8',
}

CATEGORY_MAP = [
    {
        'sota_url': 'https://sota.store/ua/smartphone',
        'our_slug': 'smartphones',
        'sub_slugs': ['smartphones-apple', 'smartphones-samsung'],
    },
    {
        'sota_url': 'https://sota.store/ua/accessories',
        'our_slug': 'smart-gadgets',
        'sub_slugs': [],
    },
    {
        'sota_url': 'https://sota.store/ua/plansheti-noutbuki-pk',
        'our_slug': 'tablets-laptops-pc',
        'sub_slugs': ['tablets', 'laptops'],
    },
    {
        'sota_url': 'https://sota.store/ua/photo-video',
        'our_slug': 'tv-audio-photo',
        'sub_slugs': ['tvs', 'audio'],
    },
    {
        'sota_url': 'https://sota.store/ua/household',
        'our_slug': 'home-garden',
        'sub_slugs': ['large-appliances', 'small-appliances'],
    },
    {
        'sota_url': 'https://sota.store/ua/kuhonnaya-tehnika',
        'our_slug': 'kitchen',
        'sub_slugs': ['kitchen-large', 'kitchen-small'],
    },
    {
        'sota_url': 'https://sota.store/ua/styling-tools',
        'our_slug': 'beauty-health',
        'sub_slugs': ['hair-care', 'electric-shavers'],
    },
    {
        'sota_url': 'https://sota.store/ua/dityachi-tovari',
        'our_slug': 'kids',
        'sub_slugs': ['toys', 'kids-transport'],
    },
    {
        'sota_url': 'https://sota.store/ua/sport-i-turizm',
        'our_slug': 'sports-tourism',
        'sub_slugs': ['cycling', 'camping'],
    },
    {
        'sota_url': 'https://sota.store/ua/elektrotransport',
        'our_slug': 'electrotransport-auto',
        'sub_slugs': ['e-scooters', 'e-bikes'],
    },
    {
        'sota_url': 'https://sota.store/ua/b-v-tehnika-ta-gadzheti-1192',
        'our_slug': 'used-tech',
        'sub_slugs': ['used-iphone', 'used-samsung'],
    },
]

REVIEW_TITLES = [
    'Відмінний товар!', 'Дуже задоволений', 'Рекомендую всім', 'Чудова якість',
    'Відповідає опису', 'Швидка доставка', 'Гарне співвідношення ціна/якість',
    'Задоволений покупкою', 'Все як на фото', 'Супер товар',
]
REVIEW_BODIES = [
    'Отримав товар вчасно, якість відмінна. Все відповідає опису на сайті.',
    'Замовляв вперше в цьому магазині — дуже задоволений. Упаковка надійна, товар цілий.',
    'Використовую вже кілька тижнів, жодних нарікань. Якість на рівні. Доставка швидка.',
    'Гарний товар за свої гроші. Функціонал повністю відповідає заявленому.',
    'Все чудово! Менеджер допоміг з вибором, доставка на наступний день. Дякую!',
    'Купляв як подарунок — людина дуже рада. Якість відповідає ціні, рекомендую.',
    'Замовив, привезли швидко. Товар в ідеальному стані, як описано.',
    'Відмінна якість збірки, все функціонує справно. Задоволений на 100%.',
]
REVIEWER_NAMES = [
    'Олексій К.', 'Марія П.', 'Іван С.', 'Наталія В.', 'Дмитро Л.',
    'Юлія М.', 'Андрій Б.', 'Оксана Т.', 'Тарас Г.', 'Вікторія Н.',
    'Михайло Р.', 'Катерина З.', 'Богдан Ф.', 'Ірина Ш.', 'Сергій Д.',
]


def clean_text(s):
    return re.sub(r'\s+', ' ', s).strip() if s else ''


def get_html(url, timeout=20):
    try:
        time.sleep(random.uniform(0.3, 0.7))
        resp = requests.get(url, headers=HEADERS, timeout=timeout, allow_redirects=True)
        if resp.status_code == 200:
            return resp.text
        return ''
    except Exception as e:
        print(f'  WARN {url}: {e}')
        return ''


def flat_product_links(html):
    """
    Extract flat product URLs: /ua/some-slug-{id}.html
    These are always listing-page products, never nav/sidebar links.
    Filter: numeric suffix > 1000 (real products have 5-6 digit IDs).
    """
    links = re.findall(r'href="(https://sota\.store/ua/[^"]+\.html)"', html)
    result = []
    seen = set()
    for l in links:
        # Must end with -NNN.html where NNN > 1000
        m = re.search(r'-(\d+)\.html$', l)
        if m and int(m.group(1)) > 1000 and l not in seen:
            seen.add(l)
            result.append(l)
    return result


def subcategory_links(html, parent_url):
    """
    Find subcategory listing page URLs under parent_url.
    These are path-style: same prefix as parent, deeper, end in -NNN (any size).
    We sort them to visit most product-rich first (deeper = more specific).
    """
    prefix = parent_url.rstrip('/')
    base_depth = parent_url.count('/')
    all_links = re.findall(r'href="(https://sota\.store/ua/[^"#?]+)"', html)
    result = []
    seen = set()
    for l in all_links:
        if not l.startswith(prefix + '/'):
            continue
        if l.count('/') <= base_depth:
            continue
        if l in seen or l == parent_url:
            continue
        seen.add(l)
        result.append(l)
    return result


def collect_product_links(cat_url, limit=20):
    """BFS through category/subcategory pages collecting flat .html product links."""
    found = []
    seen_products = set()
    visited_cats = set()

    def add_products(links):
        for l in links:
            if l not in seen_products:
                seen_products.add(l)
                found.append(l)

    # Level 0: main category page
    html0 = get_html(cat_url)
    if not html0:
        return []
    add_products(flat_product_links(html0))
    print(f'  L0 ({cat_url.split("/")[-1]}): {len(found)} products')

    if len(found) >= limit:
        return found[:limit]

    # Level 1: subcategories
    subcats_l1 = subcategory_links(html0, cat_url)
    visited_cats.add(cat_url)
    print(f'  Subcats L1: {len(subcats_l1)}')

    for sub1 in subcats_l1:
        if len(found) >= limit:
            break
        if sub1 in visited_cats:
            continue
        visited_cats.add(sub1)
        html1 = get_html(sub1)
        if not html1:
            continue
        before = len(found)
        add_products(flat_product_links(html1))
        gained = len(found) - before
        if gained:
            print(f'    L1 {sub1.split("/")[-1]}: +{gained} → {len(found)}')

        if len(found) < limit:
            # Level 2: sub-subcategories
            subcats_l2 = subcategory_links(html1, sub1)
            for sub2 in subcats_l2[:5]:
                if len(found) >= limit:
                    break
                if sub2 in visited_cats:
                    continue
                visited_cats.add(sub2)
                html2 = get_html(sub2)
                if not html2:
                    continue
                before2 = len(found)
                add_products(flat_product_links(html2))
                if len(found) > before2:
                    print(f'      L2 {sub2.split("/")[-1]}: +{len(found)-before2} → {len(found)}')

    return found[:limit]


def extract_price(html):
    m = re.search(r'class="price-new"[^>]*>.*?<prc>([\d\s\xa0]+)</prc>', html, re.DOTALL)
    if m:
        return float(re.sub(r'[\s\xa0]', '', m.group(1)))
    return None


def extract_old_price(html):
    m = re.search(r'class="price-old"[^>]*>.*?<prc>([\d\s\xa0]+)</prc>', html, re.DOTALL)
    if m:
        return float(re.sub(r'[\s\xa0]', '', m.group(1)))
    return None


def extract_images(html):
    imgs = re.findall(
        r'https://sota\.store/image/cache/catalog/[^\"\'\s]+\.(?:webp|jpg|jpeg|png)',
        html
    )
    result = []
    seen = set()
    for img in imgs:
        if any(x in img for x in ('logo', 'sota_icon', 'banner', 'payment')):
            continue
        normalized = re.sub(r'-\d+x\d+\.(webp|jpg|jpeg|png)$', r'-600x600.\1', img)
        if normalized not in seen:
            seen.add(normalized)
            result.append(normalized)
    return result[:5]


def extract_description(html):
    m = re.search(r'id="tab-description"[^>]*>(.*?)</div>\s*</div>', html, re.DOTALL)
    if m:
        return clean_text(re.sub(r'<[^>]+>', ' ', m.group(1)))[:800]
    m = re.search(r'<meta name="description" content="([^"]+)"', html)
    if m:
        return clean_text(m.group(1))[:800]
    return ''


def extract_specs(html):
    specs = {}
    for tab_id in ['tab-specification', 'tab-attribute']:
        tab = re.search(rf'id="{tab_id}"[^>]*>(.*?)</table>', html, re.DOTALL)
        if tab:
            rows = re.findall(r'<tr[^>]*>(.*?)</tr>', tab.group(1), re.DOTALL)
            for row in rows:
                cells = re.findall(r'<td[^>]*>(.*?)</td>', row, re.DOTALL)
                if len(cells) >= 2:
                    key = clean_text(re.sub(r'<[^>]+>', '', cells[0]))
                    val = clean_text(re.sub(r'<[^>]+>', '', cells[1]))
                    if key and val and len(key) < 80:
                        specs[key] = val[:200]
            break
    return specs


KNOWN_BRANDS = [
    'Apple', 'Samsung', 'Xiaomi', 'Huawei', 'Sony', 'LG', 'Lenovo', 'Asus',
    'HP', 'Dell', 'Acer', 'Microsoft', 'Google', 'OnePlus', 'Realme', 'OPPO',
    'Motorola', 'Nokia', 'Philips', 'Bosch', 'Dyson', 'Braun', 'Panasonic',
    'JBL', 'Bose', 'Roborock', 'Redmi', 'POCO', 'Honor', 'Vivo', 'Tecno',
    'Cubot', 'Infinix', 'DJI', 'GoPro', 'Nikon', 'Canon', 'Fujifilm',
    'Insta360', 'Amazfit', 'Garmin', 'Fitbit', 'Segway', 'Proove',
    'Electrolux', 'Whirlpool', 'Indesit', 'Gorenje', 'Haier', 'Ariston',
    'Zanussi', 'Candy', 'Beko', 'Hisense', 'TCL', 'ARDESTO', 'Rowenta',
    'Tefal', 'Miele', 'AEG', 'Bissell', 'iRobot', 'Ecovacs', 'Midea',
    'Sharp', 'Toshiba', 'Hitachi', 'De\'Longhi', 'Nespresso', 'Krups',
    'Nomi', 'Maxcom', 'Nothing', 'Xiaomi', 'Redmi', 'Poco',
]


def extract_brand(html, name_uk):
    for b in KNOWN_BRANDS:
        if b.lower() in name_uk.lower():
            return b
    breadcrumb = re.findall(r'"name":\s*"([^"]+)",\s*"item"', html)
    for b_text in breadcrumb[1:3]:
        for b in KNOWN_BRANDS:
            if b.lower() in b_text.lower():
                return b
    return None


def scrape_product(url):
    html = get_html(url)
    if not html or 'price-new' not in html:
        return None

    m = re.search(r'<h1[^>]*>([^<]+)</h1>', html)
    if not m:
        return None
    name_uk = clean_text(m.group(1))
    if not name_uk or len(name_uk) < 4:
        return None

    price = extract_price(html)
    if not price:
        return None

    old_price = extract_old_price(html)
    images = extract_images(html)
    description = extract_description(html)
    specs = extract_specs(html)
    brand = extract_brand(html, name_uk)

    # Slug from URL filename without .html
    slug = re.sub(r'\.html$', '', url.split('/')[-1])

    return {
        'name_uk': name_uk,
        'brand': brand,
        'slug': slug,
        'source_url': url,
        'price': round(price, 2),
        'old_price': round(old_price, 2) if old_price else None,
        'images': images,
        'description_uk': description,
        'specs': specs,
    }


def generate_reviews():
    return [
        {
            'rating': random.randint(4, 5),
            'title': random.choice(REVIEW_TITLES),
            'body': random.choice(REVIEW_BODIES),
            'author': random.choice(REVIEWER_NAMES),
        }
        for _ in range(random.randint(1, 5))
    ]


def main():
    all_data = []

    for cat in CATEGORY_MAP:
        print(f'\n=== {cat["our_slug"]} ({cat["sota_url"]}) ===')
        links = collect_product_links(cat['sota_url'], limit=20)
        print(f'  Collected {len(links)} product links')

        products = []
        for i, link in enumerate(links, 1):
            slug_preview = link.split('/')[-1][:45]
            print(f'  [{i}/{len(links)}] {slug_preview}')
            product = scrape_product(link)
            if product:
                product['reviews'] = generate_reviews()
                products.append(product)
                print(f'    ✓ {product["name_uk"][:50]} | {product["price"]} UAH | imgs={len(product["images"])}')
            else:
                print(f'    ✗ skipped')
            if len(products) >= 20:
                break

        print(f'  → {len(products)} products scraped')
        all_data.append({
            'our_slug': cat['our_slug'],
            'sub_slugs': cat['sub_slugs'],
            'products': products,
        })
        time.sleep(random.uniform(0.5, 1.5))

    output = '/home/roma/filkx/tech.filkx.com/scraper/products.json'
    with open(output, 'w', encoding='utf-8') as f:
        json.dump(all_data, f, ensure_ascii=False, indent=2)

    total = sum(len(c['products']) for c in all_data)
    print(f'\nDone! {total} products → {output}')


if __name__ == '__main__':
    main()
