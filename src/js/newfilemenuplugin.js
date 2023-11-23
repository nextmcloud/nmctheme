import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs'
import { generateOcsUrl } from '@nextcloud/router'

window.addEventListener('DOMContentLoaded', function() {
	const NewFileMenuPlugin = {

		attach(menu) {
			const that = this

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

				menuitems.each(function(index, element) {
					$(element).removeClass('menuitem').addClass('customitem') // eslint-disable-line

					$(element).on('click', function(event) { // eslint-disable-line
						event.preventDefault()

						let $target = $(event.target) // eslint-disable-line

						if (!$target.hasClass('customitem')) {
							$target = $target.closest('.customitem')
						}

						const fileType = $target.data('filetype')
						const name = $target.data('templatename')
						const uniqueName = self.fileList.getUniqueName(name)

						if (fileType === 'file') {
							that._createFile(uniqueName)
						} else if (fileType === 'folder') {
							Promise.all([self.fileList.createDirectory(uniqueName)]).then(() => {
								self.fileList.rename(uniqueName)
								that._hideAllMenus()
							})
						}
					})
				})
			}

			// remove 'Set up templates folder' option
			menu.removeMenuEntry('template-init')
		},

		async _createFile(name) {

			const currentDirInfo = OCA?.Files?.App?.currentFileList?.dirInfo || { path: '/', name: '' }
			const currentDirectory = `${currentDirInfo.path}/${currentDirInfo.name}`.replace(/\/\//gi, '/')

			const fileList = OCA?.Files?.App?.currentFileList

			try {
				await axios.post(generateOcsUrl('apps/files/api/v1/templates/create'), {
					filePath: `${currentDirectory}/${name}`,
				})

				const options = _.extend({ scrollTo: false } || {}) // eslint-disable-line

				await fileList?.addAndFetchFileInfo(name, undefined, options)
				fileList.rename(name)

				this._hideAllMenus()
			} catch (error) {
				console.error(error)
				showError('Unable to create new file')
			}
		},

		_hideAllMenus() {
			OC.hideMenus()
			OCA.Files.Sidebar.close()
		},
	}

	OC.Plugins.register('OCA.Files.NewFileMenu', NewFileMenuPlugin)
})
