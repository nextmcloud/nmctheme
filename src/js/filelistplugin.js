window.addEventListener('DOMContentLoaded', function() {

	const FileListPlugin = {

		attach(fileList) {
			const that = this

			window.addEventListener('resize', function() {
				that._resizeFileActionMenu(fileList)
			})

			const actionHandler = () => {
				this._onClickCancelSelected(fileList)
			}

			fileList.registerMultiSelectFileAction({
				name: 'cancel',
				displayName: t('files', 'Cancel'),
				iconClass: 'icon-close',
				order: 101,
				action: actionHandler,
			})

			const $thActions = $('<th>', { class: 'column-actions' }) // eslint-disable-line
			fileList.$el.find('.column-mtime').after($thActions)

			const $thMenu = $('<th>', { class: 'column-menu' }) // eslint-disable-line
			fileList.$el.find('.column-mtime').after($thMenu)

			fileList.fileMultipleSelectionMenu = new OCA.Files.FileMultiSelectMenu(fileList.multiSelectMenuItems)
			fileList.fileMultipleSelectionMenu.render()

			fileList.$el.find('.selectedActions').detach().appendTo($thActions)
			$thMenu.append(fileList.fileMultipleSelectionMenu.$el)

			fileList.updateEmptyContent = function() {
				const permissions = fileList.getDirectoryPermissions()
				const isCreatable = (permissions & OC.PERMISSION_CREATE) !== 0
				fileList.$el.find('.emptyfilelist.emptycontent').toggleClass('hidden', !fileList.isEmpty)
				fileList.$el.find('.emptyfilelist.emptycontent').toggleClass('hidden', !fileList.isEmpty)
				fileList.$el.find('.emptyfilelist.emptycontent .uploadmessage').toggleClass('hidden', !isCreatable || !fileList.isEmpty)
				fileList.$el.find('.files-filestable').toggleClass('hidden', fileList.isEmpty)
				fileList.$el.find('.files-filestable thead th').toggleClass('hidden', fileList.isEmpty)
				fileList.$el.find('.files-filestable thead th.column-menu').addClass('hidden')
				fileList.$el.find('.files-filestable thead th.column-actions').addClass('hidden')
				fileList.$el.find('.files-filestable thead th.column-expiration').addClass('hidden')
			}

			fileList.showDetailsView = function(fileName, tabId) {
				that._updateDetailsView(fileName, fileList)
				if (tabId) {
					OCA.Files.Sidebar.setActiveTab(tabId)
				}
			}

			fileList.updateSelectionSummary = function() {
				const self = this

				const summary = fileList._selectionSummary.summary
				const showHidden = !!fileList._filesConfig.show_hidden

				const fileTable = fileList.$table
				fileTable.find('.column-selection > label > span').text(t('files', 'All'))

				let selection

				if (summary.totalFiles === 0 && summary.totalDirs === 0) {
					fileTable.find('.column-size').removeClass('hidden')
					fileTable.find('.column-mtime').removeClass('hidden')
					fileTable.find('.column-menu').addClass('hidden')
					fileTable.find('.column-actions').addClass('hidden')
					fileTable.find('.column-name a.name').css('pointer-events', 'all')
					fileTable.find('.column-name a.name > span:first').text(t('files', 'Name'))
					fileTable.find('.column-name a.name > span.sort-indicator').css('display', '')
					fileTable.find('table').removeClass('multiselect')
				} else {
					fileTable.find('.column-size').addClass('hidden')
					fileTable.find('.column-mtime').addClass('hidden')
					fileTable.find('.column-menu').removeClass('hidden')
					fileTable.find('.column-actions').removeClass('hidden')

					fileList.fileMultiSelectMenu.show(self)
					fileList.fileMultipleSelectionMenu.show(self)
					that._resizeFileActionMenu(fileList)

					const directoryInfo = n('files', '%n folder', '%n folders', summary.totalDirs)
					const fileInfo = n('files', '%n file', '%n files', summary.totalFiles)

					if (summary.totalDirs > 0 && summary.totalFiles > 0) {
						const selectionVars = {
							dirs: directoryInfo,
							files: fileInfo,
						}
						selection = t('files', '{dirs} and {files}', selectionVars)
					} else if (summary.totalDirs > 0) {
						selection = directoryInfo
					} else {
						selection = fileInfo
					}

					if (!showHidden && summary.totalHidden > 0) {
						const hiddenInfo = n('files', 'including %n hidden', 'including %n hidden', summary.totalHidden)
						selection += ' (' + hiddenInfo + ')'
					}

					if (summary.totalSize > 0) {
						selection += ' (' + OC.Util.humanFileSize(summary.totalSize) + ')' // eslint-disable-line
					}

					fileTable.find('.column-name a.name').css('pointer-events', 'none')
					fileTable.find('.column-name a.name > span:first').text(selection)
					fileTable.find('.column-name a.name > span.sort-indicator').css('display', 'none')
					fileTable.find('table').addClass('multiselect')

					if (fileList.fileMultiSelectMenu) {
						fileList.fileMultiSelectMenu.toggleItemVisibility('download', fileList.isSelectedDownloadable())
						fileList.fileMultiSelectMenu.toggleItemVisibility('delete', fileList.isSelectedDeletable())
						fileList.fileMultiSelectMenu.toggleItemVisibility('copyMove', fileList.isSelectedCopiable())
						if (fileList.isSelectedCopiable()) {
							if (fileList.isSelectedMovable()) {
								fileList.fileMultiSelectMenu.updateItemText('copyMove', t('files', 'Move or copy'))
							} else {
								fileList.fileMultiSelectMenu.updateItemText('copyMove', t('files', 'Copy'))
							}
						} else {
							fileList.fileMultiSelectMenu.toggleItemVisibility('copyMove', false)
						}
					}

					if (fileList.fileMultipleSelectionMenu) {
						fileList.fileMultipleSelectionMenu.toggleItemVisibility('download', fileList.isSelectedDownloadable())
						fileList.fileMultipleSelectionMenu.toggleItemVisibility('delete', fileList.isSelectedDeletable())
						fileList.fileMultipleSelectionMenu.toggleItemVisibility('copyMove', fileList.isSelectedCopiable())
						if (fileList.isSelectedCopiable()) {
							if (fileList.isSelectedMovable()) {
								fileList.fileMultipleSelectionMenu.updateItemText('copyMove', t('files', 'Move or copy'))
							} else {
								fileList.fileMultipleSelectionMenu.updateItemText('copyMove', t('files', 'Copy'))
							}
						} else {
							fileList.fileMultipleSelectionMenu.toggleItemVisibility('copyMove', false)
						}
					}
				}
			}
		},

		_resizeFileActionMenu(fileList) {
			fileList.$el.find('.column-menu .filesSelectMenu ul li').removeClass('hidden')

			const appList = fileList.$el.find('.column-menu .filesSelectMenu ul li:visible')
			const appListLength = appList.length
			const colSelectionWidth = Math.ceil(fileList.$el.find('.column-selection').outerWidth())
			const colNameWidth = Math.ceil(fileList.$el.find('.column-name').outerWidth())
			const menuWidth = Math.ceil(fileList.$el.find('.column-menu .filesSelectMenu ul').outerWidth())
			const colActionsWidth = Math.ceil(fileList.$el.find('.column-actions').outerWidth())
			const headerWidth = Math.ceil(fileList.$el.find('.files-filestable thead').outerWidth())
			const listItemWidth = Math.round(menuWidth / appListLength)
			let availableWidth = headerWidth - (colSelectionWidth + colNameWidth)
			let appCount = Math.floor(availableWidth / listItemWidth)

			if (appCount < appListLength) {
				availableWidth = headerWidth - (colSelectionWidth + colNameWidth + colActionsWidth)
				appCount = Math.floor((availableWidth / listItemWidth))
			}

			if (appCount < appListLength) {
				fileList.$el.find('.selectedActions').css('display', 'block')
			} else if (appCount === appListLength) {
				fileList.$el.find('.selectedActions').css('display', 'none')
			} else if (!isFinite(appCount)) {
				fileList.$el.find('.selectedActions').css('display', 'block')
			} else if (appCount > appListLength) {
				fileList.$el.find('.selectedActions').css('display', 'none')
			}

			for (let k = 0; k < appListLength; k++) {
				if (k < appCount) {
					$(appList[k]).removeClass('hidden') // eslint-disable-line
				} else {
					$(appList[k]).addClass('hidden') // eslint-disable-line
				}
			}
		},

		_updateDetailsView(fileName, fileList, show) {
			FileListPlugin._resizeFileActionMenu(fileList)

			if (!(OCA.Files && OCA.Files.Sidebar)) {
				console.error('No sidebar available')
				return
			}

			if (!fileName && OCA.Files.Sidebar.close) {
				OCA.Files.Sidebar.close()
				return
			} else if (typeof fileName !== 'string') {
				fileName = ''
			}

			const tr = fileList.findFileEl(fileName)
			const model = fileList.getModelForFile(tr)
			const path = model.attributes.path + '/' + model.attributes.name

			if (fileList._currentFileModel) {
				fileList._currentFileModel.off()
			}
			fileList.$fileList.children().removeClass('highlighted')
			tr.addClass('highlighted')
			fileList._currentFileModel = model

			if (typeof show === 'undefined' || !!show || (OCA.Files.Sidebar.file !== '')) {
				OCA.Files.Sidebar.open(path.replace('//', '/'))
			}
		},

		_onClickCancelSelected(fileList) {
			fileList._selectedFiles = {}
			fileList._selectionSummary.clear()
			fileList.$table.find('input').prop('checked', false)
			fileList.$table.find('td.selection > .selectCheckBox:visible').closest('tr').toggleClass('selected', false)
			fileList.updateSelectionSummary()
		},
	}

	OC.Plugins.register('OCA.Files.FileList', FileListPlugin)
})
