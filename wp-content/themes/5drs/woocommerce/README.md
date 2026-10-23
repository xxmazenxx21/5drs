# WooCommerce template overrides

This folder is **still empty, on purpose**. The whole store is styled without a
single template override.

## How the store is currently styled

- **CSS** — `src/css/woocommerce.css` targets WooCommerce's own markup and
  restyles it with the brand tokens: grid, product card, single product, cart,
  checkout, my-account, buttons, inputs, notices, prices, sale badge,
  pagination. WooCommerce ships physical `left`/`right` rules; that file
  restates them logically so the store mirrors correctly in Arabic.
- **Hooks** — `inc/woocommerce.php` swaps WooCommerce's `#primary .content-area`
  wrapper for the theme's container (`woocommerce_before_main_content` /
  `woocommerce_after_main_content`), renders the shared page hero on archives,
  turns off the duplicated archive title, and registers the header cart badge as
  a cart fragment.

## The rule

1. **Try a hook first.** Almost everything WooCommerce renders is wrapped in
   `do_action()` / `apply_filters()`. Add the change to `inc/woocommerce.php`
   instead of copying a template. Hooks survive WooCommerce updates; copied
   templates do not.
2. **Only copy the template you actually change.** Copy a single file from
   `wp-content/plugins/woocommerce/templates/<path>` to
   `wp-content/themes/5drs/woocommerce/<path>`, keeping the same relative path.
   Never copy the whole `templates/` directory.
3. **Record the version.** Keep the `@version x.y.z` line in the copied file's
   header and re-diff it against the plugin's template whenever WooCommerce is
   updated. `WooCommerce → Status` flags overrides that have gone stale.
4. **Log it here.** Add the file to the table below with one line saying why a
   hook could not do the job.
5. **Keep it bilingual.** Any string you add must be wrapped in a translation
   function with the `5drs` text domain, and any layout must use CSS logical
   properties so it mirrors correctly in Arabic.

## Overrides in this theme

| Template | WooCommerce version when copied | Why a hook was not enough |
|---|---|---|
| _none_ | — | — |

## Cart and checkout

The Cart and Checkout pages use the classic shortcodes `[woocommerce_cart]`
and `[woocommerce_checkout]` rather than the block equivalents, specifically so
that these PHP templates are the ones rendering. Do not convert those pages
back to blocks.
