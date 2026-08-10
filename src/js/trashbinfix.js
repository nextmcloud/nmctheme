(function() {
	const clearSelection = () => {
		document.querySelector('[data-cy-files-list-selection-action="cancel_select"]')?.click()
	}

	// Nextcloud does not reset the Vue selection store after trashbin delete/restore.
	// We watch the table's class: when it gets files-list__table--hidden (empty state),
	// we click Cancel to clear the stale selection.
	// Note: window.fetch interception does not work here because the @nextcloud/webdav
	// library captures the fetch reference at module init time before our script runs.
	const watchTableState = () => {
		const table = document.querySelector('.files-list__table')
		if (!table) return

		const observer = new MutationObserver(() => {
			if (table.classList.contains('files-list__table--hidden')) {
				clearSelection()
			}
		})
		observer.observe(table, { attributes: true, attributeFilter: ['class'] })
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => setTimeout(watchTableState, 500))
	} else {
		setTimeout(watchTableState, 500)
	}
})()
