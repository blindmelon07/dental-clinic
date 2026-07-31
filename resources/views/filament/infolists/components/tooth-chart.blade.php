@php
    $data = $getState() ?? [];
    $teeth = $data['teeth'] ?? [];
    $strokes = $data['strokes'] ?? [];

    $conditions = [
        'D'  => ['label' => 'Decayed / Caries', 'color' => '#ef4444'],
        'M'  => ['label' => 'Missing', 'color' => '#6b7280'],
        'P'  => ['label' => 'Filled / Pontic', 'color' => '#3b82f6'],
        'Cu' => ['label' => 'Crown', 'color' => '#f59e0b'],
    ];

    $permQuads = [
        'ur' => [18, 17, 16, 15, 14, 13, 12, 11],
        'ul' => [21, 22, 23, 24, 25, 26, 27, 28],
        'lr' => [48, 47, 46, 45, 44, 43, 42, 41],
        'll' => [31, 32, 33, 34, 35, 36, 37, 38],
    ];

    $primaryFor = function (int $n): ?int {
        $quadrant = intdiv($n, 10);
        $position = $n % 10;

        return $position <= 5 ? (($quadrant + 4) * 10 + $position) : null;
    };

    $cell = function (int $n, bool $isPrimary = false) use ($teeth, $conditions) {
        $code = $teeth[$n] ?? null;
        $condition = $code ? ($conditions[$code] ?? null) : null;
        $class = 'tooth-chart-tooth' . ($isPrimary ? ' tc-tooth-primary' : '');
        $style = $condition ? 'background-color: ' . $condition['color'] . ';' : '';
        $title = $condition['label'] ?? 'Healthy';

        return "<div class=\"{$class}\" style=\"{$style}\" title=\"{$title}\"><span class=\"tc-num\">{$n}</span><span class=\"tc-code\">" . e($code ?? '') . '</span></div>';
    };
@endphp

<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div class="tooth-chart">
        <div class="tooth-chart-palette">
            @foreach ($conditions as $code => $condition)
                <div class="tooth-chart-swatch">
                    <span class="tooth-chart-dot" style="background-color: {{ $condition['color'] }}">{{ $code }}</span>
                    {{ $condition['label'] }}
                </div>
            @endforeach
        </div>

        <div class="tc-canvas-wrap">
            {{-- Upper arch --}}
            <div class="tc-row">
                <div class="tc-quad">
                    @foreach ($permQuads['ur'] as $n)
                        {!! ($primary = $primaryFor($n)) ? $cell($primary, true) : '<div class="tc-empty"></div>' !!}
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ul'] as $n)
                        {!! ($primary = $primaryFor($n)) ? $cell($primary, true) : '<div class="tc-empty"></div>' !!}
                    @endforeach
                </div>
            </div>

            <div class="tc-row">
                <div class="tc-quad">
                    @foreach ($permQuads['ur'] as $n)
                        {!! $cell($n) !!}
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ul'] as $n)
                        {!! $cell($n) !!}
                    @endforeach
                </div>
            </div>

            <div class="tc-arch-divider"></div>

            {{-- Lower arch --}}
            <div class="tc-row">
                <div class="tc-quad">
                    @foreach ($permQuads['lr'] as $n)
                        {!! $cell($n) !!}
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ll'] as $n)
                        {!! $cell($n) !!}
                    @endforeach
                </div>
            </div>

            <div class="tc-row">
                <div class="tc-quad">
                    @foreach ($permQuads['lr'] as $n)
                        {!! ($primary = $primaryFor($n)) ? $cell($primary, true) : '<div class="tc-empty"></div>' !!}
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ll'] as $n)
                        {!! ($primary = $primaryFor($n)) ? $cell($primary, true) : '<div class="tc-empty"></div>' !!}
                    @endforeach
                </div>
            </div>

            @if (! empty($strokes))
                <svg class="tc-draw-layer" viewBox="0 0 100 100" preserveAspectRatio="none">
                    @foreach ($strokes as $stroke)
                        @php
                            $points = collect($stroke['points'] ?? [])->map(fn ($p) => $p[0] . ',' . $p[1])->implode(' ');
                        @endphp
                        <polyline points="{{ $points }}" fill="none" stroke="{{ $stroke['color'] ?? '#111827' }}" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
                    @endforeach
                </svg>
            @endif
        </div>

        <div class="tooth-chart-legend-note">Outer rows: permanent teeth (11&ndash;48). Inner rows: temporary/primary teeth (51&ndash;85), shown only where a primary tooth exists.</div>
    </div>

    <style>
        .tooth-chart { display: flex; flex-direction: column; gap: 0.5rem; }

        .tooth-chart-palette { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; font-size: 0.75rem; color: rgb(107 114 128); }
        .dark .tooth-chart-palette { color: rgb(156 163 175); }
        .tooth-chart-swatch { display: flex; align-items: center; gap: 0.375rem; }
        .tooth-chart-dot {
            min-width: 1.125rem;
            height: 1.125rem;
            padding: 0 0.25rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(0, 0, 0, 0.15);
            font-size: 0.625rem;
            font-weight: 700;
            color: #fff;
        }

        .tooth-chart-legend-note { font-size: 0.75rem; color: rgb(107 114 128); margin: 0; text-align: center; }
        .dark .tooth-chart-legend-note { color: rgb(156 163 175); }

        .tc-canvas-wrap { position: relative; max-width: 56rem; margin: 0 auto; padding: 0.5rem 0; }
        .tc-draw-layer { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }

        .tc-row { display: flex; align-items: center; justify-content: center; gap: 0; margin: 4px 0; }
        .tc-quad { display: grid; grid-template-columns: repeat(8, 3.25rem); gap: 6px; }
        .tc-mid { width: 2px; align-self: stretch; background: rgba(0, 0, 0, 0.25); margin: 0 14px; }
        .dark .tc-mid { background: rgba(255, 255, 255, 0.25); }
        .tc-arch-divider { height: 2px; background: rgba(0, 0, 0, 0.35); margin: 12px auto; width: 100%; max-width: 52rem; }
        .dark .tc-arch-divider { background: rgba(255, 255, 255, 0.3); }

        .tooth-chart-tooth {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 3.25rem;
            height: 3.5rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.15);
            background: #fff;
            padding: 2px;
            line-height: 1.1;
        }
        .dark .tooth-chart-tooth { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.2); }
        .tc-tooth-primary { width: 2.75rem; height: 2.75rem; border-radius: 9999px; }
        .tc-empty { width: 3.25rem; height: 2.75rem; }

        .tc-num { font-size: 0.75rem; font-weight: 600; color: rgb(107 114 128); }
        .dark .tc-num { color: rgb(156 163 175); }
        .tc-code { font-size: 1rem; font-weight: 700; color: rgb(9 9 11); }
        .dark .tc-code { color: rgb(250 250 250); }
    </style>
</x-dynamic-component>
