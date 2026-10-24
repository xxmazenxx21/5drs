/**
 * Contact form, enhanced.
 *
 * The form already works on its own: it posts to admin-post.php and comes back
 * with a status. This intercepts that and posts the same fields with fetch, so
 * the page does not reload - same endpoint, same handler, same validation.
 *
 * Every string shown here comes from PHP via wp_localize_script, so the Arabic
 * page shows Arabic without this file knowing either language.
 */

const config = () => window.fivedrsContact || {};
const strings = () => config().i18n || {};

/**
 * Show or clear the message under one field.
 *
 * @param {HTMLFormElement} form    The form.
 * @param {string}          field   Field key.
 * @param {string}          message Error text, or '' to clear.
 */
function setFieldError( form, field, message ) {
	const holder = form.querySelector( `[data-error-for="${ field }"]` );
	const input = form.querySelector( `[name="fivedrs_${ field }"]` );

	if ( holder ) {
		holder.textContent = message;
	}

	if ( input ) {
		if ( message ) {
			input.setAttribute( 'aria-invalid', 'true' );
		} else {
			input.removeAttribute( 'aria-invalid' );
		}
	}
}

const FIELDS = [ 'name', 'phone', 'email', 'message' ];

function clearErrors( form ) {
	FIELDS.forEach( ( field ) => setFieldError( form, field, '' ) );
}

/**
 * Put a message in the live region.
 *
 * @param {HTMLElement} status The status element.
 * @param {string}      text   Message.
 * @param {string}      tone   'success' or 'error'.
 */
function setStatus( status, text, tone ) {
	if ( ! status ) {
		return;
	}

	status.classList.remove( 'is-success', 'is-error' );
	status.textContent = text;

	if ( text ) {
		status.classList.add( tone === 'success' ? 'is-success' : 'is-error' );
	}
}

function bind( form ) {
	const status = document.querySelector( '[data-contact-status]' );
	const submit = form.querySelector( '[type="submit"]' );

	form.addEventListener( 'submit', ( event ) => {
		if ( ! config().endpoint ) {
			return; // No endpoint: let the browser post it normally.
		}

		event.preventDefault();
		clearErrors( form );
		setStatus( status, '', '' );

		const body = new FormData( form );
		body.append( 'fivedrs_ajax', '1' );

		if ( submit ) {
			submit.classList.add( 'is-loading' );
			submit.disabled = true;
		}

		window
			.fetch( config().endpoint, {
				method: 'POST',
				credentials: 'same-origin',
				body,
			} )
			.then( ( response ) => response.json() )
			.then( ( payload ) => {
				const data = ( payload && payload.data ) || {};
				const ok = Boolean( payload && payload.success );

				if ( submit ) {
					submit.classList.remove( 'is-loading' );
					submit.disabled = false;
				}

				if ( ok ) {
					form.reset();
					setStatus( status, data.message || strings().success || '', 'success' );

					if ( submit ) {
						submit.classList.add( 'is-done' );
						window.setTimeout( () => submit.classList.remove( 'is-done' ), 2000 );
					}

					// Announce it, and put the keyboard somewhere sensible.
					if ( status ) {
						status.focus();
					}
					return;
				}

				const errors = data.errors || {};
				Object.keys( errors ).forEach( ( field ) => setFieldError( form, field, errors[ field ] ) );

				setStatus( status, data.message || strings().error || '', 'error' );

				// Send focus to the first field that needs attention.
				const firstBad = FIELDS.find( ( field ) => errors[ field ] );
				const target = firstBad
					? form.querySelector( `[name="fivedrs_${ firstBad }"]` )
					: status;

				if ( target ) {
					target.focus();
				}
			} )
			.catch( () => {
				if ( submit ) {
					submit.classList.remove( 'is-loading' );
					submit.disabled = false;
				}
				setStatus( status, strings().error || '', 'error' );
				if ( status ) {
					status.focus();
				}
			} );
	} );
}

export default function initContact() {
	document.querySelectorAll( '[data-contact-form]' ).forEach( bind );

	// A status rendered by the no-JavaScript path still deserves focus.
	const status = document.querySelector( '[data-contact-status].is-success, [data-contact-status].is-error' );

	if ( status ) {
		status.focus();
	}
}
