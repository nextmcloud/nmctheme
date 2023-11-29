import axios from '@nextcloud/axios'
import { showError } from '@nextcloud/dialogs' // eslint-disable-line
import { generateOcsUrl } from '@nextcloud/router'
import Types from '../utils/types.js'

window.addEventListener('DOMContentLoaded', function() {
	const NewFileMenuPlugin = {

		attach(menu) {
			const that = this
			const fileList = menu.fileList
			const isPublic = window.document.getElementById('isPublic')?.value === '1'

			menu.render = function() {

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
						const uniqueName = fileList.getUniqueName(name)

						if (fileType === 'folder') {
							Promise.all([fileList.createDirectory(uniqueName)]).then(() => {
								fileList.rename(uniqueName)
								that._hideAllMenus()
							})
						} else if (fileType === 'file') {
							if (isPublic) {
								Promise.all([fileList.createFile(uniqueName)]).then(() => {
									fileList.rename(uniqueName)
									that._hideAllMenus()
								})
							} else {
								that._createFile(uniqueName, fileList)
							}
						} else if (fileType.includes('x-office')) {
							if (OC.getCapabilities().richdocuments?.templates) {
								const docType = fileType.split(/[\s-]+/).pop()
								const mime = Types.getFileType(docType).mime
								that._createDocument(uniqueName, mime, fileList)
							}
						}
					})
				})
			}

			// remove 'Set up templates folder' option
			menu.removeMenuEntry('template-init')
		},

		async _createFile(name, fileList) {
			const currentDirInfo = OCA?.Files?.App?.currentFileList?.dirInfo || { path: '/', name: '' }
			const currentDirectory = `${currentDirInfo.path}/${currentDirInfo.name}`.replace(/\/\//gi, '/')

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

		_createDocument(name, mime, fileList) {
			OCA.Files.Files.isFileNameValid(name)
			name = fileList.getUniqueName(name)

			this._createEmptyFile(mime, name).then((response) => {
				if (response && response.status === 'success') {
					fileList.add(response.data, { animate: true, scrollTo: true })
					const fileModel = fileList.getModelForFile(name)
					const fileAction = OCA.Files.fileActions.getDefaultFileAction(fileModel.get('mimetype'), 'file', OC.PERMISSION_ALL)
					fileAction.action(name, {
						$file: null,
						dir: fileList.getCurrentDirectory(),
						fileList,
						fileActions: fileList.fileActions,
					})
				} else {
					OC.dialogs.alert(response.data.message, t('core', 'Could not create file'))
				}
			})
		},

		async _createEmptyFile(mimeType, fileName) {
			const shareToken = document.getElementById('sharingToken')?.value
			const directoryPath = document.getElementById('dir')?.value

			const response = await axios.post(generateOcsUrl('apps/richdocuments/api/v1', 2) + 'file', {
				mimeType,
				fileName,
				directoryPath,
				shareToken,
			})

			return response.data
		},

		_hideAllMenus() {
			OC.hideMenus()
			OCA.Files.Sidebar?.close()
		},
	}

	OC.Plugins.register('OCA.Files.NewFileMenu', NewFileMenuPlugin)
})
