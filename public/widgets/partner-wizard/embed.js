/* TFD Partner Wizard – Ein-Zeilen-Einbettung.
 * <script src="https://HOST/widgets/partner-wizard/embed.js" data-client-id="no1"
 *         data-primary-color="#E31E24" data-theme="light" async></script>
 * Der Wizard erscheint genau an der Stelle des Script-Tags. */
(function () {
  var s = document.currentScript;
  if (!s) return;
  var base = new URL(s.src).origin;
  var dir = base + '/widgets/partner-wizard/';
  var v = s.getAttribute('data-version') || '19';

  if (!document.querySelector('link[data-tfd-pw]')) {
    var l = document.createElement('link');
    l.rel = 'stylesheet';
    l.href = dir + 'style.css?v=' + v;
    l.setAttribute('data-tfd-pw', '1');
    document.head.appendChild(l);
  }

  var el = document.createElement('tfd-partner-wizard');
  el.setAttribute('client-id', s.getAttribute('data-client-id') || 'no1');
  el.setAttribute('api-base-url', s.getAttribute('data-api-base-url') || base);
  el.setAttribute('primary-color', s.getAttribute('data-primary-color') || '#E31E24');
  el.setAttribute('theme', s.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
  s.parentNode.insertBefore(el, s.nextSibling);

  if (window.customElements && customElements.get('tfd-partner-wizard')) return;
  if (document.querySelector('script[data-tfd-pw-lib]')) return;
  var j = document.createElement('script');
  j.src = dir + 'tfd-partner-wizard.umd.js?v=' + v;
  j.setAttribute('data-tfd-pw-lib', '1');
  document.head.appendChild(j);
})();
