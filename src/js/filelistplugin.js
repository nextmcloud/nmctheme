document.addEventListener('DOMContentLoaded', function() {
	setTimeout(() => {
		const filesTable = document.querySelector('.files-list');
	
		if (filesTable) {
			$(filesTable).find('.files-list__row').each(function() {
				// check if row contains .key-icon (E2EE)
				if ($(this).find('.key-icon').length > 0) {
					// if it dows hide .action-item of this row
					$(this).find('.action-item').css('display', 'none');
				}
			});
		}
	}, 500);
});