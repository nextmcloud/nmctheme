<?php
/**
 * TODO Change L10NString implementation using IL10N in constructor instead of
 * L10N.
 * 
 * For the meantime, this is the workaround
 *
 */
namespace OCA\NMCTheme\L10N;

use OCP\IL10N;

class L10NString extends \OC\L10N\L10NString {

    public function __construct(IL10N $l10n, $text, array $parameters, int $count = 1) {
		$this->l10n = $l10n;
		$this->text = $text;
		$this->parameters = $parameters;
		$this->count = $count;
	}
}