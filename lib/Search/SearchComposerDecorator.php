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

use OC\Search\SearchComposer;
use OCP\Search\SearchResult;
use OCP\Search\ISearchQuery;

use OCP\IUser;


/**
 * Blacklist unwanted search types like apps or settings for full-text search
 * so that these types are not delivered to client at all - even when using
 * API directly.
 */
class SearchComposerDecorator extends SearchComposer {
    protected SearchComposer $decorated;
    protected array $providerBlacklist = [];


	public function __construct(SearchComposer $decorated,
                                array $providerBlacklist ) {
		$this->decorated = $decorated;
		$this->providerBlacklist = $providerBlacklist;
    }

    /**
     * Get providers with the blacklisted ones filtered out
     */
    public function getProviders(string $route, array $routeParameters): array {
		$providers = $this->decorated->getProviders($route, $routeParameters);
		return array_filter($providers, function($p) {
            return !in_array($p['id'], $this->providerBlacklist);
        });
	}

	/**
	 * Query an individual search provider for results
	 *
	 * @param IUser $user
	 * @param string $providerId one of the IDs received by `getProviders`
	 * @param ISearchQuery $query
	 *
	 * @return SearchResult
	 * @throws InvalidArgumentException when the $providerId does not correspond to a registered provider
	 */
	public function search(IUser $user,
						   string $providerId,
						   ISearchQuery $query): SearchResult {
		
        if (in_array($providerId, $this->providerBlacklist)) {
            // for performance reasons, we do not use the correct app name for blacklisted searches
            // anyway, this is only shown if somebody tries to mess around with search
            return SearchResult::complete($providerId, []);
        } else {
            return $this->decorated->search($user, $providerId, $query);
        }
	}

}