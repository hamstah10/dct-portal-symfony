import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
  // Wichtig für UMD-Einbindung per normalem <script>:
  // React enthält sonst teilweise process.env.NODE_ENV und der Browser kennt process nicht.
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
    'process.env': {},
  },
  plugins: [react()],
  build: {
    cssCodeSplit: false,
    lib: {
      entry: 'src/entry.tsx',
      name: 'TfdPartnerWizard',
      formats: ['umd', 'es'],
      fileName: (format) => format === 'umd'
        ? 'tfd-partner-wizard.umd.js'
        : 'tfd-partner-wizard.es.js',
    },
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return 'style.css';
          }
          return '[name][extname]';
        },
      },
    },
  },
});
