<?php

declare(strict_types=1);

namespace OCA\NMCTheme\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\IRequest;

class DebugController extends Controller {
	public function __construct(
		string $appName,
		IRequest $request,
	) {
		parent::__construct($appName, $request);
	}

    #[PublicPage]
    #[NoCSRFRequired]
    public function exceptionPage(): void {
        throw new \RuntimeException(
            'NMCTheme intentional exception page test'
        );
    }
}