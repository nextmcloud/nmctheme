class TeleBrandFooter extends HTMLElement {
    connectedCallback() {
        this.innerHTML = `
        <footer id="telekom-footer" role="contentinfo">
            <div class="telekom-footer-minimal">
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
        </footer>
        `;
    }
}

window.customElements.define('telekom-footer-element', TeleBrandFooter);

var footerElement = document.querySelector('body footer');
if ( footerElement === null ) {
    // add footer tag
    document.body.appendChild(new TeleBrandFooter());
} else {
    //replace footer tag
    document.body.replaceChild(new TeleBrandFooter(), footerElement);
}







