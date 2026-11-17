/**
 * @copyright Copyright (c) 2026 T-Systems International
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Separates the number from the percent sign in the image editor's transparency label
 * ("63%" -> "63 %"), matching the slider thumb next to it.
 *
 * The label is a single text node rendered by Filerobot (viewer app), so CSS cannot reach
 * inside it. React rewrites the node whenever the value changes, hence the observer.
 */

const LABEL_SELECTOR = '.FIE_annotation-option-popup .SfxSlider-root + .SfxLabel-root .SfxLabel-text'
const UNSPACED_PERCENT = /^(\d+)%$/

let frame = null

/** Inserts a non-breaking space before every unspaced percent sign in the label. */
function formatLabels() {
	frame = null

	for (const label of document.querySelectorAll(LABEL_SELECTOR)) {
		for (const node of label.childNodes) {
			if (node.nodeType === Node.TEXT_NODE && UNSPACED_PERCENT.test(node.data)) {
				node.data = node.data.replace(UNSPACED_PERCENT, '$1\u00A0%')
			}
		}
	}
}

/** Coalesces mutation bursts into one pass per frame. */
function schedule() {
	if (frame === null) {
		frame = window.requestAnimationFrame(formatLabels)
	}
}

/** Watches the whole body: the editor's popups are portalled outside the viewer. */
function observe() {
	new MutationObserver(schedule).observe(document.body, {
		childList: true,
		subtree: true,
		characterData: true,
	})
}

if (document.body) {
	observe()
} else {
	window.addEventListener('DOMContentLoaded', observe)
}
