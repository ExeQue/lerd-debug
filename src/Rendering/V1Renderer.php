<?php

namespace Lerd\Debug\Rendering;

use JsonSerializable;
use Lerd\Debug\Contracts\AuthEntry;
use Lerd\Debug\Contracts\Block;
use Lerd\Debug\Contracts\Blocks\Code;
use Lerd\Debug\Contracts\Blocks\Counters;
use Lerd\Debug\Contracts\Blocks\KeyValue;
use Lerd\Debug\Contracts\Blocks\Table;
use Lerd\Debug\Contracts\Blocks\Text;
use Lerd\Debug\Contracts\Chart;
use Lerd\Debug\Contracts\LogEntry;
use Lerd\Debug\Contracts\Series;
use Lerd\Debug\Contracts\TabEntry;
use Lerd\Debug\Contracts\TimelineEntry;
use Lerd\Debug\Contracts\Trackable;

use function array_keys;
use function array_map;
use function is_array;

/**
 * lerd's first schema: a timeline row with its category, colour, start and
 * duration, and a tab block with the tab it belongs to. A block or entry this
 * renderer does not know describes itself through its own JSON.
 */
class V1Renderer implements Renderer
{
    public function version(): int
    {
        return 1;
    }

    public function render(Trackable $entry): array
    {
        if ($entry instanceof TimelineEntry) {
            return [
                'type' => 'timeline',
                'label' => $entry->label(),
                'category' => $entry->category(),
                'color' => $entry->color()->value,
                'start' => $entry->start(),
                'duration_ms' => $entry->durationMs(),
                'details' => $entry->details(),
            ];
        }
        if ($entry instanceof AuthEntry) {
            return [
                'type' => 'auth',
                'id' => $entry->id(),
                'email' => $entry->email(),
                'name' => $entry->name(),
                'guard' => $entry->guard(),
            ];
        }
        if ($entry instanceof LogEntry) {
            return [
                'type' => 'log',
                'level' => $entry->level(),
                'message' => $entry->message(),
                'context' => $entry->context(),
                'trace' => $entry->withTrace(),
                'performance' => $entry->onPerformance(),
            ];
        }
        if ($entry instanceof TabEntry) {
            return [
                'type' => 'tab',
                'tab' => $entry->tabId(),
                'title' => $entry->tabTitle(),
                'columns' => $entry->tabColumns(),
                'placement' => $entry->placement() === null ? null : ['position' => $entry->placement()[0], 'tab' => $entry->placement()[1]],
                'block' => $this->tabBlock($entry),
            ];
        }

        return $this->ownJson($entry);
    }

    public function tabBlock(TabEntry $entry): array
    {
        return ['title' => $entry->blockTitle(), 'span' => $entry->span()] + $this->block($entry->block());
    }

    public function block(Block $block): array
    {
        return match (true) {
            $block instanceof Table => ['type' => 'table', 'columns' => $block->columns(), 'rows' => $block->rows()],
            $block instanceof KeyValue => ['type' => 'kv', 'values' => $block->values()],
            $block instanceof Code => ['type' => 'code', 'code' => $block->code(), 'language' => $block->language()],
            $block instanceof Text => ['type' => 'text', 'text' => $block->text()],
            $block instanceof Counters => ['type' => 'counters', 'counters' => $block->counters()],
            $block instanceof Chart => $this->chart($block),
            default => $this->ownJson($block),
        };
    }

    /**
     * What an object this renderer does not know says about itself, kept only
     * when it is a keyed array, since that is all a block or entry can be.
     *
     * @return array<string, mixed>
     */
    protected function ownJson(JsonSerializable $object): array
    {
        $data = $object->jsonSerialize();
        if (!is_array($data)) {
            return [];
        }
        $out = [];
        foreach ($data as $key => $value) {
            $out[(string) $key] = $value;
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    protected function chart(Chart $chart): array
    {
        $labels = [];
        foreach ($chart->series() as $series) {
            foreach (array_keys($series->points()) as $label) {
                $labels[(string) $label] = true;
            }
        }

        return [
            'type' => 'chart',
            'chart' => $chart->chartType()->value,
            'labels' => array_keys($labels),
            'series' => array_map(fn (Series $series) => $this->series($series), $chart->series()),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function series(Series $series): array
    {
        return ['name' => $series->name(), 'points' => $series->points(), 'color' => $series->color()?->value];
    }
}
