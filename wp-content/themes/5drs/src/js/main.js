/**
 * 5drs theme entry point.
 *
 * Styles are imported here so the Vite dev server can hot-reload them; the
 * production build still emits a standalone CSS file that PHP enqueues.
 */

import '../css/main.css';

/**
 * Current document direction. Prefer CSS logical properties over branching on
 * this - it exists for the rare case where JS genuinely needs to know.
 *
 * @returns {'rtl'|'ltr'} Document direction.
 */
export function direction() {
	return document.documentElement.getAttribute( 'dir' ) === 'rtl' ? 'rtl' : 'ltr';
}

document.addEventListener( 'DOMContentLoaded', () => {
	document.documentElement.classList.add( 'js' );
} );
