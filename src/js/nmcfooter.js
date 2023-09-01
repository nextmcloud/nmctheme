const footerContent = `
<div class="footer-content">
    <div id="notice">
        (C) Telekom Deutschland GmbH
    </div>
    <ul id="navigation">
        <li><a href="https://static.magentacloud.de/licences/webui.htm" target="_blank" rel="noreferrer noopener">Open Source Lizenzen</a>
        <li><a href="http://www.telekom.de/impressum" target="_blank" rel="noreferrer noopener">Impressum</a></li>
        <li><a href="https://static.magentacloud.de/Datenschutz" target="_blank" rel="noreferrer noopener">Datenschutz</a>
        </li>
        <li><a href="https://cloud.telekom-dienste.de/hilfe" target="_blank" rel="noreferrer noopener">Hilfe und FAQ</a></li>
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
