var footerElement = document.querySelector('body footer');
if ( footerElement === null ) {
    // add footer tag
    footerElement = document.createElement('footer');
    document.body.appendChild(footerElement);
} 

footerElement.innerHTML=`
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
`; 
footerElement.setAttribute('role', 'contentinfo');
footerElement.setAttribute('id', 'telekom-minimal-footer');

if ( footerElement === null ) {
    // add footer tag
    footerElement = document.createElement('footer');
    document.body.appendChild(footerElement);
} 
