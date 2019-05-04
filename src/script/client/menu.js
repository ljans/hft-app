// Select installer elements
const $installer = $('.installer');

// Show installer if not launched standalone
if(!window.matchMedia('(display-mode: standalone)').matches && !window.navigator.standalone) $installer.toggleClass('hidden', false);

// Catch prompt
let deferredPrompt;
window.addEventListener('beforeinstallprompt', e => deferredPrompt = e);

// Register click handler
$installer.on('click', () => {
	if(deferredPrompt) deferredPrompt.prompt();
	else alert('Öffne die Seitenoptionen deines Browsers und wähle "Zum Startbildschirm hinzufügen".');
});

// Logout confirmation
$('.logout').on('click', e => {
	if(!confirm('Möchtest du dich wirklich abmelden?\nDadurch werden alle deine Daten auf diesem Gerät gelöscht.')) e.preventDefault();
});
