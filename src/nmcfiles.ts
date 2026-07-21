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

const renameAction = FileActions.find(action => action.id === 'rename')

if (renameAction?.enabled) {
	const originalEnabled = renameAction.enabled

	// Relies on `_action` being a plain field on FileAction (@nextcloud/files
	// internal detail) - re-verify on @nextcloud/files upgrades.
	;(renameAction as unknown as { _action: { enabled: typeof originalEnabled } })._action.enabled = (nodes: Node[], view: View) => {
		if (view.id !== 'favorites') {
			return originalEnabled(nodes, view)
		}

		return nodes.every(node =>
			Boolean(node.permissions & Permission.DELETE)
			&& Boolean(node.permissions & Permission.UPDATE),
		)
	}
}

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

const PENDING_SHARES_BODY_CLASS = 'nmc-pendingshares-view'

function isPendingSharesPage(): boolean {
	return window.location.pathname.includes('/pendingshares')
}

function syncPendingSharesBodyClass(): void {
	document.body.classList.toggle(PENDING_SHARES_BODY_CLASS, isPendingSharesPage())
}

/**
 * Prevent row clicks, sidebar opening, and right-click context menu on pending share rows.
 */
function blockPendingShareRowClick(row: HTMLElement): void {
	if (row.dataset.pendingShareBlocked === 'true') return

	row.addEventListener('click', (event: MouseEvent) => {
		const target = event.target as HTMLElement
		if (!target.closest('.files-list__row-checkbox') && !target.closest('.files-list__row-actions')) {
			event.preventDefault()
			event.stopPropagation()
		}
	}, true)

	row.addEventListener('contextmenu', (event: MouseEvent) => {
		event.preventDefault()
		event.stopPropagation()
	}, true)

	row.dataset.pendingShareBlocked = 'true'
}

/**
 * Filter menu to show only Accept/Reject share if it belongs to a pending share row.
 */
function filterPendingSharePopper(popper: Element): void {
	const menu = popper.querySelector<HTMLElement>('ul[role="menu"]')
	if (!menu || !menu.querySelector('.files-list__row-action-accept-share')) return

	menu.querySelectorAll<HTMLElement>('li.action, li.action-separator').forEach(item => {
		const keep = item.classList.contains('files-list__row-action-accept-share')
			|| item.classList.contains('files-list__row-action-reject-share')
		item.style.display = keep ? '' : 'none'
	})
}

/**
 * Patch history.pushState and history.replaceState to dispatch a custom
 * 'locationchange' event, enabling detection of SPA navigation.
 */
function patchHistoryForNavigation(): void {
	const dispatch = () => window.dispatchEvent(new Event('locationchange'))
	const originalPush = history.pushState.bind(history)
	const originalReplace = history.replaceState.bind(history)
	history.pushState = (...args) => { originalPush(...args); dispatch() }
	history.replaceState = (...args) => { originalReplace(...args); dispatch() }
}

/**
 * Set up all pending share behaviours: row click blocking and popper menu filtering.
 * A single MutationObserver handles both DOM concerns.
 */
function setupPendingShare(): void {
	// Sync body class on init and on SPA navigation (pushState + popstate)
	patchHistoryForNavigation()
	syncPendingSharesBodyClass()
	window.addEventListener('popstate', syncPendingSharesBodyClass)
	window.addEventListener('locationchange', syncPendingSharesBodyClass)

	// Handle rows already in the DOM on init
	if (isPendingSharesPage()) {
		document.querySelectorAll<HTMLElement>('tr[data-cy-files-list-row]').forEach(row => {
			blockPendingShareRowClick(row)
		})
	}

	// Single observer: watches for new rows (childList) and popper visibility changes (attributes)
	const observer = new MutationObserver((mutations: MutationRecord[]) => {
		for (const mutation of mutations) {
			if (mutation.type === 'childList') {
				if (!isPendingSharesPage()) continue
				for (const node of Array.from(mutation.addedNodes)) {
					if (!(node instanceof Element)) continue
					const rows = node.matches('tr[data-cy-files-list-row]')
						? [node as HTMLElement]
						: Array.from(node.querySelectorAll<HTMLElement>('tr[data-cy-files-list-row]'))
					for (const row of rows) {
						blockPendingShareRowClick(row)
					}
				}
			} else if (
				mutation.type === 'attributes'
				&& mutation.target instanceof Element
				&& mutation.target.classList.contains('v-popper__popper')
				&& mutation.target.classList.contains('v-popper__popper--shown')
			) {
				requestAnimationFrame(() => filterPendingSharePopper(mutation.target as Element))
			}
		}
	})
	observer.observe(document.body, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: ['class'],
	})

	// Click capture: retry across frames until the popper is open
	document.addEventListener('click', (event: MouseEvent) => {
		if (!(event.target as Element).closest('button.action-item__menutoggle')) return

		const tryFilter = (remaining: number): void => {
			const popper = document.querySelector('.v-popper__popper.v-popper__popper--shown')
			if (popper) { filterPendingSharePopper(popper); return }
			if (remaining > 0) requestAnimationFrame(() => tryFilter(remaining - 1))
		}
		requestAnimationFrame(() => tryFilter(10))
	}, true)
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupPendingShare)
} else {
	setupPendingShare()
}

/** Fixes empty file list when switching between grid and list view. */
function setupGridViewScrollFix(): void {
	let filesListEl: Element | null = null
	let classObserver: MutationObserver | null = null

	const domObserver = new MutationObserver(() => {
		const el = document.querySelector('.files-list')
		if (!el || el === filesListEl) return
		filesListEl = el

		let prevIsGrid = el.classList.contains('files-list--grid')
		classObserver?.disconnect()
		classObserver = new MutationObserver(() => {
			const isGrid = el.classList.contains('files-list--grid')
			if (isGrid === prevIsGrid) return
			prevIsGrid = isGrid
			setTimeout(() => el.dispatchEvent(new Event('scroll', { bubbles: true })), 0)
		})
		classObserver.observe(el, { attributes: true, attributeFilter: ['class'] })
	})

	domObserver.observe(document.body, { childList: true, subtree: true })
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupGridViewScrollFix)
} else {
	setupGridViewScrollFix()
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
