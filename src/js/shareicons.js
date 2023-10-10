import { Type as ShareTypes } from '@nextcloud/sharing'
import { hideTooltip, showTooltip } from './tooltip.js'

// this function is used to distinguish between user and email share
const isEmail = (email) => {
	const re = /\S+@\S+\.\S+/
	return re.test(email)
}

// generate tooltip content for 'My shares' icons
const generateText = (recipients) => {
	if (recipients.length === 0) return ''
	let text = t('files_sharing', 'Shared with')
	const displayNames = recipients.map((rec) => rec.shareWithDisplayName)
	switch (displayNames.length) {
	case 1:
		text += ` ${displayNames[0]}`
		break
	case 2:
		text += ` ${displayNames[0]}, ${displayNames[1]}`
		break
	case 3:
		text += ` ${displayNames[0]}, ${displayNames[1]} ${t('nmctheme', 'and')} ${displayNames[2]}`
		break
	case 4:
	default:
		text += ` ${displayNames[0]}, ${displayNames[1]}, ${displayNames[2]}...`
		break
	}
	return text
}

/**
 * Generate icons for 'My shares' page
 * @param {object} action icons container
 * @param {object} recipients list of the recipients information: id and displayName
 * @param {string} shareTypes string containing existing share types
 */
const generateShareList = (action, recipients, shareTypes) => {
	action.empty()
	shareTypes.split(',').forEach(shareType => {
		const iconEl = document.createElement('span')
		iconEl.className = 'icon'
		if (parseInt(shareType) === ShareTypes.SHARE_TYPE_LINK) {
			iconEl.classList.add('icon-link')
		} else if (parseInt(shareType) === ShareTypes.SHARE_TYPE_EMAIL) {
			iconEl.classList.add('icon-user')
			iconEl.tooltipContent = generateText(Object.values(recipients).filter(rec => isEmail(rec.shareWith)))
		} else if (parseInt(shareType) === ShareTypes.SHARE_TYPE_USER) {
			iconEl.classList.add('icon-upload-to-cloud')
			iconEl.tooltipContent = generateText(Object.values(recipients).filter(rec => !isEmail(rec.shareWith)))
		}
		iconEl.addEventListener('mouseenter', showTooltip)
		iconEl.addEventListener('mouseleave', hideTooltip)
		action.append(iconEl)
	})
}

const generateSharedWithMe = (action, owner) => {
	const iconEl = document.createElement('span')
	iconEl.classList.add('icon', 'icon-upload-to-cloud')
	iconEl.tooltipContent = `${t('files_sharing', 'Shared by')} ${owner}`
	iconEl.addEventListener('mouseenter', showTooltip)
	iconEl.addEventListener('mouseleave', hideTooltip)
	action.html(iconEl)
}

window.addEventListener('DOMContentLoaded', function() {
	if (!OCA.Sharing) return
	OCA.Sharing.Util._markFileAsShared = function($tr, hasShares, hasLink) {
		const action = $tr.find('.fileactions .action[data-action="Share"]')
		const type = $tr.data('type')
		const icon = action.find('.icon')
		let recipients, avatars, shareTypes
		const ownerId = $tr.attr('data-share-owner-id')
		const owner = $tr.attr('data-share-owner')
		const mountType = $tr.attr('data-mounttype')
		let shareFolderIcon
		let iconClass = 'icon-shared'
		action.removeClass('shared-style')
		// update folder icon
		if (type === 'dir' && (hasShares || hasLink || ownerId)) {
			if (typeof mountType !== 'undefined' && mountType !== 'shared-root' && mountType !== 'shared') {
				shareFolderIcon = OC.MimeType.getIconUrl('dir-' + mountType)
			} else if (hasLink) {
				shareFolderIcon = OC.MimeType.getIconUrl('dir-public')
			} else {
				shareFolderIcon = OC.MimeType.getIconUrl('dir-shared')
			}
			$tr.find('.filename .thumbnail').css('background-image', 'url(' + shareFolderIcon + ')')
			$tr.attr('data-icon', shareFolderIcon)
		} else if (type === 'dir') {
			const isEncrypted = $tr.attr('data-e2eencrypted')
			// FIXME: duplicate of FileList._createRow logic for external folder,
			// need to refactor the icon logic into a single code path eventually
			if (isEncrypted === 'true') {
				shareFolderIcon = OC.MimeType.getIconUrl('dir-encrypted')
				$tr.attr('data-icon', shareFolderIcon)
			} else if (mountType && mountType.indexOf('external') === 0) {
				shareFolderIcon = OC.MimeType.getIconUrl('dir-external')
				$tr.attr('data-icon', shareFolderIcon)
			} else {
				shareFolderIcon = OC.MimeType.getIconUrl('dir')
				// back to default
				$tr.removeAttr('data-icon')
			}
			$tr.find('.filename .thumbnail').css('background-image', 'url(' + shareFolderIcon + ')')
		}
		// update share action text / icon
		if (hasShares || ownerId) {
			recipients = $tr.data('share-recipient-data')
			shareTypes = $tr.data('share-types')
			action.addClass('shared-style')

			avatars = '<span>' + t('files_sharing', 'Shared') + '</span>'
			action.html(avatars).prepend(icon)
			// even if reshared, only show "Shared by"
			if (ownerId) {
				generateSharedWithMe(action, owner)
			} else if (recipients) {
				generateShareList(action, recipients, shareTypes)
			// in case file has only link shares and the user is on 'My shares' page
			} else if (window.location.href.includes('view=sharingout')) {
				const iconEl = document.createElement('span')
				iconEl.classList.add('icon', 'icon-link')
				action.html(iconEl)
			}
		} else {
			action.html('<span class="hidden-visually">' + t('files_sharing', 'Shared') + '</span>').prepend(icon)
		}
		if (hasLink) {
			iconClass = 'icon-public'
		}
		icon.removeClass('icon-shared icon-public').addClass(iconClass)
	}
})
