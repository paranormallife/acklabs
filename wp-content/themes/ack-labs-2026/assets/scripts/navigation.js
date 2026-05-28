/**
 * Ack Labs 2026 — Navigation
 * Handles: hamburger menu toggle, mobile sub-menu tap, close-on-outside-click.
 * Desktop sub-menus are handled by CSS hover (no JS needed).
 */
(function () {
	'use strict';

	var nav    = document.getElementById( 'site-nav' );
	var toggle = nav ? nav.querySelector( '.nav-toggle' ) : null;

	if ( ! nav || ! toggle ) return;

	// ── Hamburger toggle ──────────────────────────────────────────────────
	toggle.addEventListener( 'click', function () {
		var isOpen = nav.classList.toggle( 'nav-open' );
		toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
	} );

	// ── Mobile: tap parent items to toggle sub-menus ──────────────────────
	var parentItems = nav.querySelectorAll( '.nav-links li.menu-item-has-children > a' );

	parentItems.forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			// Only intercept on small screens (hamburger mode)
			if ( window.innerWidth >= 640 ) return;

			e.preventDefault();
			var li = link.parentElement;
			var isSubmenuOpen = li.classList.contains( 'submenu-open' );

			// Close any other open sub-menus at the same level
			var siblings = nav.querySelectorAll( '.nav-links > li.submenu-open' );
			siblings.forEach( function ( s ) { s.classList.remove( 'submenu-open' ); } );

			if ( ! isSubmenuOpen ) {
				li.classList.add( 'submenu-open' );
			}
		} );
	} );

	// ── Close nav when a leaf link is clicked ─────────────────────────────
	var leafLinks = nav.querySelectorAll( '.nav-links a:not(.menu-item-has-children > a)' );

	leafLinks.forEach( function ( link ) {
		link.addEventListener( 'click', function () {
			if ( window.innerWidth >= 640 ) return;
			nav.classList.remove( 'nav-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		} );
	} );

	// ── Close nav on outside click ────────────────────────────────────────
	document.addEventListener( 'click', function ( e ) {
		if ( nav.classList.contains( 'nav-open' ) && ! nav.contains( e.target ) ) {
			nav.classList.remove( 'nav-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		}
	} );

	// ── Close nav on Escape key ───────────────────────────────────────────
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && nav.classList.contains( 'nav-open' ) ) {
			nav.classList.remove( 'nav-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.focus();
		}
	} );
}() );
