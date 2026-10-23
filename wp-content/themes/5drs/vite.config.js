import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { mkdirSync, writeFileSync, rmSync } from 'node:fs';
import { resolve } from 'node:path';

const THEME_PATH = '/wp-content/themes/5drs/';
const OUT_DIR = 'dist';
const HOT_FILE = resolve( import.meta.dirname, OUT_DIR, '.vite-dev-server' );

/**
 * Writes dist/.vite-dev-server while `npm run dev` runs, so inc/assets.php
 * knows to load assets from the dev server instead of the build output.
 */
function devServerMarker() {
	return {
		name: 'fivedrs-dev-server-marker',
		apply: 'serve',
		configureServer( server ) {
			server.httpServer?.once( 'listening', () => {
				const address = server.httpServer.address();
				const port = typeof address === 'object' ? address.port : 5173;
				mkdirSync( resolve( import.meta.dirname, OUT_DIR ), { recursive: true } );
				writeFileSync( HOT_FILE, `http://localhost:${ port }` );
			} );

			const cleanup = () => {
				try {
					rmSync( HOT_FILE, { force: true } );
				} catch {
					/* nothing to clean up */
				}
			};

			process.on( 'exit', cleanup );
			process.on( 'SIGINT', () => {
				cleanup();
				process.exit( 0 );
			} );
			process.on( 'SIGTERM', () => {
				cleanup();
				process.exit( 0 );
			} );
		},
	};
}

export default defineConfig( {
	base: `${ THEME_PATH }${ OUT_DIR }/`,
	plugins: [ tailwindcss(), devServerMarker() ],
	build: {
		outDir: OUT_DIR,
		emptyOutDir: true,
		manifest: true,
		sourcemap: false,
		rollupOptions: {
			input: {
				main: resolve( import.meta.dirname, 'src/js/main.js' ),
				style: resolve( import.meta.dirname, 'src/css/main.css' ),
			},
		},
	},
	server: {
		host: '0.0.0.0',
		port: 5173,
		strictPort: true,
		cors: true,
		origin: 'http://localhost:5173',
	},
} );
