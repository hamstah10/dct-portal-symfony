Drag-and-drop dropzone for ECU file submission.

```jsx
<FileUpload accept=".bin,.ori,.mod" onFiles={fs => upload(fs[0])} />
```

Highlights on drag-over with the ignition tint. Pair with `ProgressBar` for upload state.
