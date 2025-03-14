document.addEventListener('DOMContentLoaded', function() {
	setTimeout(() => {
		const filesTable = document.querySelector('.files-list');
	
		if (filesTable) {
			// Gehe durch alle Zeilen der Tabelle
			$(filesTable).find('.files-list__row').each(function() {
				// Überprüfe, ob in dieser Zeile ein .key-icon (E2EE) vorhanden ist
				if ($(this).find('.key-icon').length > 0) {
					// Wenn ja, verstecke das .action-item in dieser Zeile
					$(this).find('.action-item').css('display', 'none');
				}
			});
		} else {
			console.log('Tabelle mit der Klasse "files-list" nicht gefunden');
		}
	}, 500);
});