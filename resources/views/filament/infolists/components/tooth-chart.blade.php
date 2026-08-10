@php
    $data = $getState() ?? [];
    $teeth = $data['teeth'] ?? [];
    $strokes = $data['strokes'] ?? [];

    $conditions = [
        'D'  => ['label' => 'Decayed (Caries Indicated for Filling)', 'color' => '#ef4444', 'group' => 'Condition'],
        'M'  => ['label' => 'Missing due to Caries', 'color' => '#6b7280', 'group' => 'Condition'],
        'F'  => ['label' => 'Filled', 'color' => '#3b82f6', 'group' => 'Condition'],
        'I'  => ['label' => 'Caries Indicated for Extraction', 'color' => '#f97316', 'group' => 'Condition'],
        'RF' => ['label' => 'Root Fragment', 'color' => '#92400e', 'group' => 'Condition'],
        'MO' => ['label' => 'Missing due to Other Causes', 'color' => '#78716c', 'group' => 'Condition'],
        'Im' => ['label' => 'Impacted Tooth', 'color' => '#a855f7', 'group' => 'Condition'],
        'AN' => ['label' => 'Anodontia', 'color' => '#57534e', 'group' => 'Condition'],
        'PT' => ['label' => 'Peg Tooth', 'color' => '#ca8a04', 'group' => 'Condition'],

        'J'  => ['label' => 'Jacket Crown', 'color' => '#f59e0b', 'group' => 'Restoration & Prosthetics'],
        'A'  => ['label' => 'Amalgam Filling', 'color' => '#64748b', 'group' => 'Restoration & Prosthetics'],
        'AB' => ['label' => 'Abutment', 'color' => '#14b8a6', 'group' => 'Restoration & Prosthetics'],
        'P'  => ['label' => 'Pontic', 'color' => '#06b6d4', 'group' => 'Restoration & Prosthetics'],
        'In' => ['label' => 'Inlay', 'color' => '#6366f1', 'group' => 'Restoration & Prosthetics'],
        'FX' => ['label' => 'Fixed Cure Composite', 'color' => '#ec4899', 'group' => 'Restoration & Prosthetics'],
        'Rm' => ['label' => 'Removable Denture', 'color' => '#84cc16', 'group' => 'Restoration & Prosthetics'],
        'RCT' => ['label' => 'Root Canal Treatment (RCT)', 'color' => '#0ea5e9', 'group' => 'Restoration & Prosthetics'],

        'X'  => ['label' => 'Extraction due to Caries', 'color' => '#dc2626', 'group' => 'Surgery'],
        'XO' => ['label' => 'Extraction due to Other Causes', 'color' => '#7f1d1d', 'group' => 'Surgery'],
        '✓'  => ['label' => 'Present Teeth', 'color' => '#22c55e', 'group' => 'Surgery'],
        'Cm' => ['label' => 'Congenitally Missing', 'color' => '#8b5cf6', 'group' => 'Surgery'],
        'Sp' => ['label' => 'Supernumerary', 'color' => '#d946ef', 'group' => 'Surgery'],
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
        <div class="tooth-chart-palette-groups">
            @foreach (['Condition', 'Restoration & Prosthetics', 'Surgery'] as $group)
                <div class="tooth-chart-palette-group">
                    <div class="tooth-chart-group-label">{{ $group }}</div>
                    <div class="tooth-chart-palette">
                        @foreach ($conditions as $code => $condition)
                            @continue($condition['group'] !== $group)
                            <div class="tooth-chart-swatch">
                                <span class="tooth-chart-dot" style="background-color: {{ $condition['color'] }}">{{ $code }}</span>
                                {{ $condition['label'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="tc-canvas-wrap">
            {{-- Upper arch --}}
            <div class="tc-row tc-row-primary-top">
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

            <div class="tc-row tc-row-primary-bottom">
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
        .tooth-chart { display: flex; flex-direction: column; gap: 1.25rem; }

        .tooth-chart-palette-groups { display: flex; flex-direction: column; gap: 0.75rem; }
        .tooth-chart-palette-group { display: flex; flex-direction: column; gap: 0.375rem; }
        .tooth-chart-group-label {
            font-size: 0.688rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgb(107 114 128);
        }
        .dark .tooth-chart-group-label { color: rgb(156 163 175); }

        .tooth-chart-palette { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; row-gap: 0.5rem; font-size: 0.75rem; color: rgb(107 114 128); }
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

        .tc-canvas-wrap { position: relative; width: 100%; margin: 0 auto; padding: 1.5rem 0.5rem; box-sizing: border-box; }
        .tc-draw-layer { position: absolute; inset: 0; width: 100%; height: 100%; pointer-events: none; }

        .tc-row { display: flex; align-items: center; justify-content: center; gap: 0; margin: 6px 0; width: 100%; }
        .tc-row-primary-top { margin-bottom: 1in; }
        .tc-row-primary-bottom { margin-top: 1in; }
        .tc-quad { flex: 1 1 0; min-width: 0; display: grid; grid-template-columns: repeat(8, minmax(0, 1fr)); gap: 6px; }
        .tc-mid { width: 2px; flex-shrink: 0; align-self: stretch; background: rgba(0, 0, 0, 0.25); margin: 0 16px; }
        .dark .tc-mid { background: rgba(255, 255, 255, 0.25); }
        .tc-arch-divider { height: 2px; background: rgba(0, 0, 0, 0.35); margin: 16px 0; width: 100%; }
        .dark .tc-arch-divider { background: rgba(255, 255, 255, 0.3); }

        .tooth-chart-tooth {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-width: 0;
            aspect-ratio: 0.9;
            border-radius: 0.5rem;
            border: 1px solid rgba(0, 0, 0, 0.15);
            background: #fff;
            padding: 2px;
            line-height: 1.1;
        }
        .dark .tooth-chart-tooth { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.2); }
        .tc-tooth-primary { aspect-ratio: 1; border-radius: 9999px; }
        .tc-empty { width: 100%; min-width: 0; aspect-ratio: 1; }

        .tc-num { font-size: clamp(0.5rem, 2vw, 0.875rem); font-weight: 600; color: rgb(107 114 128); }
        .dark .tc-num { color: rgb(156 163 175); }
        .tc-code { font-size: clamp(0.563rem, 2.4vw, 1rem); font-weight: 700; color: rgb(9 9 11); }
        .dark .tc-code { color: rgb(250 250 250); }

        @media (max-width: 640px) {
            .tc-quad { gap: 3px; }
            .tc-mid { margin: 0 8px; }
            .tc-canvas-wrap { padding: 1rem 0.25rem; }
        }
    </style>
</x-dynamic-component>
