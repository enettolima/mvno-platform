<?php

require_once('../bootstrap.php');
require_once('SimpleAuth.php');

use Luracast\Restler\Resources;
Resources::$useFormatAsExtension = false;

use Luracast\Restler\Restler;

$r = new Restler(true, true);
$r->addAPIClass('Luracast\\Restler\\Resources');
$r->setSupportedFormats('JsonFormat');
$r->addAuthenticationClass('SimpleAuth');
$r->addAPIClass('User');
$r->addAPIClass('Book');
$r->addAPIClass('Car');
$r->addAPIClass('Ads');
$r->addAPIClass('Clicks');
$r->addAPIClass('Points');
$r->addAPIClass('Plan');
$r->addAPIClass('Mvnos');
$r->addAPIClass('Subscribers');
$r->addAPIClass('Mvno');
$r->addAPIClass('Impression');
$r->handle();
?>
