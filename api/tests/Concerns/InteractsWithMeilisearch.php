<?php

namespace Tests\Concerns;

use App\Models\Product;
use Meilisearch\Client;
use Meilisearch\Contracts\TasksQuery;

/**
 * Meilisearch itself isn't part of RefreshDatabase's transaction rollback, and
 * indexing is asynchronous even once a document is submitted - tests that
 * exercise the real Meilisearch-backed code path need to explicitly clear
 * leftover documents between tests and wait for indexing to finish before
 * asserting on search/facet results.
 */
trait InteractsWithMeilisearch
{
    protected function meilisearchIndexUid(): string
    {
        return (new Product)->searchableAs();
    }

    protected function flushMeilisearchIndex(): void
    {
        $client = app(Client::class);

        try {
            $task = $client->index($this->meilisearchIndexUid())->deleteAllDocuments();
            $client->waitForTask($task['taskUid']);
        } catch (\Throwable $e) {
            // Index doesn't exist yet on a fresh Meilisearch instance - nothing to flush.
        }
    }

    protected function waitForMeilisearchIndexing(): void
    {
        $client = app(Client::class);

        $pending = $client->getTasks(
            (new TasksQuery)
                ->setIndexUids([$this->meilisearchIndexUid()])
                ->setStatuses(['enqueued', 'processing'])
        );

        $uids = array_column($pending->getResults(), 'uid');

        if (! empty($uids)) {
            $client->waitForTasks($uids);
        }
    }

    /**
     * Product's automatic Scout indexing fires on the Product model's own
     * save/delete events only - it can't know about a later, separate
     * `categories()->attach()`, variant/stock write, or attribute assignment.
     * Tests build fixtures across several separate writes like that, so rather
     * than track exactly which write must come last, force a full reindex of
     * everything currently in the test database right before asserting.
     */
    protected function reindexAllProducts(): void
    {
        Product::all()->searchable();
        $this->waitForMeilisearchIndexing();
    }
}
