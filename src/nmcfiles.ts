import { getFileActions, registerFileAction, FileAction, Node, Permission, View, getNavigation } from '@nextcloud/files'
import { subscribe } from '@nextcloud/event-bus'
import { loadState } from '@nextcloud/initial-state'
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

/**
 * NC33 labels the upload button "New"; production calls it "Add".
 */
function fixUploadButton(container: Element): void {
	const buttonText = container.querySelector('.button-vue__text')

	if (buttonText && buttonText.textContent !== t('nmctheme', 'Add')) {
		buttonText.textContent = t('nmctheme', 'Add')
	}
}

/**
 * Watches the document because Vue re-renders the button on navigation.
 */
function setupUploadButtonFix(): void {
	const apply = (): void => {
		document.querySelectorAll('.files-list__header-upload-button').forEach(fixUploadButton)
	}

	apply()

	const observer = new MutationObserver(() => apply())
	observer.observe(document.body, { childList: true, subtree: true })
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupUploadButtonFix)
} else {
	setupUploadButtonFix()
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

/**
 * NC33 renders the Type/Modified filters inline in the header; move them into the
 * .files-list__filters row below. Re-runs on Vue re-renders that re-insert them.
 */
function setupFilterRelocation(): void {
	const relocate = (): void => {
		const target = document.querySelector<HTMLElement>('.files-list__filters')
		if (!target) return

		const headerFilters = document.querySelector<HTMLElement>(
			'.files-list__header [data-test-id="files-list-filters"]',
		)
		if (!headerFilters) return

		// Drop any stale copy before moving, in case Vue recreated the element.
		target.querySelectorAll('[data-test-id="files-list-filters"]').forEach(el => el.remove())
		target.appendChild(headerFilters)
	}

	relocate()

	const observer = new MutationObserver(() => relocate())
	observer.observe(document.body, { childList: true, subtree: true })
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupFilterRelocation)
} else {
	setupFilterRelocation()
}

/**
 * NC33's "Custom range" calendar is appended to <body>, where it paints below the filter
 * popover and every click in it reads as "outside", closing the menu. Adopt it instead.
 */
