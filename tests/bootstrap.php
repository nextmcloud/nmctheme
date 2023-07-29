<?php

require_once __DIR__ . '/../../../tests/bootstrap.php';

\OC::$composerAutoloader->addPsr4('OCA\\NMCTheme\\Tests\\', dirname(__FILE__) . '/unit/', true);
\OC_App::loadApp('nmctheme');
OC_Hook::clear();
