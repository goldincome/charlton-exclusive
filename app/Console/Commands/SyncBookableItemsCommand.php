<?php

namespace App\Console\Commands;

use App\Services\BookableItemMapper;
use Illuminate\Console\Command;

class SyncBookableItemsCommand extends Command
{
    protected $signature = 'sections:sync-bookable-items';

    protected $description = 'Map existing halls, events, dining tables, and menu items to sections/bookable_items.';

    public function handle(BookableItemMapper $mapper): int
    {
        $counts = $mapper->syncCoreResources();
        $this->info('Bookable items synced: '.json_encode($counts));

        return self::SUCCESS;
    }
}
