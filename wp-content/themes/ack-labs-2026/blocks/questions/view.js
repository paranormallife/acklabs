/**
 * acklabs/questions — Frontend view script (accordion interactivity)
 * Loaded only on pages that contain this block.
 */
(function () {
	'use strict';

	document.querySelectorAll( '.questions' ).forEach( function ( container ) {
		var questions = container.querySelectorAll( '.question' );

		questions.forEach( function ( q ) {
			var trigger = q.querySelector( '.question-trigger' );
			if ( ! trigger ) return;

			trigger.addEventListener( 'click', function () {
				var isOpen = q.classList.contains( 'open' );

				// Close all open items in this container
				questions.forEach( function ( other ) {
					other.classList.remove( 'open' );
					var t = other.querySelector( '.question-trigger' );
					if ( t ) t.setAttribute( 'aria-expanded', 'false' );
				} );

				// Open clicked item if it was closed
				if ( ! isOpen ) {
					q.classList.add( 'open' );
					trigger.setAttribute( 'aria-expanded', 'true' );
				}
			} );

			// Keyboard accessibility
			trigger.setAttribute( 'tabindex', '0' );
			trigger.setAttribute( 'role', 'button' );
			trigger.setAttribute( 'aria-expanded', 'false' );

			trigger.addEventListener( 'keydown', function ( e ) {
				if ( e.key === 'Enter' || e.key === ' ' ) {
					e.preventDefault();
					trigger.click();
				}
			} );
		} );
	} );
}() );