function setupModifiedFilterCalendar(): void {
	const MIN_CALENDAR_POPOVER_HEIGHT = 200
	const ISO_DATE = /\d{4}-\d{2}-\d{2}/g

	const startOfToday = (): number => new Date().setHours(0, 0, 0, 0)

	// Local midnight; `new Date(iso)` would parse "YYYY-MM-DD" as UTC.
	const parseIsoDate = (iso: string): number => {
		const [year, month, day] = iso.split('-').map(Number)
		return new Date(year, month - 1, day).getTime()
	}

	// A data attribute, not a class: Vue overwrites the cells' class on re-render.
	const markFutureCells = (panel: HTMLElement): void => {
		const today = startOfToday()
		panel.querySelectorAll<HTMLElement>('td.cell[title]').forEach(cell => {
			const future = parseIsoDate(cell.title) > today
			cell.toggleAttribute('data-nmc-future', future)
			if (future) {
				cell.setAttribute('aria-disabled', 'true')
			} else {
				cell.removeAttribute('aria-disabled')
			}
		})
	}

	let cellObserver: MutationObserver | null = null

	const watchCells = (panel: HTMLElement): void => {
		cellObserver?.disconnect()
		markFutureCells(panel)

		// Month navigation patches cells in place, so childList alone would miss it;
		// filtering to class also stops our own writes re-triggering this.
		cellObserver = new MutationObserver(() => markFutureCells(panel))
		cellObserver.observe(panel, {
			childList: true,
			subtree: true,
			characterData: true,
			attributes: true,
			attributeFilter: ['class'],
		})
	}

	const adopt = (panel: HTMLElement): void => {
		const target = document.querySelector<HTMLElement>(
			'.v-popper__popper--shown:has(files-file-list-filter-modified) .v-popper__inner',
		)
		if (!target || panel.parentElement === target) return

		target.appendChild(panel)
		watchCells(panel)

		// Shrink before measuring: an oversized popper is already shifted up, so its
		// position would over-report the room left below the trigger.
		target.style.maxHeight = `${MIN_CALENDAR_POPOVER_HEIGHT}px`

		requestAnimationFrame(() => {
			const popper = target.closest<HTMLElement>('.v-popper__popper')
			if (popper) {
				const bounds = document.getElementById('app-content-vue')?.getBoundingClientRect()
				const bottom = Math.min(bounds?.bottom ?? window.innerHeight, window.innerHeight)
				const room = bottom - popper.getBoundingClientRect().top - 16
				target.style.maxHeight = `${Math.max(MIN_CALENDAR_POPOVER_HEIGHT, room)}px`
			}
			requestAnimationFrame(() => { target.scrollTop = target.scrollHeight })
		})
	}

	const observer = new MutationObserver((mutations: MutationRecord[]) => {
		for (const mutation of mutations) {
			for (const node of Array.from(mutation.addedNodes)) {
				if (node instanceof HTMLElement && node.classList.contains('mx-datepicker-main')) {
					adopt(node)
				}
			}
		}
	})
	observer.observe(document.body, { childList: true })

	// The field is typable, so it needs its own guard. Capture on document runs
	// before vue2-datepicker's own listeners on the input.
	let lastCommitted = ''

	const rangeInput = (event: Event): HTMLInputElement | null => {
		const target = event.target
		if (!(target instanceof HTMLInputElement) || !target.matches('.mx-input')) return null
		return target.closest('.v-popper__popper:has(files-file-list-filter-modified)')
			? target
			: null
	}

	document.addEventListener('focus', (event: Event) => {
		const input = rangeInput(event)
		if (input) lastCommitted = input.value
	}, true)

	const rejectFutureInput = (event: Event): void => {
		const input = rangeInput(event)
		if (!input) return

		const today = startOfToday()
		const dates = input.value.match(ISO_DATE) ?? []
		if (!dates.some(iso => parseIsoDate(iso) > today)) return

		event.stopPropagation()
		event.preventDefault()
		input.value = lastCommitted
		// Resync vue2-datepicker's own draft, or its next render restores the rejection.
		input.dispatchEvent(new Event('input', { bubbles: true }))
	}

	document.addEventListener('change', rejectFutureInput, true)
	document.addEventListener('keydown', (event: KeyboardEvent) => {
		if (event.key === 'Enter') rejectFutureInput(event)
	}, true)
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupModifiedFilterCalendar)
} else {
	setupModifiedFilterCalendar()
}

const PUBLIC_NO_DOWNLOAD_BODY_CLASS = 'nmc-public-nodownload'
const WRITE_PERMISSIONS = Permission.CREATE | Permission.UPDATE | Permission.DELETE

interface ShareAttribute {
	scope: string
	key: string
	value: unknown
}

/** Mirrors the download checks of the server's `isDownloadable()`. */
function isDownloadHidden(node: Node): boolean {
	const hideDownload = node.attributes['hide-download']
	if (hideDownload === true || hideDownload === 'true') {
		return true
	}

	const shareAttributes = node.attributes['share-attributes']
	if (!shareAttributes) {
		return false
	}

	try {
		return (JSON.parse(shareAttributes) as ShareAttribute[])
			.some(({ scope, key, value }) => scope === 'permissions' && key === 'download' && value === false)
	} catch {
		return false
	}
}

/**
 * A public share that hides downloads can be left with every row action filtered out, so the
 * actions menu opens empty. Flag the page so the toggles can be hidden.
 */
function setupPublicHideDownload(): void {
	if (!loadState('files_sharing', 'isPublic', false)) {
		return
	}

	// The single-file share view registers no write actions, so only the folder view keeps a
	// usable menu when the share is writable: there Rename, Move and Delete remain.
	const isSingleFileShare = loadState<string>('files_sharing', 'view', '') === 'public-file-share'

	// Judged per row: the single-file share view emits a synthetic root folder that carries
	// neither the share attributes nor the share permissions.
	subscribe('files:list:updated', (event) => {
		const { contents } = event as { contents: Node[] }
		const menuIsEmpty = contents.length > 0 && contents.every(node =>
			isDownloadHidden(node)
			&& (isSingleFileShare || (node.permissions & WRITE_PERMISSIONS) === 0),
		)
		document.body.classList.toggle(PUBLIC_NO_DOWNLOAD_BODY_CLASS, menuIsEmpty)
	})
}

