import { generateUrl } from '@nextcloud/router'

const ncLogoElement = document.getElementById('nextcloud')
if (ncLogoElement !== null) {
	ncLogoElement.remove()
}

const headerDivElement = document.querySelector('body header #header')
if (headerDivElement !== null) {
	headerDivElement.remove()
}

const logoClassElement = document.querySelector('body header .logo')
if (logoClassElement !== null) {
	logoClassElement.remove()
}

const headerElement = document.querySelector('body header')
if (headerElement !== null) {
	const brandElement = document.createElement('div')
	brandElement.setAttribute('class', 'brand')
	headerElement.prepend(brandElement)
	brandElement.innerHTML = `
    <a href="${generateUrl('/')}" aria-label="MagentaCLOUD – Go to home" class="brand-link">
      <div class="logo logo-icon"></div>
      <div class="title">MagentaCLOUD</div>
    </a>`
}
