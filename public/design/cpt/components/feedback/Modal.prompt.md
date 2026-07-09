Centered dialog for confirmations, vehicle details, and forms.

```jsx
<Modal open={open} onClose={()=>setOpen(false)} title="Bestellung bestätigen"
  footer={<><Button variant="ghost" onClick={close}>Abbrechen</Button><Button>Kostenpflichtig bestellen</Button></>}>
  <p>Stage 1 für deinen 2.0 TDI — Lieferung innerhalb von 30 Minuten.</p>
</Modal>
```

Closes on Esc or scrim click. Sizes `sm`/`md`/`lg`.
