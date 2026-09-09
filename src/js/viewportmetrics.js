/**
 * @copyright Copyright (c) 2026 T-Systems International
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Publishes the visual viewport as CSS custom properties on <html>.
 *
 * An on-screen keyboard shrinks only the *visual* viewport. Per CSS Values 4 the
 * viewport-percentage units may ignore that, and Chrome on Android does - so `vh`, `dvh`
 * and `%` all keep their full height while the keyboard covers the lower ~45% of the
 * screen. `window.visualViewport` is the only source of truth for the visible region.
 *
 * Media queries cannot see the keyboard either, hence [data-nmc-viewport] for layout that
 * cannot be expressed as a length. Consumed in css/layouts/modal.scss.
 */

// Portrait phones with the keyboard up land around 330-530px and stay in 'tight', which
// keeps the filter row. Only landscape falls to 'minimal', where there is not enough
// height for both the filters and a usable result list.
const TIER_TIGHT = 560
const TIER_MINIMAL = 300

// Above this, the shrinking visual viewport is the user zooming in rather than a keyboard;
// clamping dialogs to it would leave them unusably small.
const MAX_TRACKED_SCALE = 1.01

let frame = null

/** Mirrors the visual viewport onto the document element. */
function publish() {
	frame = null

	const viewport = window.visualViewport
	const root = document.documentElement

	if (viewport.scale > MAX_TRACKED_SCALE) {
		root.style.removeProperty('--nmc-viewport-height')
		root.style.removeProperty('--nmc-viewport-offset-top')
		delete root.dataset.nmcViewport
		return
	}

	const height = Math.round(viewport.height)
	root.style.setProperty('--nmc-viewport-height', `${height}px`)
	root.style.setProperty('--nmc-viewport-offset-top', `${Math.round(viewport.offsetTop)}px`)

	if (height < TIER_MINIMAL) {
		root.dataset.nmcViewport = 'minimal'
	} else if (height < TIER_TIGHT) {
		root.dataset.nmcViewport = 'tight'
	} else {
		root.dataset.nmcViewport = 'roomy'
	}
}

/** Coalesces event bursts into one write per frame. */
function schedule() {
	if (frame === null) {
		frame = window.requestAnimationFrame(publish)
	}
}

// Without the API the stylesheet falls back to full-viewport sizing, i.e. what we had before.
if (window.visualViewport) {
	const viewport = window.visualViewport
	viewport.addEventListener('resize', schedule, { passive: true })
	viewport.addEventListener('scroll', schedule, { passive: true })
	window.addEventListener('orientationchange', schedule, { passive: true })

	publish()
	window.addEventListener('DOMContentLoaded', publish)
}
