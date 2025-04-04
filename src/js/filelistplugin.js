document.addEventListener('DOMContentLoaded', function() {
	setTimeout(() => {
		const filesTable = document.querySelector('.files-list')

		if (filesTable) {
			filesTable.querySelectorAll('.files-list__row').forEach(row => {
				// check if row contains .key-icon (E2EE)
				if (row.querySelector('.key-icon')) {
					// if it does, hide .action-item of this row
					const actionItem = row.querySelector('.action-item')
					if (actionItem) {
						actionItem.style.display = 'none'
					}
				}
			})
		}
	}, 500)
})
