Labelled text input with hint, error, and affixes.

```jsx
<Input label="Fahrgestellnummer (VIN)" placeholder="WVWZZZ…" required
       prefix={<i data-lucide="hash" />} />
<Input label="Wunschleistung" suffix="PS" type="number" />
```

Pass `error` to show the error state + message. `prefix`/`suffix` accept Lucide icons or short unit text.
