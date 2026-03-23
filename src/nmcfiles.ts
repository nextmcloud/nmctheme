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

/**
 * Move the text editor link bubble tooltip to document.body to escape
 * the CSS stacking context and appear above the app navigation.
 */
function setupLinkBubbleFix(): void {
	const observer = new MutationObserver((mutations: MutationRecord[]) => {
		for (const mutation of mutations) {
			for (const node of Array.from(mutation.addedNodes)) {
				if (!(node instanceof Element)) continue
				const candidates = node.matches('[data-tippy-root]')
					? [node]
					: Array.from(node.querySelectorAll('[data-tippy-root]'))
				for (const root of candidates) {
					if (root.parentNode !== document.body && root.querySelector('.link-view-bubble')) {
						document.body.appendChild(root)
					}
				}
			}
		}
	})
	observer.observe(document.body, { childList: true, subtree: true })
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupLinkBubbleFix)
} else {
	setupLinkBubbleFix()
}

/**
 * Prevent row clicks and sidebar opening on pending share rows.
 * Pending share rows are identified by the presence of the accept-share action button.
 */
function blockPendingShareRowClick(row: HTMLElement): void {
	if (row.dataset.pendingShareBlocked === 'true') return

	const nameLink = row.querySelector<HTMLElement>('.files-list__row-name-link')
	if (nameLink) {
		nameLink.addEventListener('click', (event: MouseEvent) => {
			event.preventDefault()
			event.stopPropagation()
		}, true)
	}

	// Also block row-level clicks (opens sidebar) on non-interactive cells
	row.addEventListener('click', (event: MouseEvent) => {
		const target = event.target as HTMLElement
		const isCheckbox = target.closest('.files-list__row-checkbox')
		const isActionButton = target.closest('.files-list__row-actions')
		if (!isCheckbox && !isActionButton) {
			event.preventDefault()
			event.stopPropagation()
		}
	}, true)

	row.dataset.pendingShareBlocked = 'true'
}

function setupPendingShareRowClickBlock(): void {
	const observer = new MutationObserver((mutations: MutationRecord[]) => {
		for (const mutation of mutations) {
			for (const node of Array.from(mutation.addedNodes)) {
				if (!(node instanceof Element)) continue
				const rows: HTMLElement[] = node.matches('tr[data-cy-files-list-row]')
					? [node as HTMLElement]
					: Array.from(node.querySelectorAll<HTMLElement>('tr[data-cy-files-list-row]'))
				for (const row of rows) {
					if (row.querySelector('.files-list__row-action-accept-share')) {
						blockPendingShareRowClick(row)
					}
				}
			}
		}
	})
	observer.observe(document.body, { childList: true, subtree: true })

	// Handle rows already present in the DOM
	document.querySelectorAll<HTMLElement>('tr[data-cy-files-list-row]').forEach(row => {
		if (row.querySelector('.files-list__row-action-accept-share')) {
			blockPendingShareRowClick(row)
		}
	})
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupPendingShareRowClickBlock)
} else {
	setupPendingShareRowClickBlock()
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
