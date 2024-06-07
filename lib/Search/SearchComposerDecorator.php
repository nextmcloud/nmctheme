<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2023 T-Systems International
 *
 * @author Bernd Rederlechner <bernd.rederlechner@t-systems.com>
 *
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\NMCTheme\Search;

use OC\Search\FilterCollection;
use OC\Search\SearchComposer;
use OCP\IUser;
use OCP\Search\ISearchQuery;
use OCP\Search\SearchResult;

/**
 * Blacklist unwanted search types like apps or settings for full-text search
 * so that these types are not delivered to client at all - even when using
 * API directly.
 */
class SearchComposerDecorator extends SearchComposer {
	protected SearchComposer $decorated;
	protected array $providerBlacklist = [];

	public function __construct(
		SearchComposer $decorated,
		array $providerBlacklist
	) {
		$this->decorated = $decorated;
		$this->providerBlacklist = $providerBlacklist;
	}

	/**
	 * Get providers with the blacklisted ones filtered out
	 */
	public function getProviders(string $route, array $routeParameters): array {
		$providers = $this->decorated->getProviders($route, $routeParameters);

		return array_values(array_filter($providers, function ($p) {
			return !in_array($p['id'], $this->providerBlacklist);
		}));
	}

	/**
	 * No decoration, only delegate.
	 */
	public function search(IUser $user, string $providerId, ISearchQuery $query): SearchResult {
		return $this->decorated->search($user, $providerId, $query);
	}

	/**
	 * No decoration, only delegate.
	 */
	public function buildFilterList(string $providerId, array $parameters): FilterCollection {
		return $this->decorated->buildFilterList($providerId, $parameters);
	}
}
