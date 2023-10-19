<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author B. Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 *
 * Test blacklisting of search providers to avoid unwanted test results
 */
namespace OCA\NMCTheme\Test\Search;

use OC\AppFramework\Bootstrap\Coordinator;
use OC\Search\SearchComposer;
use OCA\NMCTheme\Search\SearchComposerDecorator;
use OCP\IServerContainer;
use OCP\IUser;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;
use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;

class SearchComposerDecoratorTest extends TestCase {
	
	protected function setUp(): void {
		parent::setUp();

		// test behavior in combination with the core apps
		$this->app = new \OCP\AppFramework\App("nmctheme");
	}

	public function testGetProviders() {
		$this->composerService =
			new SearchComposerDecorator(
				new SearchComposer(
					$this->app->getContainer()->get(Coordinator::class),
					$this->app->getContainer()->get(IServerContainer::class),
					$this->app->getContainer()->get(LoggerInterface::class)
				),
				['contacts', // from apps/dav
					'calendar',
					'tasks',
					'settings_apps', // from apps/settings
					'settings',
					'systemtags' // from apps/systemtags (first candidate to enable in the future!)
				]
			);
		$providers = array_values($this->composerService->getProviders('/', []));
		$providerIds = array_map(function ($p) { return $p['id']; }, $providers);
		$this->assertNotContains('contacts', $providerIds);
		$this->assertNotContains('calender', $providerIds);
		$this->assertNotContains('tasks', $providerIds);
		$this->assertNotContains('settings_apps', $providerIds);
		$this->assertNotContains('settings', $providerIds);
		$this->assertNotContains('systemtags', $providerIds);
		$this->assertContains('files', $providerIds);
	}

	protected function createSearchMock() {
		$this->decorated = $this->createMock(SearchComposer::class);
		$this->composerService =
			new SearchComposerDecorator(
				$this->decorated,
				['contacts', // from apps/dav
					'calendar',
					'tasks',
					'settings_apps', // from apps/settings
					'settings',
					'systemtags' // from apps/systemtags (first candidate to enable in the future!)
				]
			);
		$this->user = $this->createMock(IUser::class);
		$this->query = $this->createMock(ISearchQuery::class);
	}

	public function testSearchBlacklisted() {
		$this->createSearchMock();
		$this->decorated->expects(self::never())->method("search");
		$result = $this->composerService->search($this->user, 'tasks', $this->query)->jsonSerialize();

		$this->assertEquals('tasks', $result['name']);
		$this->assertFalse($result['isPaginated']);
		$this->assertEmpty($result['entries']);
	}

	public function testSearch() {
		$this->createSearchMock();
		$this->decorated->expects(self::once())
			->method("search")
			->with($this->equalTo($this->user), $this->equalTo('files'), $this->equalTo($this->query))
			->willReturn(SearchResult::complete('files', []));
		$result = $this->composerService->search($this->user, 'files', $this->query)->jsonSerialize();

		
	}

}
