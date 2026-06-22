/**
 * @copyright Copyright (c) 2024 T-Systems International
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Shows a favorite star indicator on favorited files/folders in unified search results.
 */

const LOG = '[nmc-search-favorites]'

let favoriteIdsPromise = null

function getRequestToken() {
	return document.head.dataset.requesttoken
		?? window.OC?.requestToken
		?? ''
}

function getUserId() {
	return window.OC?.currentUser ?? ''
}

/**
 * Fetches all favorited file IDs for the current user in a single REPORT request.
 * Result is cached for the page lifetime.
 */
async function loadFavoriteIds() {
	if (favoriteIdsPromise) return favoriteIdsPromise

	favoriteIdsPromise = (async () => {
		const userId = getUserId()
		if (!userId) return new Set()

		const url = `/remote.php/dav/files/${encodeURIComponent(userId)}/`

		try {
			const response = await fetch(url, {
				method: 'REPORT',
				headers: {
					'Content-Type': 'application/xml; charset=utf-8',
					requesttoken: getRequestToken(),
				},
				body: '<?xml version="1.0"?>'
					+ '<oc:filter-files xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns">'
					+ '<d:prop><oc:fileid/><oc:favorite/></d:prop>'
					+ '<oc:filter-rules><oc:favorite>1</oc:favorite></oc:filter-rules>'
					+ '</oc:filter-files>',
				credentials: 'same-origin',
			})

			if (!response.ok) {
				console.warn(LOG, `REPORT failed: HTTP ${response.status}`)
				return new Set()
			}

			const text = await response.text()
			const doc = new DOMParser().parseFromString(text, 'application/xml')
			const ids = new Set()

			Array.from(doc.getElementsByTagNameNS('http://owncloud.org/ns', 'fileid'))
				.forEach(el => ids.add(el.textContent.trim()))

			return ids
		} catch (err) {
			console.error(LOG, 'Error loading favorites:', err)
			return new Set()
		}
	})()

	return favoriteIdsPromise
}

function addFavoriteMarker(item) {
	const iconEl = item.querySelector('.result-item__icon')
	if (!iconEl || iconEl.querySelector('.nmc-search-favorite')) return

	const marker = document.createElement('span')
	marker.className = 'nmc-search-favorite'
	marker.setAttribute('aria-hidden', 'true')
	iconEl.appendChild(marker)
}

async function processResultItem(item, favoriteIds) {
	item.dataset.nmcFavChecked = '1'

	const link = item.querySelector('a[href*="/index.php/f/"]')
	if (!link) return

	const match = link.href.match(/\/index\.php\/f\/(\d+)/)
	if (!match) return

	const fileId = match[1]
	if (favoriteIds.has(fileId)) {
		addFavoriteMarker(item)
	}
}

async function processUnprocessedItems() {
	const unprocessed = [
		...document.querySelectorAll('.result-item:not([data-nmc-fav-checked])'),
	]
	if (unprocessed.length === 0) return

	const favoriteIds = await loadFavoriteIds()
	unprocessed.forEach(item => processResultItem(item, favoriteIds))
}

window.addEventListener('DOMContentLoaded', () => {
	const observer = new MutationObserver(processUnprocessedItems)
	observer.observe(document.body, { childList: true, subtree: true })
})
