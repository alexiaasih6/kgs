import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import fs from 'fs';
import path from 'path';
import {defineConfig} from 'vite';

export default defineConfig(() => {
  return {
    plugins: [
      react(),
      tailwindcss(),
      {
        name: 'kgs-live-export-sync',
        configureServer(server) {
          server.middlewares.use('/api/sync-live-export', (req, res, next) => {
            if (req.method !== 'POST') return next();
            let body = '';
            req.on('data', chunk => {
              body += chunk;
            });
            req.on('end', () => {
              try {
                const parsed = JSON.parse(body);
                const formatted = JSON.stringify(parsed, null, 2);
                const phpConfigPath = path.resolve(__dirname, 'php-app/config/live-preview-state.json');
                const rootConfigPath = path.resolve(__dirname, 'config/live-preview-state.json');
                fs.mkdirSync(path.dirname(phpConfigPath), {recursive: true});
                fs.mkdirSync(path.dirname(rootConfigPath), {recursive: true});
                fs.writeFileSync(phpConfigPath, formatted, 'utf8');
                fs.writeFileSync(rootConfigPath, formatted, 'utf8');
                res.setHeader('Content-Type', 'application/json');
                res.end(JSON.stringify({ok: true, buildId: parsed.buildId}));
              } catch (err) {
                res.statusCode = 400;
                res.setHeader('Content-Type', 'application/json');
                res.end(JSON.stringify({ok: false, error: String(err)}));
              }
            });
          });
        },
      },
    ],
    resolve: {
      alias: {
        '@': path.resolve(__dirname, '.'),
      },
    },
    server: {
      // HMR is disabled in AI Studio via DISABLE_HMR env var.
      // Do not modify—file watching is disabled to prevent flickering during agent edits.
      hmr: process.env.DISABLE_HMR !== 'true',
      // Disable file watching when DISABLE_HMR is true to save CPU during agent edits.
      watch: process.env.DISABLE_HMR === 'true' ? null : {},
    },
  };
});