setupPublicHideDownload()

/**
 * Keep the narrow filter area looking like the wide layout while reusing the
 * mobile filter menu for the actual filter controls.
 */
function setupNarrowFilterChips(): void {
	const suppressLegacyFilterPopover = (): void => {
		document.querySelectorAll<HTMLElement>('.v-popper__popper').forEach(popover => {
			const legacyMenu = popover.querySelector('[role="menu"][aria-labelledby="file-list-filters-menu-trigger"]')
			if (legacyMenu) {
				popover.style.visibility = 'hidden'
			} else if (popover.querySelector('[class*="popoverFilterView"]')) {
				popover.style.visibility = 'visible'
			}
		})
	}

	const openFilter = (label: string): void => {
		const trigger = document.querySelector<HTMLElement>('#file-list-filters-menu-trigger')
		if (!trigger) return

		trigger.click()
		const filterPopover = document.querySelector<HTMLElement>('.v-popper__popper--shown')
		if (filterPopover) {
			filterPopover.style.visibility = 'hidden'
		}
		let attempts = 0
		const selectFilter = (): void => {
			const menu = document.querySelector<HTMLElement>('#file-list-filters-menu-trigger ~ [role="menu"], [role="menu"][aria-labelledby="file-list-filters-menu-trigger"]')
			const option = Array.from(menu?.querySelectorAll<HTMLElement>('button') ?? [])
				.find(button => button.textContent?.trim() === label)

			if (option) {
				option.click()
				requestAnimationFrame(() => {
					const selectedFilterPopover = document.querySelector<HTMLElement>('.v-popper__popper--shown')
					if (selectedFilterPopover) {
						selectedFilterPopover.style.visibility = 'visible'
					}
				})
			} else if (attempts++ < 10) {
				requestAnimationFrame(selectFilter)
			}
		}
		requestAnimationFrame(selectFilter)
	}

	const createChip = (label: string): HTMLButtonElement => {
		const chip = document.createElement('button')
		chip.type = 'button'
		chip.className = 'button-vue'
		chip.setAttribute('aria-label', label)
		chip.dataset.nmcFilterChip = label
		chip.innerHTML = `<span class="button-vue__wrapper"><span class="button-vue__text">${label}</span></span>`
		chip.querySelector<HTMLElement>('.button-vue__text')!.style.fontWeight = 'bold'
		chip.addEventListener('click', () => openFilter(label))
		return chip
	}

	const update = (): void => {
		const trigger = document.querySelector<HTMLElement>('#file-list-filters-menu-trigger')
		const container = trigger?.closest<HTMLElement>('[data-test-id="files-list-filters"]')
		if (!trigger || !container) {
			document.querySelectorAll('[data-nmc-filter-chip]').forEach(chip => chip.remove())
			return
		}

		trigger.style.visibility = 'hidden'
		trigger.style.position = 'absolute'
		trigger.style.pointerEvents = 'none'
		const backLabel = t('files', 'Back to filters')
		document.querySelectorAll<HTMLElement>('[role="dialog"] button').forEach(button => {
			if (button.textContent?.trim() === backLabel) {
				button.remove()
			}
		})
		for (const label of [t('files', 'Type'), t('files', 'Modified')]) {
			if (!container.querySelector(`[data-nmc-filter-chip="${label}"]`)) {
				container.appendChild(createChip(label))
			}
		}
		const backButton = document.querySelector<HTMLElement>('[class*="popoverFilterView"] > button')
		if (backButton) {
			backButton.style.display = 'none'
		}
	}

	update()
	suppressLegacyFilterPopover()

	const observer = new MutationObserver(() => {
		suppressLegacyFilterPopover()
		update()
	})
	observer.observe(document.body, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: ['class'],
	})
}

if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', setupNarrowFilterChips)
} else {
	setupNarrowFilterChips()
}