Underline tab bar for switching views (Übersicht / Dateien / Bestellungen).

```jsx
<Tabs value={tab} onChange={setTab}
  tabs={[{id:'pkw',label:'PKW',icon:'car',count:8423},
         {id:'lkw',label:'LKW',icon:'truck',count:1290}]} />
```

Active tab carries the ignition underline. Counts render as mono chips.
