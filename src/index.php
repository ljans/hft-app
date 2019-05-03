<!DOCTYPE html>
<html lang="de">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="author" content="Lukas Jans">
		<meta name="generator" content="Luniverse Handcrafted">
		<meta name="keywords" content="HFT, Hochschule für Technik, Stuttgart, App, Download, Vorlesungen, Kurse, Mensa, Termine, Noten, Luniverse">
		<meta name="description" content="Vorlesungen, Termine und mehr auf deinem Smartphone.">
		<meta name="google" content="notranslate">
	
		<title>HFT App &middot; Für dein Studium</title>
		
		<link rel="icon" type="image/png" href="/image/favicon.png">
		
		<link rel="stylesheet" href="/style/intro.scss">
		
		<meta property="og:url" content="https://hft-app.de/">
		<meta property="og:title" content="HFT App &middot; Für dein Studium">
		<meta property="og:description" content="Vorlesungen, Termine und mehr auf deinem Smartphone.">
		<!--<meta property="og:type" content="website">-->
		<meta property="og:locale" content="de_DE">
		<meta property="og:image" content="https://hft-app.de/image/icon.png">
		<meta property="og:image:type" content="image/png">
		<meta property="og:image:width" content="192">
		<meta property="og:image:height" content="192">
	</head>
	<body>
		<div class="bar">
			<a href="/launch" class="container">
				<div class="app">
					<img class="logo" src="/image/icon.png" alt="Icon">
					<div class="info">
						<div class="title">HFT App</div>
						<div class="description">Im Browser starten</div>
					</div>
				</div>
			</a>
		</div>
	
		<?php foreach([[
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
		]] as $article) print '
			<article>
				<div class="container">
					<div class="description">
						<h2>'.$article['title'].'</h2>
						<p>'.$article['description'][0].'</p>
						<p>'.$article['description'][1].'</p>
					</div>
					<div class="screenshot">
						<img src="/image/intro/'.$article['name'].'.png">
					</div>
				</div>
			</article>
		'; ?>
	
		<div class="banner">
			<div class="container">
				<div class="icon icon-prepend icon-info-circle"></div>
				<div class="text">Diese App wurde von Studenten für Studenten entwickelt und ist daher keine offizielle Software der HFT Stuttgart.</div>
			</div>
		</div>
		
		<footer>
			<span>
				<a href="https://github.com/luniverse/hft-app" title="GitHub Repository" target="_blank" class="icon icon-prepend icon-github-alt">hft-app</a> 
				&middot; <a href="//luniversity.de/info/imprint" title="Impressum" target="_blank">Impressum</a>
			<span>
		</footer>
	</body>
</html>