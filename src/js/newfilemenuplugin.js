window.addEventListener('DOMContentLoaded', function() {
	const NewFileMenuPlugin = {

		attach(menu) {

			menu.render = function() {
				const self = this

				this.$el.html(this.template({
					uploadMaxHumanFileSize: 'TODO',
					uploadLabel: t('files', 'Upload file'),
					items: this._menuItems,
				}))

				// Trigger upload action also with keyboard navigation on enter
				this.$el.find('[for="file_upload_start"]').on('keyup', function(event) {
					if (event.key === ' ' || event.key === 'Enter') {
						$('#file_upload_start').trigger('click') // eslint-disable-line
					}
				})
				
				const menuitems = this.$el.find('a.menuitem')

				menuitems.each(function( index, element ) {
					$(element).removeClass('menuitem').addClass('customitem')

					$(element).on('click', function(event) {
						let $target = $(event.target) // eslint-disable-line
	
						if (!$target.hasClass('menuitem')) {
							$target = $target.closest('.customitem')
						}
	
						const filetype = $target.data('filetype')
						const name = $target.data('templatename')
						const uniqueName = self.fileList.getUniqueName(name)

						if(filetype === 'file') {
							Promise.all([self.fileList.createFile(uniqueName)]).then(() => {
								self.fileList.rename(uniqueName)
							})
						} else if(filetype === 'folder') {
							Promise.all([self.fileList.createDirectory(uniqueName)]).then(() => {
								self.fileList.rename(uniqueName)
							})
						}
					})
				})
			}

			// remove 'Set up templates folder' option
			menu.removeMenuEntry('template-init')
		},
	}

	OC.Plugins.register('OCA.Files.NewFileMenu', NewFileMenuPlugin)
})
