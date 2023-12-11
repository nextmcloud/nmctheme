<?php

declare(strict_types=1);
/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme\Command;

use \Symfony\Component\Console\Command\Command;

use Exception;
use OCA\Theming\ThemingDefaults;

use OCA\Theming\Util;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CacheBuster extends Command {

	protected ThemingDefaults $themingDefaults;
	protected Util $themeUtil;

	public function __construct(ThemingDefaults $themeingDefaults,
		Util $themeUtil) {
		parent::__construct();
		$this->themeingDefaults = $themeingDefaults;
		$this->themeUtil = $themeUtil;
	}

	protected function configure() {
		$this
			->setName('nmctheme:cachebuster')
			->setDescription('Show/modify cachebuster')
			->setHelp('Show/bust the cachebuster query parameter to remote bust browser caches')
			->addOption('bust', 'b', InputOption::VALUE_OPTIONAL, 'Bust all user browser caches', false);
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		try {
			$doBust = $input->getOption('bust');
			if ($doBust === false) {
				$output->writeln('CacheBusterValue=' . $this->themeUtil->getCacheBuster());
			} else {
				$output->writeln('CacheBusterOldValue=' . $this->themeUtil->getCacheBuster());
				$this->themeingDefaults->increaseCacheBuster();
				$output->writeln('CacheBusterNewValue=' . $this->themeUtil->getCacheBuster());
			}

		} catch (Exception $e) {
			$output->writeln('Error: ' . $e->getMessage());
			return -1;
		}
		return 0;
	}
}
