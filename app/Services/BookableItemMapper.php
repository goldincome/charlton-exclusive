<?php

namespace App\Services;

use App\Models\BookableItem;
use App\Models\DiningTable;
use App\Models\Event;
use App\Models\Hall;
use App\Models\MenuItem;
use App\Models\Section;

class BookableItemMapper
{
    public function syncCoreResources(): array
    {
        $counts = ['halls' => 0, 'events' => 0, 'tables' => 0, 'menu_items' => 0];

        $meeting = Section::query()->firstOrCreate(['slug' => 'meeting-rooms'], ['name' => 'Meeting Rooms', 'type' => Section::TYPE_SLOT, 'access_type' => Section::ACCESS_MEMBERS_ONLY, 'active' => true]);
        $events = Section::query()->firstOrCreate(['slug' => 'events'], ['name' => 'Events', 'type' => Section::TYPE_SLOT, 'access_type' => Section::ACCESS_MEMBERS_ONLY, 'active' => true]);
        $dining = Section::query()->firstOrCreate(['slug' => 'dining'], ['name' => 'Dining', 'type' => Section::TYPE_CART, 'access_type' => Section::ACCESS_OPEN, 'active' => true]);

        foreach (Hall::query()->get() as $hall) {
            BookableItem::query()->updateOrCreate(
                ['source_type' => Hall::class, 'source_id' => $hall->id],
                ['section_id' => $meeting->id, 'name' => $hall->name, 'description' => $hall->description, 'price' => 0, 'pricing_unit' => 'hour', 'capacity' => $hall->capacity, 'is_active' => (bool) $hall->active, 'metadata' => ['legacy' => 'hall']]
            );
            $counts['halls']++;
        }

        foreach (Event::query()->get() as $event) {
            BookableItem::query()->updateOrCreate(
                ['source_type' => Event::class, 'source_id' => $event->id],
                ['section_id' => $events->id, 'name' => $event->name, 'description' => $event->description, 'price' => (int) $event->price_per_ticket, 'pricing_unit' => 'flat', 'capacity' => $event->capacity, 'is_active' => (bool) $event->active, 'metadata' => ['legacy' => 'event']]
            );
            $counts['events']++;
        }

        if (class_exists(DiningTable::class)) {
            foreach (DiningTable::query()->get() as $table) {
                BookableItem::query()->updateOrCreate(
                    ['source_type' => DiningTable::class, 'source_id' => $table->id],
                    ['section_id' => $dining->id, 'name' => $table->name ?? ('Table '.$table->id), 'description' => null, 'price' => 0, 'pricing_unit' => 'flat', 'capacity' => $table->capacity ?? null, 'is_active' => (bool) ($table->active ?? true), 'metadata' => ['legacy' => 'dining_table']]
                );
                $counts['tables']++;
            }
        }

        if (class_exists(MenuItem::class)) {
            foreach (MenuItem::query()->get() as $item) {
                BookableItem::query()->updateOrCreate(
                    ['source_type' => MenuItem::class, 'source_id' => $item->id],
                    ['section_id' => $dining->id, 'name' => $item->name, 'description' => $item->description ?? null, 'price' => (int) ($item->price ?? 0), 'pricing_unit' => 'flat', 'capacity' => null, 'is_active' => (bool) ($item->active ?? true), 'metadata' => ['legacy' => 'menu_item']]
                );
                $counts['menu_items']++;
            }
        }

        return $counts;
    }
}
