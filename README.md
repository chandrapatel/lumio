# Lumio

Lumio is a clean WordPress block theme built for writers. Fluid typography, generous whitespace, and a focused reading experience — for any blog, any topic.

- **Version:** 1.1.0
- **Author:** Chandra Patel
- **Requires WordPress:** 7.1+
- **Requires PHP:** 8.2+
- **License:** GPL v2 or later

---

## Disclaimer

I built Lumio for my personal blog. It hasn't been tested thoroughly across environments and likely contains bugs. Expect rough edges.

---

## Features

- **Full Site Editing** — Block-based templates and template parts
- **Fluid Typography** — All font sizes scale smoothly between viewport widths using `clamp()`, no breakpoints needed
- **Writer-Focused Layout** — 740px content width, 1.75 line-height, and generous spacing optimized for long-form reading
- **Self-Hosted Fonts** — Outfit, Fira Sans, and Fira Code loaded locally; no Google Fonts requests
- **Style Variations** — A second look, Liquid Glass, selectable in the Site Editor; its CSS and JS load only while it is active

---

## Style Variations

### Liquid Glass

Selected under **Site Editor → Styles**. Translucent lens-rimmed surfaces over a soft colour field, sliding pill controls, a floating header bar, and an airier spacing rhythm. The indigo brand and the Outfit / Fira type pairing carry over unchanged — `styles/liquid.json` overrides only the tokens that actually differ.

| File | Role |
|---|---|
| `styles/liquid.json` | Spacing scale, `settings.custom` tokens, and the block/element styles theme.json can express |
| `assets/css/liquid.css` | Everything needing a selector theme.json cannot reach — glass surfaces, the header pill, hover states |
| `assets/js/liquid.js` | Two progressive enhancements: the navigation pill that slides between items, and the specular highlight that tracks the pointer across cards |

Both assets load on the front end only while the variation is active. The stylesheet also loads inside the editor canvas so the editor matches the site; the script does not, since neither behaviour it adds applies there.

Detection does not read the variation file. Applying a variation merges its contents into the site's global styles and discards the label, so `liquid.json` carries a `settings.custom.styleSlug` sentinel that `lumio_is_liquid_style()` looks for in the merged settings. That also survives any customisation made on top of the variation.

Neither asset is required for the page to work. With JavaScript off, navigation items keep the per-item hover pill that `liquid.css` draws on its own, and the cards simply do not glint.

Two consequences worth knowing:

- **Preset arrays are replaced, not merged.** `WP_Theme_JSON` swaps `color.palette`, `typography.fontSizes`, `fontFamilies` and `spacingSizes` wholesale, so a variation that redefines one must list every slug in it. `liquid.json` redefines `spacingSizes` and therefore carries all nine.
- **Tokens added to the variation later do not reach a site that already applied it.** The merge happens once, into the `wp_global_styles` post. For that reason `liquid.css` pairs the tokens it cannot count on with a literal fallback.

The variation redefines the spacing scale from slug `3` upward, roughly 1.3–1.5× larger at the top end than the default:

| Preset | Default | Liquid Glass |
|---|---|---|
| `3` | 12px | 14px |
| `4` | 16px | 20px |
| `5` | 24px | 28px |
| `6` | 32px | clamp(28px, 3.2vw, 40px) |
| `7` | 48px | clamp(40px, 5vw, 64px) |
| `8` | 64px | clamp(56px, 7vw, 96px) |
| `9` | 96px | clamp(64px, 9vw, 128px) |

---

## Custom Block Classes — Required

Several core blocks in the templates carry a `lumio-*` class in their block attributes. **These are not decorative.** Neither stylesheet can reach those blocks any other way: core group, columns and paragraph blocks render with generic class names, and the alternative — matching on structural position — would break the moment a block moved.

Removing a class silently drops the styling it carries. Nothing errors, and the editor shows no warning; the page simply renders unstyled in that spot. This matters most when editing a template in the Site Editor, where it is easy to delete a group and rebuild it without the class, or to paste over a block from elsewhere.

| Class | Where | Needed by |
|---|---|---|
| `lumio-header-bar` | `parts/header.html` | `liquid.css` — the floating glass header pill |
| `lumio-footer-bar` | `parts/footer.html` | `liquid.css` — the footer slab |
| `lumio-post-row` | `index.html`, `search.html` | `liquid.css`, `liquid.js` — post card surface and pointer highlight |
| `lumio-archive-row` | `archive`, `category`, `tag`, `author` | `liquid.css`, `liquid.js` — as above, for the compact archive row |
| `lumio-page-header` | `archive`, `category`, `tag`, `author`, `search`, `page` | `liquid.css` — drops the heading rule that glass does not need |
| `lumio-author-bio` | `single.html` | `liquid.css`, `liquid.js` — author card surface |
| `lumio-error-columns`, `lumio-error-eyebrow`, `lumio-browse-all` | `404.html` | `style.css` — 404 layout and its supporting type |
| `lumio-error-num` | `404.html` | `style.css`, `liquid.css` — the oversized numeral and its outline |
| `lumio-error-section` | `404.html` | `liquid.css` — drops the rule under the error block |
| `lumio-recent-card` | `404.html` | `style.css`, `liquid.css`, `liquid.js` — recent-post cards |

