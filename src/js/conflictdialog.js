/* eslint-disable no-undef */
/* eslint-disable @nextcloud/no-deprecations */

/**
 * This script overrides nextcloud 'File already exists' dialog with the custom one
 */

const extendJquery = () => {
	// renders conflict dialog
	$.widget('oc.ocdialogconflictpredlg', {
		options: {
			width: 'auto',
			height: 'auto',
			closeButton: true,
			closeOnEscape: true,
			closeCallback: null,
			modal: false,
		},
		_create() {
			const self = this

			this.originalCss = {
				display: this.element[0].style.display,
				width: this.element[0].style.width,
				height: this.element[0].style.height,
			}

			this.originalTitle = this.element.attr('title')
			this.options.title = this.options.title || this.originalTitle

			this.$dialog = $('<div class="oc-dialog oc-conflict-pre-dlg" />')
				.attr({
					// Setting tabIndex makes the div focusable
					tabIndex: -1,
					role: 'dialog',
				})
				.insertBefore(this.element)
			this.$dialog.append(this.element.detach())
			this.element.removeAttr('title').addClass('oc-dialog-content').appendTo(this.$dialog)

			this.$dialog.css({
				display: 'inline-block',
				position: 'fixed',
			})

			this.enterCallback = null

			$(document).on('keydown keyup', function(event) {
				if (
					event.target !== self.$dialog.get(0)
					&& self.$dialog.find($(event.target)).length === 0
				) {
					return
				}
				// Escape
				if (
					event.keyCode === 27
					&& event.type === 'keydown'
					&& self.options.closeOnEscape
				) {
					event.stopImmediatePropagation()
					self.close()
					return false
				}
				// Enter
				if (event.keyCode === 13) {
					event.stopImmediatePropagation()
					if (self.enterCallback !== null) {
						self.enterCallback()
						event.preventDefault()
						return false
					}
					if (event.type === 'keyup') {
						event.preventDefault()
						return false
					}
					// If no button is selected we trigger the primary
					if (
						self.$buttonrow
						&& self.$buttonrow.find($(event.target)).length === 0
					) {
						const $button = self.$buttonrow.find('button.primary')
						if ($button && !$button.prop('disabled')) {
							$button.trigger('click')
						}
					} else if (self.$buttonrow) {
						$(event.target).trigger('click')
					}
					return false
				}
			})

			this._setOptions(this.options)
			this._createOverlay()
		},
		_init() {
			this.$dialog.focus()
			this._trigger('open')
		},
		_setOption(key, value) {
			const self = this
			switch (key) {
			case 'title':
				if (this.$title) {
					this.$title.text(value)
				} else {
					const $title = $('<div class="flex-container"><div class="oc-conflict-pre-dlg-title-div"><h2 class="oc-conflct-pre-dlg-title">'
							+ value
							+ '</h2></div><div class="oc-conflict-pre-dlg-close-div"><a class="close-conflict-pre-dlg">&nbsp;</a></div></div>')
					this.$title = $title.prependTo(this.$dialog)
				}
				this._setSizes()
				break
			case 'buttons':
				if (this.$buttonrow) {
					this.$buttonrow.empty()
				} else {
					const $buttonrow = $('<div class="" />')
					this.$buttonrow = $buttonrow.appendTo(this.$dialog)
				}
				if (value.length === 1) {
					this.$buttonrow.addClass('onebutton')
				} else if (value.length === 2) {
					this.$buttonrow.addClass('twobuttons')
				} else if (value.length === 3) {
					this.$buttonrow.addClass('threebuttons')
				} else if (value.length === 4) {
					this.$buttonrow.addClass('fourbuttons')
				}

				$.each(value, function(idx, val) {
					const $button = $('<button>').text(val.text)
					if (val.classes) {
						$button.addClass(val.classes)
					}

					if (val.defaultButton) {
						$button.addClass('primary')
						self.$defaultButton = $button
					}
					self.$buttonrow.append($button)
					$button.click(function() {
						val.click.apply(self.element[0], arguments)
					})
				})
				this.$buttonrow.find('button')
					.on('focus', function(event) {
						self.$buttonrow.find('button').removeClass('primary')
						$(this).addClass('primary')
					})
				this._setSizes()
				break
			case 'style':
				if (value.buttons !== undefined) {
					this.$buttonrow.addClass(value.buttons)
				}
				break
			case 'closeButton':
				if (value) {
					const $closeButton = $('<a class="oc-dialog-close"></a>')
					this.$dialog.prepend($closeButton)
					$closeButton.on('click', function() {
						self.options.closeCallback && self.options.closeCallback()
						self.close()
					})
				} else {
					this.$dialog.find('.oc-dialog-close').remove()
				}
				break
			case 'width':
				this.$dialog.css('width', value)
				break
			case 'height':
				this.$dialog.css('height', value)
				break
			case 'close':
				this.closeCB = value
				break
			}
			// this._super(key, value);
			$.Widget.prototype._setOption.apply(this, arguments)
		},
		_setOptions(options) {
			// this._super(options);
			$.Widget.prototype._setOptions.apply(this, arguments)
		},
		_setSizes() {
			let lessHeight = 0
			if (this.$title) {
				lessHeight += this.$title.outerHeight(true)
			}
			if (this.$buttonrow) {
				lessHeight += this.$buttonrow.outerHeight(true)
			}
			this.element.css({
				height: 'calc(100% - ' + lessHeight + 'px)',
			})
		},
		_createOverlay() {
			if (!this.options.modal) {
				return
			}

			const self = this
			let contentDiv = $('#content')
			if (contentDiv.length === 0) {
				// nextcloud-vue compatibility
				contentDiv = $('.content')
			}
			this.overlay = $('<div>')
				.addClass('oc-dialog-dim')
				.appendTo(contentDiv)
			this.overlay.on('click keydown keyup', function(event) {
				if (event.target !== self.$dialog.get(0) && self.$dialog.find($(event.target)).length === 0) {
					event.preventDefault()
					event.stopPropagation()

				}
			})
		},
		_destroyOverlay() {
			if (!this.options.modal) {
				return
			}

			if (this.overlay) {
				this.overlay.off('click keydown keyup')
				this.overlay.remove()
				this.overlay = null
			}
		},
		widget() {
			return this.$dialog
		},
		setEnterCallback(callback) {
			this.enterCallback = callback
		},
		unsetEnterCallback() {
			this.enterCallback = null
		},
		close() {
			this._destroyOverlay()
			const self = this
			// Ugly hack to catch remaining keyup events.
			setTimeout(function() {
				self._trigger('close', self)
			}, 200)

			self.$dialog.remove()
			this.destroy()
		},
		destroy() {
			if (this.$title) {
				this.$title.remove()
			}
			if (this.$buttonrow) {
				this.$buttonrow.remove()
			}

			if (this.originalTitle) {
				this.element.attr('title', this.originalTitle)
			}
			this.element.removeClass('oc-dialog-content')
				.css(this.originalCss).detach().insertBefore(this.$dialog)
			this.$dialog.remove()
		},
	})
}

