<?php class Problem extends Exception {}

class InvalidCredentials extends Problem {}
class MissingCredentials extends Problem {}
class DisabledUser extends Problem {}
class AccessLimit extends Problem {}
class MaintenancePeriod extends Problem {}
class InvalidDevice extends Problem {}
class InvalidAction extends Problem {}
class GatewayError extends Problem {}
class FormatError extends Problem {}
