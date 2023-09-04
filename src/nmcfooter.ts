import { translate as t } from '@nextcloud/l10n'

const app = 'nmctheme'
const copyright = t(app, 'Copyright')
const opensource = t(app, 'OpenSource')
const impressum = t(app, 'Impressum')
const dataprotection = t(app, 'Data protection')
const faq = t(app, 'Faq')

const footerContent = `
<div class="footer-content">
    <div id="notice">${copyright}</div>
    <ul id="navigation">
        <li><a href="https://static.magentacloud.de/licences/webui.htm" target="_blank" rel="noreferrer noopener">${opensource}</a>
        <li><a href="http://www.telekom.de/impressum" target="_blank" rel="noreferrer noopener">${impressum}</a></li>
        <li><a href="https://static.magentacloud.de/Datenschutz" target="_blank" rel="noreferrer noopener">${dataprotection}</a>
        </li>
        <li><a href="https://cloud.telekom-dienste.de/hilfe" target="_blank" rel="noreferrer noopener">${faq}</a></li>
    </ul>
</div>
`
const footerRole = 'contentinfo'
const footerId = 'telekom-minimal-footer'

let footerElement = document.querySelector('body footer')
if (footerElement === null) {
	// add footer tag
	footerElement = document.createElement('footer')
	footerElement.innerHTML = footerContent
	footerElement.setAttribute('role', footerRole)
	footerElement.setAttribute('id', footerId)
	document.body.appendChild(footerElement)
} else {
	footerElement.innerHTML = footerContent
	footerElement.setAttribute('role', footerRole)
	footerElement.setAttribute('id', footerId)
}
