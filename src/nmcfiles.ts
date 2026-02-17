import { getFileActions, registerFileAction, FileAction, Node, Permission, View, getNavigation } from '@nextcloud/files'
import { translate as t } from '@nextcloud/l10n'
import { sidebarAction } from './utils/sidebar.js'

const Navigation = getNavigation()

const sharinginView = Navigation.views.find(view => view.id === 'sharingin')

if (sharinginView) {
	sharinginView.order = 3
	Navigation.remove('sharingin')
	Navigation.register(sharinginView)
}

const filesView = Navigation.views.find(view => view.id === 'files')

if (filesView) {
	filesView.order = 10
	Navigation.remove('files')
	Navigation.register(filesView)
}

const handleCancel = async () => {
	return true
}

const fileAction = new FileAction({
	id: 'cancel_select',
	order: 10000000,
	iconSvgInline() {
		return ''
	},
	displayName() {
		return t('files', 'Cancel')
	},
	enabled() {
		return true
	},
	async execBatch(nodes: Node[]) {
		const result = handleCancel()
		return Promise.all(nodes.map(() => result))
	},
	async exec(): Promise<boolean|null> {
		const result = handleCancel()
		return result
	},
})

registerFileAction(fileAction)

const FileActions = getFileActions()

const sharingStatusAction = FileActions.find(action => action.id === 'sharing-status')

if (sharingStatusAction) {

	const sharingStatusMenuAction = new FileAction({
		id: 'sharing-status-menu',
		order: -100,
		iconSvgInline() {
			return ''
		},
		displayName() {
			return t('files_sharing', 'Sharing')
		},
		enabled() {
			return true
		},
		async exec(node: Node, view: View, dir: string) {
			if ((node.permissions & Permission.READ) !== 0) {
				window.OCA?.Files?.Sidebar?.setActiveTab?.('sharing')
				return sidebarAction(node, view, dir)
			}
			return null
		},
	})

	registerFileAction(sharingStatusMenuAction)

}

function updateLabel(selector) {
	const buttonText = document.querySelector(selector)?.querySelector('.upload-picker')?.querySelector('.button-vue__text')

	if (buttonText) {
		buttonText.textContent = t('nmctheme', 'Add')
		return true
	}

	return false
}

window.addEventListener('DOMContentLoaded', function() {
	const breadcrumb = document.querySelector('.breadcrumb')
	const empty = document.querySelector('.files-list__empty')

	if (breadcrumb) {
		const observer = new MutationObserver(() => {
			if (updateLabel('.breadcrumb')) {
				observer.disconnect()
			}
		})

		observer.observe(breadcrumb, {
			childList: true,
			subtree: true,
		})
	}

	if (empty) {
		const observerB = new MutationObserver(() => {
			if (updateLabel('.files-list__empty')) {
				observerB.disconnect()
			}
		})

		observerB.observe(empty, {
			childList: true,
			subtree: true,
		})
	}
})