`lumio-post-header`, `lumio-post-byline`, `lumio-post-tags`, `lumio-recent-section` and `lumio-recent-grid` are hooks with no rules attached to them today. They are safe to remove, but they are also the obvious handles for styling those areas later.

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
| Header | `parts/header.html` | Site header with logo, site title, and navigation |
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

Slugs are usage-based, so swapping in a different palette doesn't make the names misleading. All colors live in `theme.json`; `style.css` consumes them via the WP-generated `--wp--preset--color--*` CSS variables.

| Token | Value | Role |
|---|---|---|
| `text` | `#0d0d10` | Primary text, headings |
| `text-body` | `#212126` | Body paragraphs, lists, tables |
| `text-secondary` | `#404048` | Navigation, excerpts, footer |
| `text-muted` | `#61616b` | Meta, captions, tagline |
| `text-subtle` | `#85858d` | Placeholders, eyebrows, separators |
| `text-on-dark` | `#e9e9ee` | Code/preformatted text |
| `background` | `#eef0fb` | Page background |
| `background-alt` | `#e2e6f5` | Secondary background (e.g. 404 gradient) |
| `background-subtle` | `#f6f7fc` | Subtle fills (table headers, kbd, stripes) |
| `surface` | `#ffffff` | Cards, form inputs |
| `border` | `#d6daec` | Borders, dividers |
| `border-strong` | `#d2d2d5` | Form input borders |
| `brand-soft` | `#f0f0fc` | Light brand fill, mark, code background |
| `brand-200` | `#c5c5f5` | Brand tint — text-stroke, underline decoration |
| `brand-300` | `#9a9aee` | Brand tint — hover border |
| `brand` | `#4343d7` | Primary actions, buttons, links |
| `brand-hover` | `#23239f` | Link/button hover states |
| `brand-deep` | `#0e0e55` | Strongest brand shade |
| `link-visited` | `#4747d4` | Visited link decoration |
| `link-visited-deep` | `#2626a0` | Visited link color |

Border radii are exposed via `settings.custom.radius` as `--wp--custom--radius--default` (`10px`), `--wp--custom--radius--sm` (`6px`), and `--wp--custom--radius--xs` (`4px`).

Two more token groups live alongside them: `settings.custom.transition` (`--wp--custom--transition--fast` at `0.15s` for colour and border changes, `--wp--custom--transition--instant` at `0.05s` for the button active-state nudge) and `settings.custom.color` for the form error red (`--wp--custom--color--danger`, `--wp--custom--color--danger-soft`). The error colors sit under `custom` rather than the palette so they are not offered as content colors in the editor. Two shadow presets are defined in `settings.shadow.presets`: `--wp--preset--shadow--focus` for form focus rings and `--wp--preset--shadow--card-hover`.

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

The Liquid Glass variation redefines slugs `3` through `9`; see [Style Variations](#style-variations).

---

## Theme Functions

- **`lumio_theme_setup`** — Registers theme supports: wide alignment, editor styles, feed links, title tag, post thumbnails, and HTML5 markup
- **`lumio_enqueue_styles`** — Enqueues `style.css` with version-based cache busting
- **`lumio_modify_tag_archive_title`** — Prepends `#` to tag archive titles
- **`lumio_filter_query_title_block`** — Rebuilds the search results heading, wrapping the query term in a styleable `<span>`
- **`lumio_is_liquid_style`** — Reports whether the Liquid Glass variation is the active global style, by looking for its `styleSlug` sentinel in the merged settings
- **`lumio_asset_version`** — Builds a cache-busting version from the theme version plus the file's modification time, so an edited stylesheet always invalidates the cached copy
- **`lumio_enqueue_liquid_assets`** — Enqueues `liquid.css` and the deferred `liquid.js` on the front end while that variation is active
- **`lumio_enqueue_liquid_editor_style`** — Enqueues the same stylesheet inside the editor canvas. Uses `enqueue_block_assets` rather than `add_editor_style()`, which rewrites the `:root` and `body` selectors the file's token overrides depend on

---

## File Structure

```
lumio/
├── style.css           # Theme header, design tokens, component styles
├── theme.json          # Global settings and styles
├── functions.php       # Theme setup and hooks
├── styles/             # Style variations (1)
│   └── liquid.json     # Liquid Glass — tokens that differ from theme.json
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
    ├── css/
    │   └── liquid.css  # Liquid Glass — loaded only while that style is active
    ├── js/
    │   └── liquid.js   # Liquid Glass — nav pill and pointer highlight, no build step
    └── fonts/
        ├── outfit/
        ├── fira-sans/
        └── fira-code/
```
