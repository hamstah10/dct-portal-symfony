Data table for file lists, orders, and vehicle results.

```jsx
<Table
  columns={[
    {key:'file', header:'Datei'},
    {key:'ps', header:'PS', numeric:true},
    {key:'status', header:'Status', render:r=><Badge tone={r.tone}>{r.status}</Badge>},
  ]}
  rows={orders} />
```

Mark `numeric` columns to right-align with tabular mono numerals. Use `render` for badges/actions.
