# Lumio

Lumio is a clean WordPress block theme built for writers. Fluid typography, generous whitespace, and a focused reading experience — for any blog, any topic.

- **Version:** 1.0.0
- **Author:** Chandra Patel
- **Requires WordPress:** 6.9+
- **Requires PHP:** 8.2+
- **License:** GPL v2 or later

---

## Features

- **Full Site Editing** — Block-based templates and template parts
- **Fluid Typography** — All font sizes scale smoothly between viewport widths using `clamp()`, no breakpoints needed
- **Writer-Focused Layout** — 740px content width, 1.75 line-height, and generous spacing optimized for long-form reading
- **Self-Hosted Fonts** — Outfit, Fira Sans, and Fira Code loaded locally; no Google Fonts requests

---

## Templates

| Template | Slug | Description |
|---|---|---|
| Index (Blog Home) | `index` | Featured hero post + paginated post list |
| Single Post | `single` | Full post with byline, featured image, tags, author bio, navigation, and comments |
| Page | `page` | Static page — title, featured image, content |
| Archive | `archive` | Generic date/taxonomy archive with date · title · category column layout |
| Category | `category` | Category archive with term description |
| Tag | `tag` | Tag archive; titles prefixed with `#` |
| Author | `author` | Author avatar, bio, and posts |
| Search | `search` | Search form + results sorted by relevance |
| 404 | `404` | Not-found page with search, quick links, recent posts, and decorative 404 graphic |

---

## Template Parts

| Part | File | Description |
|---|---|---|
| Header | `parts/header.html` | Sticky site header with logo, site title, and navigation |
| Footer | `parts/footer.html` | Site title, font credits, and WordPress attribution |

---

## Typography

Three self-hosted font families are included under `assets/fonts/`.

| Family | Role | Weights |
|---|---|---|
| **Outfit** | Headings, site title | 400, 700 |
| **Fira Sans** | Body, navigation, metadata | 400 (+ italic), 500, 600, 700 |
| **Fira Code** | Code, captions, dates | 400, 500 |

All fonts use `font-display: swap`.

### Font Size Scale

| Preset | Size | Usage |
|---|---|---|
| `xs` | clamp(0.625rem → 0.75rem) | Captions, metadata |
| `sm` | clamp(0.8125rem → 0.875rem) | Small print, footnotes |
| `base` | clamp(1rem → 1.125rem) | Body copy |
| `md` | clamp(1.25rem → 1.4375rem) | Lead paragraphs |
| `lg` | clamp(1.5625rem → 1.75rem) | H3, subheadings |
| `xl` | clamp(1.9375rem → 2.1875rem) | H2, section headings |
| `2xl` | clamp(2.4375rem → 2.75rem) | H1, post titles |

---

## Color Palette

| Token | Value | Role |
|---|---|---|
| `ink` | `#0d0d10` | Primary text |
| `paper` | `#eef0fb` | Page background |
| `paper-2` | `#e2e6f5` | Secondary background |
| `paper-3` | `#f6f7fc` | Subtle backgrounds |
| `surface` | `#ffffff` | Cards, overlays |
| `rule` | `#d6daec` | Borders, dividers |
| `brand` | `#4343d7` | Primary actions, links |
| `brand-soft` | `#f0f0fc` | Light accent fills |
| `brand-deep` | `#0e0e55` | Dark accent |

A 5-step neutral scale (`neutral-200` → `neutral-700`) and two violet shades (`violet-500`, `violet-600`) are also included for secondary text and visited links.

---

## Layout

| Setting | Value |
|---|---|
| Content width | 740px |
| Wide width | 960px |

---

## Spacing Scale

| Preset | Value |
|---|---|
| `1` | 4px |
| `2` | 8px |
| `3` | 12px |
| `4` | 16px |
| `5` | 24px (default block gap) |
| `6` | 32px |
| `7` | 48px |
| `8` | 64px |
| `9` | 96px |

---

## Theme Functions

- **`lumio_theme_setup`** — Registers theme supports: wide alignment, editor styles, feed links, title tag, post thumbnails, and HTML5 markup
- **`lumio_enqueue_styles`** — Enqueues `style.css` with version-based cache busting
- **`lumio_modify_tag_archive_title`** — Prepends `#` to tag archive titles
- **`lumio_filter_query_title_block`** — Cleans up the search results heading and wraps the query term in a styleable `<span>`
- **`lumio_sticky_header_styles`** — Outputs a targeted CSS fix to prevent ancestor `overflow: hidden` from clipping the sticky header

---

## File Structure

```
lumio/
├── style.css           # Theme header, design tokens, component styles
├── theme.json          # Global settings and styles
├── functions.php       # Theme setup and hooks
├── templates/          # Page templates (9)
│   ├── index.html
│   ├── single.html
│   ├── page.html
│   ├── archive.html
│   ├── category.html
│   ├── tag.html
│   ├── author.html
│   ├── search.html
│   └── 404.html
├── parts/              # Template parts (2)
│   ├── header.html
│   └── footer.html
└── assets/
    └── fonts/
        ├── outfit/
        ├── fira-sans/
        └── fira-code/
```
