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
}

// Render template
Elements::$path = '../template/';
print Elements::renderFile($page);