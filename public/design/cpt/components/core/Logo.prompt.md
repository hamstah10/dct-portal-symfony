Brand lockup (mark + wordmark) for headers, footers, login.

```jsx
<Logo size="md" href="/" assetBase="../" />
<Logo size="lg" solidMark showWordmark={false} />
```

`assetBase` prefixes the path to `/assets` so the mark resolves from nested pages. Use `solidMark` on dark backgrounds. ⚠️ The mark is a placeholder — swap the SVGs in `assets/` for the official logo.
