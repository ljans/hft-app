<?php class Problem extends Exception {}

class InvalidCredentials extends Problem {
	protected $message = 'Ungültige Zugangsdaten. Bitte überprüfe deine Eingaben.';
}

class MissingCredentials extends Problem {
	protected $message = 'Bitte fülle alle Felder aus.';
}

class DisabledUser extends Problem {
	protected $message = 'Dein Konto wurde deaktiviert.';
}

class AccessLimit extends Problem {
	protected $message = 'Von deinem Gerät wurden zu viele Anfragen in kurzer Zeit gesendet. Bitte versuche es später erneut.';
}

class MaintenancePeriod extends Problem {
	protected $message = 'Die Server der HFT sind wegen Wartungsarbeiten nicht erreichbar. Bitte versuche es später erneut.';
}

class InvalidDevice extends Problem {}
class InvalidAction extends Problem {}
class GatewayError extends Problem {}
class FormatError extends Problem {}
