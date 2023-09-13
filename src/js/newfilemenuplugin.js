(function() {

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
						$('#file_upload_start').trigger('click')
					}
				})

				const folderEntry = this.$el.find('[data-action="folder"]')
				folderEntry.removeClass('menuitem').addClass('customitem')

				folderEntry.on('click', function(event) {
					let $target = $(event.target)

					if (!$target.hasClass('menuitem')) {
						$target = $target.closest('.customitem')
					}

					const name = $target.attr('data-templatename')
					const uniqueName = self.fileList.getUniqueName(name)

					const tempPromise = self.fileList.createDirectory(uniqueName)
					Promise.all([tempPromise]).then(() => {
						self.fileList.rename(uniqueName)
					})
				})
			}
		},
	}

	OC.Plugins.register('OCA.Files.NewFileMenu', NewFileMenuPlugin)
})()
