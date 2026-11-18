import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

// Dentro do Docker o Vite precisa escutar em 0.0.0.0 e avisar o navegador para usar localhost
const noDocker = process.env.VITE_DOCKER === '1';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        ...(noDocker ? { host: '0.0.0.0', port: 5173, strictPort: true, hmr: { host: 'localhost' } } : {}),
        watch: {
            ignored: ['**/storage/framework/views/**'],
            ...(noDocker ? { usePolling: true, interval: 300 } : {}),
        },
    },
});
