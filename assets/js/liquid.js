/**
 * Lumio · Liquid Glass — interaction layer.
 *
 * Loaded only while the Liquid Glass style variation is active. No dependencies,
 * no build step: ES5 syntax throughout, and every DOM API used here (classList,
 * closest, matchMedia, requestAnimationFrame, CSS custom properties, passive
 * listeners) is available in every browser WordPress 7.1 supports.
 *
 * Two behaviours, each a progressive enhancement over a working CSS baseline:
 *
 *   1. One glass pill slides between the top-level navigation items.
 *   2. A specular highlight tracks the pointer across each glass card.
 *
 * Nothing here is required for the style to work. With JavaScript off, navigation
 * items keep the per-item hover pill that liquid.css draws on its own, and the
 * cards simply do not glint.
 *
 * The resize and pointermove handlers coalesce into requestAnimationFrame: those
 * events can fire many times per frame, but the paint only changes once.
 */
( function () {
	'use strict';

	var coarsePointer = window.matchMedia( '(hover: none)' );

	/**
	 * Run a callback at most once per animation frame.
	 *
	 * @param {Function} callback Work to perform on the next frame.
	 * @return {Function} Handler safe to attach to a high-frequency event.
	 */
	function perFrame( callback ) {
		var queued = false;

		return function () {
			if ( queued ) {
				return;
			}

			queued = true;

			window.requestAnimationFrame( function () {
				queued = false;
				callback();
			} );
		};
	}

	/**
	 * Slide a single glass pill between the top-level navigation items.
	 *
	 * The pill is appended to the <nav> rather than the <ul>, which may only
	 * contain list items, and positions itself against the nav's own box.
	 *
	 * Position animates as a transform; only the width is a layout property, and
	 * the pill is absolutely positioned, so no sibling is ever reflowed by it.
	 *
	 * @param {Element} nav The navigation block inside the header.
	 * @return {void}
	 */
	function initNavInk( nav ) {
		var list = nav.querySelector( '.wp-block-navigation__container' );

		if ( ! list ) {
			return;
		}

		var ink = document.createElement( 'span' );

		ink.className = 'lumio-nav-ink';
		ink.setAttribute( 'aria-hidden', 'true' );
		nav.appendChild( ink );
		nav.classList.add( 'lumio-has-ink' );

		/**
		 * Whether the pill applies to the navigation's current presentation.
		 *
		 * It does not once the mobile overlay takes over: the items become a
		 * full-screen vertical stack positioned outside the nav's own box, and
		 * liquid.css hands the per-item hover pill back for that case.
		 *
		 * @return {boolean} True when the horizontal bar is on screen.
		 */
		function isAvailable() {
			if ( nav.querySelector( '.wp-block-navigation__responsive-container.is-menu-open' ) ) {
				return false;
			}

			return !! list.offsetParent;
		}

		/* The item the pill currently sits under, so a hover that bubbles up from
		   an item's inner label does not re-measure what has not moved. */
		var tracked = null;

		function hide() {
			ink.style.opacity = '0';
			tracked = null;
		}

		/**
		 * Move the pill behind one navigation item.
		 *
		 * @param {Element} link The item's anchor or submenu toggle.
		 * @return {void}
		 */
		function moveTo( link ) {
			if ( ! link || ! isAvailable() ) {
				hide();
				return;
			}

			var navBox = nav.getBoundingClientRect();
			var box = link.getBoundingClientRect();

			if ( ! box.width ) {
				hide();
				return;
			}

			ink.style.setProperty( '--lumio-ink-x', box.left - navBox.left + 'px' );
			ink.style.setProperty( '--lumio-ink-y', box.top - navBox.top + 'px' );
			ink.style.setProperty( '--lumio-ink-w', box.width + 'px' );
			ink.style.setProperty( '--lumio-ink-h', box.height + 'px' );
			ink.style.opacity = '1';
			tracked = link;
		}

		/**
		 * Return the pill to the current menu item, or park it out of sight.
		 *
		 * Always re-measures: this also runs after a resize or a webfont swap,
		 * when the item has not changed but its box has.
		 *
		 * @return {void}
		 */
		function settle() {
			tracked = null;
			moveTo( list.querySelector( '.current-menu-item > .wp-block-navigation-item__content' ) );
		}

		/**
		 * Resolve an event target to the top-level item it belongs to.
		 *
		 * Submenu links are ignored: they sit in their own floating slab, which
		 * the pill has no business reaching into.
		 *
		 * @param {EventTarget} target The event's target.
		 * @return {Element|null} The top-level item's content element.
		 */
		function topLevelItem( target ) {
			if ( ! target || ! target.closest ) {
				return null;
			}

			var link = target.closest( '.wp-block-navigation-item__content' );

			if ( ! link || ! link.parentNode || link.parentNode.parentNode !== list ) {
				return null;
			}

			return link;
		}

		function track( event ) {
			var link = topLevelItem( event.target );

			if ( link && link !== tracked ) {
				moveTo( link );
			}
		}

		list.addEventListener( 'mouseover', track );
		list.addEventListener( 'focusin', track );
		nav.addEventListener( 'mouseleave', settle );
		nav.addEventListener( 'focusout', settle );
		window.addEventListener( 'resize', perFrame( settle ) );

		settle();

		/* Transitions start only after the first placement, so the pill does not
		   slide in from the corner on page load. */
		window.requestAnimationFrame( function () {
			ink.classList.add( 'is-ready' );
		} );

		/* A late-arriving webfont changes every item's width. */
		if ( document.fonts && document.fonts.ready ) {
			document.fonts.ready.then( settle );
		}
	}

	/**
	 * Track the pointer across the glass cards so each one glints under it.
	 *
	 * One delegated listener covers every card on the page, including those a
	 * future template adds. Skipped outright on touch, where liquid.css hides the
	 * highlight anyway.
	 *
	 * @return {void}
	 */
	function initSpecular() {
		if ( coarsePointer.matches ) {
			return;
		}

		var SELECTOR = '.lumio-post-row, .lumio-archive-row, .lumio-recent-card, .lumio-author-bio';
		var pending = null;

		var paint = perFrame( function () {
			if ( ! pending ) {
				return;
			}

			var card = pending.card;
			var box = card.getBoundingClientRect();

			card.style.setProperty( '--lumio-mx', ( pending.x - box.left ) / box.width * 100 + '%' );
			card.style.setProperty( '--lumio-my', ( pending.y - box.top ) / box.height * 100 + '%' );

			pending = null;
		} );

		document.addEventListener( 'pointermove', function ( event ) {
			if ( 'touch' === event.pointerType || ! event.target || ! event.target.closest ) {
				return;
			}

			var card = event.target.closest( SELECTOR );

			if ( ! card ) {
				return;
			}

			pending = { card: card, x: event.clientX, y: event.clientY };
			paint();
		}, { passive: true } );
	}

	function init() {
		var nav = document.querySelector( '.site-header .wp-block-navigation' );

		if ( nav ) {
			initNavInk( nav );
		}

		initSpecular();
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
}() );
