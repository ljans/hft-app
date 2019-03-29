<?php
require '../code/elements.php';
require '../code/detect.php';

// Detect page
$page = isset($_GET['page']) && in_array($_GET['page'], ['launch', 'install', 'error', 'upgrade']) ? $_GET['page'] : 'intro';

// Define specific template data
switch($page) {
	
	// Intro template
	case 'intro': {
		Elements::$data['articles'] = [[
			'name' => 'lectures',
			'title' => 'Dein persönlicher Stundenplan',
			'description' => [
				'Stelle deinen eigenen Stundenplan zusammen, indem du Kurse aus dem Vorlesungsverzeichnis auswählst.',
				'Ausfälle oder Raumänderungen werden automatisch aus dem LSF synchronisiert.'
			]
		],[
			'name' => 'meals',
			'title' => 'Mensa-Speiseplan',
			'description' => [
				'Mit der App hast du immer den aktuellen Mensa-Speiseplan dabei.',
				'Die zweiwöchige Vorschau enthält auch Bilder des Angebots, um dir die Entscheidung zu erleichtern.'
			]
		],[
			'name' => 'exams',
			'title' => 'Notenübersicht',
			'description' => [
				'Deine Noten werden aus dem LSF synchronisiert, sodass du alles auf einen Blick sehen kannst.',
				'Bei neuen Prüfungsergebnissen erhältst du automatisch eine Benachrichtigung.'
			]
		],[
			'name' => 'events',
			'title' => 'Hochschul-Terminkalender',
			'description' => [
				'Verpasse keine wichtigen Hochschul-Termine mehr mit dem eingebauten Kalender.',
				'Die aktuellen Termine sind für dich auch auf der Startseite zusammengefasst.'
			]
		],[
			'name' => 'menu',
			'title' => 'Weitere Inhalte',
			'description' => [
				'Darüber hinaus enthält die App viele weitere nützliche Funktionen rund um das Hochschulleben.',
				'Hier kannst du dich auch abmelden, um die Verknüpfung mit deinem Gerät wieder aufzuheben.'
			]
		]];
	} break;
	
	// Install template
	case 'install': {
		
		// Safari on iOS
		if(Detect::os() == 'Apple' && Detect::browser() == 'Safari') Elements::$data = [
			'store' => 'App Store',
			'home' => 'Home-Bildschirm',
			'step' => 'Tippe auf <img src="/image/share.png" alt title="Share icon" class="share"> in der unteren Statusleiste.'
		];
		
		// Chrome on Android
		elseif(Detect::os() == 'Android' && Detect::browser() == 'Chrome') Elements::$data = [
			'store' => 'Play Store',
			'home' => 'Startbildschirm',
			'step' => 'Tippe auf <span class="icon icon-ellipsis-v"></span> am rechten oberen Bildschirmende.'
		];
		
		// Other browser
		else Elements::$data = [
			'store' => 'App Store',
			'home' => 'Startbildschirm',
			'step' => 'Öffne die Optionen deines Browsers.'
		];
	} break;
	
	// Error template
	case 'error': {
			
		// Render error message
		switch($_GET['error']) {
			
			// Incompatible device
			case 'incompatible': {
				switch(Detect::os()) {
					
					// Android information
					case 'Android': {
						Elements::$data = [
							'icons' => ['exclamation-triangle', 'chrome'],
							'infos' => [
								'Mit diesem Browser kannst du die App leider nicht verwenden.', 
								'Bitte nutze Chrome (ab Version 45) auf deinem Android-Gerät.'
							]
						];
					} break;
					
					// iOS information
					case 'Apple': {
						Elements::$data = [
							'icons' => ['exclamation-triangle', 'safari'],
							'infos' => [
								'Mit diesem Browser kannst du die App leider nicht verwenden.',
								'Bitte nutze Safari (ab iOS 11.3) auf deinem Apple-Gerät.'
							]
						];
					} break;
					
					// Default information
					default: {
						Elements::$data = [
							'icons' => ['chrome', 'safari'],
							'infos' => [
								'Mit diesem Browser kannst du die App leider nicht verwenden.',
								'Bitte nutze Chrome auf Android-Geräten oder Safari auf Apple-Geräten.'
							]
						];
					} break;
				}
			} break;
			
			// Invalid device
			case 'invalid device': {
				Elements::$data = [
					'icons' => ['mobile', 'chain-broken'],
					'infos' => [
						'Dein Gerät konnte nicht identifiziert werden. Dies kann passieren, wenn du die App längere Zeit nicht genutzt hast.',
						'Bitte melde dich erneut an.'
					], 'action' => [
						'href' => '/login',
						'title' => 'Jetzt anmelden'
					]
				];
			} break;
			
			// Invalid credentials
			case 'invalid credentials': {
				Elements::$data = [
					'icons' => ['mobile', 'lock'],
					'infos' => [
						'Der Zugriff wurde verweigert. Dies kann passieren, wenn du dein Passwort geändert hast.',
						'Bitte melde dich erneut an.'
					], 'action' => [
						'href' => '/login',
						'title' => 'Jetzt anmelden'
					]
				];
			} break;
			
			// Under maintenance
			case '503': {
				Elements::$data = [
					'icons' => ['server', 'wrench'],
					'infos' => [
						'Die Server der HFT sind wegen Wartungsarbeiten nicht erreichbar.',
						'Bitte versuche es später erneut.'
					], 'action' => [
						'href' => '/launch',
						'title' => 'Zurück'
					]
				];
			} break;
			
			// Server error
			case 'gateway': {
				Elements::$data = [
					'icons' => ['server', 'chain-broken'],
					'infos' => [
						'Es gab ein Problem bei der Verbindung zu den Servern der HFT.',
						'Bitte versuche es später erneut.'
					], 'action' => [
						'href' => '/launch',
						'title' => 'Zurück'
					]
				];
			} break;
			
			// Request failed
			case 'aborted': {
				Elements::$data = [
					'icons' => ['mobile', 'wifi'],
					'infos' => [
						'Die Anfrage wurde abgebrochen.',
						'Bitte stelle sicher, dass du mit dem Internet verbunden bist.'
					], 'action' => [
						'href' => '/launch',
						'title' => 'Wiederholen'
					]
				];
			} break;
			
			// Too many requests
			case 'cooldown': {
				Elements::$data = [
					'icons' => ['shield', 'exclamation-triangle'],
					'infos' => [
						'Von deinem Gerät wurden zu viele Anfragen in kurzer Zeit gesendet.',
						'Bitte versuche es später erneut.'
					], 'action' => [
						'href' => '/launch',
						'title' => 'Zurück'
					]
				];
			} break;
			
			// Disabled
			case 'disabled': {
				Elements::$data = [
					'icons' => ['ban', 'exclamation-triangle'],
					'infos' => [
						'Dein Konto wurde deaktiviert.',
						'Du weißt nicht, warum das passiert ist?'
					], 'action' => [
						'href' => 'mailto:info@hft-app.de',
						'title' => 'Hilfe anfordern'
					]
				];
			} break;
			
			// Offline
			case 'offline': {
				Elements::$data = [
					'icons' => ['mobile', 'wifi'],
					'infos' => [
						'Der Server konnte nicht erreicht werden.',
						'Bitte stelle sicher, dass du mit dem Internet verbunden bist.'
					], 'action' => [
						'href' => '/launch',
						'title' => 'Neu starten'
					]
				];
			} break;
			
			// Installation broken (404)
			case 'broken': {
				Elements::$data = [
					'icons' => ['mobile', 'exclamation-triangle'],
					'infos' => [
						'Es gab ein Problem beim Starten der App.',
						'Dies kann passieren, wenn du den Cache deines Browsers geleert hast oder nicht genug Speicherplatz auf deinem Gerät zur Verfügung steht.'
					], 'action' => [
						'href' => '/launch',
						'title' => 'Nochmal versuchen'
					]
				];
			} break;
			
			// Unknown error
			default: {
				Elements::$data = [
					'icons' => ['bug', 'bolt'],
					'infos' => [
						'Ein unbekanntes Problem ist aufgetreten.',
						'Falls dieser Fehler wiederholt angezeigt wird, kannst du die App unter <a class="underlined" href="https://hft-app.de">hft-app.de</a> erneut installieren.'
					], 'action' => [
						'href' => '/launch',
						'title' => 'Nochmal versuchen'
					]
				];
			} break;
		}
	} break;
}

// Render template
Elements::$path = '../template/';
print Elements::renderFile($page);