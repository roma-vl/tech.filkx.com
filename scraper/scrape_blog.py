#!/usr/bin/env python3
"""
Scrapes 10 blog posts from sota.store/ua/blog (every 3rd post).
Outputs: scraper/blog_posts.json
"""

import re, json, time, random, requests

HEADERS = {
    'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept-Language': 'uk-UA,uk;q=0.9',
    'Accept': 'text/html,application/xhtml+xml,*/*;q=0.8',
}
BASE = 'https://sota.store'
LISTING_URL = 'https://sota.store/index.php?route=cms/blog&language=uk-ua'


def clean(s):
    s = re.sub(r'<[^>]+>', ' ', s)
    s = re.sub(r'&mdash;', '—', s)
    s = re.sub(r'&nbsp;', ' ', s)
    s = re.sub(r'&laquo;', '«', s)
    s = re.sub(r'&raquo;', '»', s)
    s = re.sub(r'&rsquo;|&#39;', "'", s)
    s = re.sub(r'&[a-z]+;', '', s)
    return re.sub(r'\s+', ' ', s).strip()


def get_html(url):
    time.sleep(random.uniform(0.4, 0.9))
    r = requests.get(url, headers=HEADERS, timeout=20)
    return r.text if r.status_code == 200 else ''


def get_listing_posts(url):
    """Returns list of (post_url, thumbnail_url) from a listing page."""
    html = get_html(url)
    pairs = re.findall(
        r'href=\"(https://sota\.store/ua/[^\"]+\.html)\"[^>]*>.*?<img[^>]+src=\"([^\"]+)\"',
        html, re.DOTALL
    )
    seen = set()
    result = []
    for link, img in pairs:
        if not re.search(r'-\d{3,}\.html$', link):
            continue
        if link in seen:
            continue
        seen.add(link)
        # Normalize thumbnail to 600x600
        img = re.sub(r'-\d+x\d+\.(webp|jpg|jpeg|png)$', r'-600x600.\1', img)
        if not img.startswith('http'):
            img = BASE + img
        result.append((link, img))
    return result


def scrape_post(url, thumbnail_url):
    html = get_html(url)
    if not html:
        return None

    # Title
    m = re.search(r'<h1[^>]*>([^<]+)</h1>', html)
    title = clean(m.group(1)) if m else ''

    # Date dd.mm.yyyy → yyyy-mm-dd
    m = re.search(r'\b(\d{2})\.(\d{2})\.(\d{4})\b', html)
    published_at = f'{m.group(3)}-{m.group(2)}-{m.group(1)}' if m else None

    # Slug from URL
    slug = re.sub(r'\.html$', '', url.split('/')[-1])

    # Content: collect all substantial paragraphs and headings
    content_parts = []

    # Find main content block around h1
    h1_pos = html.find('<h1')
    if h1_pos >= 0:
        region = html[h1_pos:h1_pos + 20000]
        elements = re.findall(r'<(p|h[2-4])[^>]*>(.*?)</\1>', region, re.DOTALL)
        for tag, body in elements:
            text = clean(body)
            if len(text) > 30:
                if tag.startswith('h'):
                    content_parts.append(f'<{tag}>{text}</{tag}>')
                else:
                    content_parts.append(f'<p>{text}</p>')
            if len(content_parts) >= 30:
                break

    content_html = '\n'.join(content_parts)

    # Excerpt: first meaningful paragraph
    excerpt = ''
    for part in content_parts:
        text = clean(part)
        if len(text) > 80 and not text.startswith('<h'):
            excerpt = re.sub(r'<[^>]+>', '', text)[:300]
            break

    # Tags: look for category/topic labels on the page
    tags = re.findall(
        r'class=\"[^\"]*(?:tag|topic|label)[^\"]*\"[^>]*>([^<]{3,40})</(?:a|span)',
        html, re.IGNORECASE
    )
    tags = [clean(t) for t in tags if len(clean(t)) > 2 and len(clean(t)) < 40]
    tags = list(dict.fromkeys(tags))[:5]

    return {
        'title': title,
        'slug': slug,
        'url': url,
        'published_at': published_at,
        'excerpt': excerpt,
        'content_html': content_html,
        'thumbnail_url': thumbnail_url,
        'tags': tags,
    }


def main():
    print('Collecting post URLs from listing pages...')
    all_posts = []

    # Collect from pages 1–3 (each page has ~10 posts → 30+ total)
    for page in range(1, 4):
        page_url = LISTING_URL + (f'&page={page}' if page > 1 else '')
        print(f'  Page {page}: {page_url}')
        posts = get_listing_posts(page_url)
        print(f'  Found {len(posts)} posts')
        all_posts.extend(posts)

    print(f'Total posts collected: {len(all_posts)}')

    # Pick every 3rd post (index 0, 3, 6, 9, ...) → 10 posts
    selected = all_posts[::3][:10]
    print(f'Selected {len(selected)} posts (every 3rd)')

    results = []
    for i, (url, thumb) in enumerate(selected, 1):
        print(f'\n[{i}/10] {url.split("/")[-1][:60]}')
        post = scrape_post(url, thumb)
        if post:
            print(f'  ✓ {post["title"][:60]}')
            print(f'  date={post["published_at"]} | content_parts={post["content_html"].count("<p>")}')
            results.append(post)
        else:
            print('  ✗ failed')

    output = '/home/roma/filkx/tech.filkx.com/scraper/blog_posts.json'
    with open(output, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)

    print(f'\nDone! {len(results)} posts → {output}')


if __name__ == '__main__':
    main()