const overrideOC = () => {
	OC.conflictsData = null

	// we override this function only because we need to set OC.conflictsData
	OC.Uploader.prototype.checkExistingFiles = function(selection, callbacks) {
		const fileList = this.fileList
		const conflicts = []
		// only keep non-conflicting uploads
		selection.uploads = _.filter(selection.uploads, function(upload) {
			const file = upload.getFile()
			if (file.relativePath) {
				// can't check in subfolder contents
				return true
			}
			if (!fileList) {
				// no list to check against
				return true
			}
			if (upload.getTargetFolder() !== fileList.getCurrentDirectory()) {
				// not uploading to the current folder
				return true
			}
			const fileInfo = fileList.findFile(file.name)
			if (fileInfo) {
				conflicts.push([
					// original
					_.extend(fileInfo, {
						directory: fileInfo.directory || fileInfo.path || fileList.getCurrentDirectory(),
					}),
					// replacement (File object)
					upload,
				])
				return false
			}
			return true
		})
		OC.conflictsData = conflicts
		if (conflicts.length) {
			// wait for template loading
			OC.dialogs.fileexists(null, null, null, this).done(function() {
				_.each(conflicts, function(conflictData) {
					OC.dialogs.fileexists(conflictData[1], conflictData[0], conflictData[1].getFile(), this)
				})
			})
		}

		// upload non-conflicting files
		// note: when reaching the server they might still meet conflicts
		// if the folder was concurrently modified, these will get added
		// to the already visible dialog, if applicable
		callbacks.onNoConflicts(selection)
	}

	/**
	 * Resolve conflicts by replacing or keeping files
	 * @param {boolean} keepOriginal
	 * @param {boolean} keepReplacement
	 */
	OC.Uploader.prototype.onContinueConflictPreDlg = function(keepOriginal, keepReplacement) {
		const self = this
		// iterate over all conflicts
		jQuery.each(OC.conflictsData, function(i, conflict) {
			const conflictData = conflict[1]
			if (keepOriginal && keepReplacement) {
				// when both selected -> autorename
				self.onAutorename(conflictData)
			} else if (keepReplacement) {
				// when only replacement selected -> overwrite
				self.onReplace(conflictData)
			} else {
				// when only original selected -> skip
				// when none selected -> skip
				self.onSkip(conflictData)
			}
		})
		OC.conflictsData = null // set to null once upload done
	}

	OC._fileexistsshownConflictPreDlg = false

	/**
	 * Displays file exists dialog
	 * @param {object} data upload object
	 * @param {object} original file with name, size and mtime
	 * @param {object} replacement file with name, size and mtime
	 * @param {object} controller with onContinueCustom, moreDetails, onSkip, onReplace and onRename methods
	 * @return {Promise} jquery promise that resolves after the dialog template was loaded
	 */
	OC.dialogs.fileexists = function(data, original, replacement, controller) {
		const dialogDeferred = new $.Deferred()
		const dialogName = 'oc-dialog-fileexists-content'
		const dialogId = '#' + dialogName
		const conflictCount = OC.conflictsData.length

		if (!OC._fileexistsshownConflictPreDlg) {
			const filename = OC.conflictsData[0][0].name
			// why, what based on size of data
			const why = t('core', "Do you want to replace it with files you're moving?")
			const what = conflictCount === 1 ? t('core', 'The file {filename} already exist in the location.', { filename }) : t('core', 'The files already exist in the location.')
			// create dialog
			OC._fileexistsshownConflictPreDlg = true
			const $tmpl = $('<div id="{dialog_name}" title="{title}" class="fileexists"><span class="why">{what}</span><br/><span class="what">{why}</span><br/></div>')
			const title = conflictCount === 1 ? t('core', 'File conflict') : t('core', '{conflictCount} File conflicts', { conflictCount })
			const $dlg = $tmpl.octemplate({
				dialog_name: dialogName,
				title,
				type: 'fileexists',

				allnewfiles: t('core', 'New Files'),
				allexistingfiles: t('core', 'Already existing files'),

				why,
				what,
			})

			$('body').append($dlg)

			const buttonlist = [{
				text: t('core', 'Cancel'),
				classes: 'cancel oc-conflict-pre-dlg-button',
				click() {
					if (typeof controller.onCancel !== 'undefined') {
						controller.onCancel(data)
					}
					$(dialogId).ocdialogconflictpredlg('close')
					OC.conflictsData = null
				},
			},
			{
				text: conflictCount === 1 ? t('core', 'Keep both files') : t('core', 'Keep both files for all'),
				classes: 'cancel oc-conflict-pre-dlg-button',
				click() {
					if (typeof controller.onContinueConflictPreDlg !== 'undefined') {
						controller.onContinueConflictPreDlg(true, true)
					}
					$(dialogId).ocdialogconflictpredlg('close')
				},
			},
			{
				text: conflictCount === 1 ? t('core', 'Replace file') : t('core', 'Replace all files'),
				classes: 'cancel oc-conflict-pre-dlg-button oc-conflict-pre-dlg-replace-button',
				click() {
					if (typeof controller.onContinue !== 'undefined') {
						controller.onContinueConflictPreDlg(false, true)
					}
					$(dialogId).ocdialogconflictpredlg('close')
				},
			}]

			$(dialogId).ocdialogconflictpredlg({
				width: 288,
				closeOnEscape: true,
				modal: true,
				buttons: buttonlist,
				closeButton: null,
				close() {
					OC._fileexistsshownConflictPreDlg = false
					try {
						$(this).ocdialogconflictpredlg('destroy').remove()
					} catch (e) {
						// ignore
					}
				},
			})

			$(dialogId).css('height', 'auto')

			// close ocdialogconflictpredlg

			$('.close-conflict-pre-dlg').on('click', function() {
				$(dialogId).ocdialogconflictpredlg('close')
				OC.conflictsData = null
			})

			dialogDeferred.resolve()
		}
		return dialogDeferred.promise()
	}
}

if (location.pathname.includes('/apps/files')) {
	extendJquery()
	overrideOC()
}
