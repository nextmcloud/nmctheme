import { computePosition, offset, shift, arrow } from '@floating-ui/dom'

let tooltip = document.getElementById('nmctooltip')
let tooltipArrow = document.getElementById('nmctooltip_arrow')

if (!tooltip) {
	// create a tooltip container
	tooltip = document.createElement('div')
	tooltip.id = 'nmctooltip'
	// create a tooltip text container
	const textContent = document.createElement('div')
	textContent.id = 'nmctooltip_text'
	tooltip.appendChild(textContent)
	// create a tooltip arrow
	tooltipArrow = document.createElement('div')
	tooltipArrow.id = 'nmctooltip_arrow'
	tooltip.appendChild(tooltipArrow)
	document.querySelector('body').appendChild(tooltip)
}

export const updateTooltip = (targetEl) => {
	computePosition(targetEl, tooltip, {
		placement: 'top',
		middleware: [offset(6), shift({ padding: 6 }), arrow({ element: tooltipArrow })],
	  }).then(({ x, y, placement, middlewareData }) => {
		Object.assign(tooltip.style, {
		  left: `${x}px`,
		  top: `${y}px`,
		})

		const { x: arrowX, y: arrowY } = middlewareData.arrow

		const staticSide = {
			top: 'bottom',
			right: 'left',
			bottom: 'top',
			left: 'right',
		}[placement.split('-')[0]]

		Object.assign(tooltipArrow.style, {
			left: arrowX != null ? `${arrowX}px` : '',
			top: arrowY != null ? `${arrowY}px` : '',
			right: '',
			bottom: '',
			[staticSide]: '-4px',
		})
	  })
}

export const showTooltip = (event) => {
	const tooltipContent = event.currentTarget.tooltipContent
	if (!tooltipContent || !tooltip) return
	tooltip.style.display = 'block'
	document.getElementById('nmctooltip_text').textContent = tooltipContent
	updateTooltip(event.currentTarget)
}

export const hideTooltip = () => {
	tooltip.style.display = 'none'
}
