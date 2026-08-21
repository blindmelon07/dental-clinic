@php
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

    $conditionGroups = \App\Models\ToothCondition::GROUPS;
    $conditions = \App\Models\ToothCondition::active()->ordered()->get()
        ->mapWithKeys(fn ($condition) => [
            $condition->code => [
                'label' => $condition->label,
                'color' => $condition->color,
                'group' => $condition->group,
            ],
        ])->all();
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div
        x-data="{
            // Not calling .live() here keeps every paint stroke / drawn line
            // purely client-side in Alpine — it only syncs to the Livewire
            // component (and from there, the database) on the next real
            // request, i.e. when the record is actually saved. (Livewire 4's
            // $wire.$entangle() only recognizes a .live() modifier to opt IN
            // to immediate syncing; there is no .defer — leaving it plain is
            // already deferred by default. An earlier version of this code
            // chained a nonexistent `.defer` onto the entangle call, which
            // silently evaluated to `undefined` and detached `state` from
            // Livewire entirely: teeth painted fine on screen but never
            // reached the saved form data, so Save appeared to work while
            // the chart quietly reverted on reload.)
            state: $wire.$entangle('{{ $getStatePath() }}'),
            isDisabled: @js($isDisabled()),
            mode: 'stamp',
            selected: 'D',
            penColor: '#111827',
            painting: false,
            paintValue: null,
            drawing: false,
            currentStroke: null,
            conditionGroups: @js($conditionGroups),
            conditions: @js($conditions),
            conditionsIn(group) {
                return Object.entries(this.conditions).filter(([key, condition]) => condition.group === group);
            },
            ensureState() {
                return {
                    teeth: (this.state && this.state.teeth) ? this.state.teeth : {},
                    strokes: (this.state && this.state.strokes) ? this.state.strokes : [],
                };
            },
            colorFor(n) {
                const code = this.ensureState().teeth[n];
                return code ? (this.conditions[code]?.color ?? '') : '';
            },
            codeFor(n) {
                return this.ensureState().teeth[n] ?? '';
            },
            labelFor(n) {
                const code = this.ensureState().teeth[n];
                return code ? (this.conditions[code]?.label ?? code) : 'Healthy';
            },
            startPaint(n) {
                if (this.isDisabled || this.mode !== 'stamp') return;
                this.painting = true;
                if (this.selected === 'DELETE') {
                    this.paintValue = null;
                } else {
                    const current = this.ensureState().teeth[n] ?? null;
                    this.paintValue = (current === this.selected) ? null : this.selected;
                }
                this.applyTooth(n);
            },
            applyTooth(n) {
                const s = this.ensureState();
                const teeth = { ...s.teeth };
                if (this.paintValue) {
                    teeth[n] = this.paintValue;
                } else {
                    delete teeth[n];
                }
                this.state = { ...s, teeth };
            },
            dragTooth(n) {
                if (this.painting) this.applyTooth(n);
            },
            stopPaint() {
                this.painting = false;
                this.paintValue = null;
            },
            clearTeeth() {
                if (this.isDisabled) return;
                this.state = { ...this.ensureState(), teeth: {} };
            },
            touchTooth(event) {
                if (this.mode !== 'stamp') return;
                const touch = event.touches[0];
                if (!touch) return;
                const target = document.elementFromPoint(touch.clientX, touch.clientY)?.closest('[data-tooth]');
                if (target) this.dragTooth(parseInt(target.dataset.tooth, 10));
            },
            initCanvas() {
                this.resizeCanvas();
                new ResizeObserver(() => this.resizeCanvas()).observe(this.$refs.canvas);
                this.$watch('state', () => this.redraw());
            },
            resizeCanvas() {
                const canvas = this.$refs.canvas;
                const rect = canvas.getBoundingClientRect();
                if (! rect.width || ! rect.height) return;
                const ratio = window.devicePixelRatio || 1;
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                this.redraw();
            },
            redraw() {
                const canvas = this.$refs.canvas;
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                this.ensureState().strokes.forEach((stroke) => this.drawStroke(ctx, stroke.points, stroke.color, canvas));
                if (this.currentStroke && this.currentStroke.length > 1) {
                    this.drawStroke(ctx, this.currentStroke, this.penColor, canvas);
                }
            },
            drawStroke(ctx, points, color, canvas) {
                if (! points || points.length < 2) return;
                ctx.beginPath();
                ctx.strokeStyle = color;
                ctx.lineWidth = 2.25 * (window.devicePixelRatio || 1);
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                points.forEach(([xPct, yPct], i) => {
                    const x = (xPct / 100) * canvas.width;
                    const y = (yPct / 100) * canvas.height;
                    i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
                });
                ctx.stroke();
            },
            getPercentPoint(event) {
                const rect = this.$refs.canvas.getBoundingClientRect();
                const t = event.touches?.[0] ?? event.changedTouches?.[0] ?? event;
                const x = ((t.clientX - rect.left) / rect.width) * 100;
                const y = ((t.clientY - rect.top) / rect.height) * 100;
                return [Math.max(0, Math.min(100, x)), Math.max(0, Math.min(100, y))];
            },
            startStroke(event) {
                if (this.isDisabled || this.mode !== 'draw') return;
                this.drawing = true;
                this.currentStroke = [this.getPercentPoint(event)];
            },
            continueStroke(event) {
                if (! this.drawing) return;
                event.preventDefault?.();
                this.currentStroke.push(this.getPercentPoint(event));
                this.redraw();
            },
            endStroke() {
                if (! this.drawing) return;
                this.drawing = false;
                if (this.currentStroke && this.currentStroke.length > 1) {
                    const s = this.ensureState();
                    this.state = { ...s, strokes: [...s.strokes, { points: this.currentStroke, color: this.penColor }] };
                }
                this.currentStroke = null;
                this.$nextTick(() => this.redraw());
            },
            undoStroke() {
                if (this.isDisabled) return;
                const s = this.ensureState();
                this.state = { ...s, strokes: s.strokes.slice(0, -1) };
            },
            clearStrokes() {
                if (this.isDisabled) return;
                this.state = { ...this.ensureState(), strokes: [] };
            },
        }"
        x-on:mouseup.window="stopPaint(); endStroke()"
        x-on:touchend.window="stopPaint(); endStroke()"
        x-on:mousemove.window="continueStroke($event)"
        x-on:touchmove.window="continueStroke($event)"
        class="tooth-chart"
        :class="{ 'is-disabled': isDisabled }"
    >
        <div class="tooth-chart-modes">
            <button type="button" x-on:click="mode = 'stamp'" :class="{ 'is-active': mode === 'stamp' }" class="tc-mode-btn">Stamp Codes</button>
            <button type="button" x-on:click="mode = 'draw'" :class="{ 'is-active': mode === 'draw' }" class="tc-mode-btn">Draw Lines</button>
        </div>

        <div class="tooth-chart-palette-groups" x-show="mode === 'stamp'">
            <template x-for="group in conditionGroups" :key="group">
                <div class="tooth-chart-palette-group">
                    <div class="tooth-chart-group-label" x-text="group"></div>
                    <div class="tooth-chart-palette">
                        <template x-for="[key, condition] in conditionsIn(group)" :key="key">
                            <button
                                type="button"
                                x-on:click="selected = key"
                                :class="{ 'is-active': selected === key }"
                                class="tooth-chart-swatch"
                            >
                                <span class="tooth-chart-dot" :style="{ backgroundColor: condition.color }" x-text="key"></span>
                                <span x-text="condition.label"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </template>

            <div class="tooth-chart-palette-group">
                <div class="tooth-chart-palette">
                    <button
                        type="button"
                        x-on:click="selected = 'DELETE'"
                        :class="{ 'is-active': selected === 'DELETE' }"
                        class="tooth-chart-swatch tooth-chart-swatch-delete"
                    >
                        <span class="tooth-chart-dot tooth-chart-dot-delete">&times;</span>
                        <span>Delete</span>
                    </button>

                    <button type="button" x-on:click="clearTeeth()" class="tooth-chart-clear">
                        Clear Teeth
                    </button>
                </div>
            </div>
        </div>

        <div class="tooth-chart-palette" x-show="mode === 'draw'" x-cloak>
            <template x-for="color in ['#111827', '#ef4444', '#2563eb']" :key="color">
                <button
                    type="button"
                    x-on:click="penColor = color"
                    :class="{ 'is-active': penColor === color }"
                    class="tooth-chart-swatch"
                >
                    <span class="tooth-chart-dot" :style="{ backgroundColor: color }"></span>
                </button>
            </template>

            <button type="button" x-on:click="undoStroke()" class="tooth-chart-clear">Undo Last Line</button>
            <button type="button" x-on:click="clearStrokes()" class="tooth-chart-clear">Clear Lines</button>
        </div>

        <p class="tooth-chart-hint" x-show="mode === 'stamp'">
            Pick a condition above, then click or drag across teeth to paint them &mdash; no need to click every tooth one by one. Click a painted tooth again to reset it to healthy, or pick "Delete" to erase teeth by clicking or dragging over them.
        </p>
        <p class="tooth-chart-hint" x-show="mode === 'draw'" x-cloak>
            Click (or tap) and drag anywhere on the chart to free-hand draw a line, just like marking the paper chart.
        </p>

        <div class="tc-canvas-wrap" x-init="initCanvas()">
            {{-- Upper arch --}}
            <div class="tc-row tc-row-primary-top">
                <div class="tc-quad">
                    @foreach ($permQuads['ur'] as $n)
                        @if ($primary = $primaryFor($n))
                            <button type="button" data-tooth="{{ $primary }}" x-on:mousedown.prevent="startPaint({{ $primary }})" x-on:mouseenter="dragTooth({{ $primary }})" x-on:touchstart.prevent="startPaint({{ $primary }})" :style="{ backgroundColor: colorFor({{ $primary }}) }" :title="labelFor({{ $primary }})" class="tooth-chart-tooth tc-tooth-primary">
                                <span class="tc-num">{{ $primary }}</span>
                                <span class="tc-code" x-text="codeFor({{ $primary }})"></span>
                            </button>
                        @else
                            <div class="tc-empty"></div>
                        @endif
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ul'] as $n)
                        @if ($primary = $primaryFor($n))
                            <button type="button" data-tooth="{{ $primary }}" x-on:mousedown.prevent="startPaint({{ $primary }})" x-on:mouseenter="dragTooth({{ $primary }})" x-on:touchstart.prevent="startPaint({{ $primary }})" :style="{ backgroundColor: colorFor({{ $primary }}) }" :title="labelFor({{ $primary }})" class="tooth-chart-tooth tc-tooth-primary">
                                <span class="tc-num">{{ $primary }}</span>
                                <span class="tc-code" x-text="codeFor({{ $primary }})"></span>
                            </button>
                        @else
                            <div class="tc-empty"></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="tc-row">
                <div class="tc-quad">
                    @foreach ($permQuads['ur'] as $n)
                        <button type="button" data-tooth="{{ $n }}" x-on:mousedown.prevent="startPaint({{ $n }})" x-on:mouseenter="dragTooth({{ $n }})" x-on:touchstart.prevent="startPaint({{ $n }})" :style="{ backgroundColor: colorFor({{ $n }}) }" :title="labelFor({{ $n }})" class="tooth-chart-tooth">
                            <span class="tc-num">{{ $n }}</span>
                            <span class="tc-code" x-text="codeFor({{ $n }})"></span>
                        </button>
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ul'] as $n)
                        <button type="button" data-tooth="{{ $n }}" x-on:mousedown.prevent="startPaint({{ $n }})" x-on:mouseenter="dragTooth({{ $n }})" x-on:touchstart.prevent="startPaint({{ $n }})" :style="{ backgroundColor: colorFor({{ $n }}) }" :title="labelFor({{ $n }})" class="tooth-chart-tooth">
                            <span class="tc-num">{{ $n }}</span>
                            <span class="tc-code" x-text="codeFor({{ $n }})"></span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="tc-arch-divider"></div>

            {{-- Lower arch --}}
            <div class="tc-row">
                <div class="tc-quad">
                    @foreach ($permQuads['lr'] as $n)
                        <button type="button" data-tooth="{{ $n }}" x-on:mousedown.prevent="startPaint({{ $n }})" x-on:mouseenter="dragTooth({{ $n }})" x-on:touchstart.prevent="startPaint({{ $n }})" :style="{ backgroundColor: colorFor({{ $n }}) }" :title="labelFor({{ $n }})" class="tooth-chart-tooth">
                            <span class="tc-num">{{ $n }}</span>
                            <span class="tc-code" x-text="codeFor({{ $n }})"></span>
                        </button>
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ll'] as $n)
                        <button type="button" data-tooth="{{ $n }}" x-on:mousedown.prevent="startPaint({{ $n }})" x-on:mouseenter="dragTooth({{ $n }})" x-on:touchstart.prevent="startPaint({{ $n }})" :style="{ backgroundColor: colorFor({{ $n }}) }" :title="labelFor({{ $n }})" class="tooth-chart-tooth">
                            <span class="tc-num">{{ $n }}</span>
                            <span class="tc-code" x-text="codeFor({{ $n }})"></span>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="tc-row tc-row-primary-bottom">
                <div class="tc-quad">
                    @foreach ($permQuads['lr'] as $n)
                        @if ($primary = $primaryFor($n))
                            <button type="button" data-tooth="{{ $primary }}" x-on:mousedown.prevent="startPaint({{ $primary }})" x-on:mouseenter="dragTooth({{ $primary }})" x-on:touchstart.prevent="startPaint({{ $primary }})" :style="{ backgroundColor: colorFor({{ $primary }}) }" :title="labelFor({{ $primary }})" class="tooth-chart-tooth tc-tooth-primary">
                                <span class="tc-num">{{ $primary }}</span>
                                <span class="tc-code" x-text="codeFor({{ $primary }})"></span>
                            </button>
                        @else
                            <div class="tc-empty"></div>
                        @endif
                    @endforeach
                </div>
                <div class="tc-mid"></div>
                <div class="tc-quad">
                    @foreach ($permQuads['ll'] as $n)
                        @if ($primary = $primaryFor($n))
                            <button type="button" data-tooth="{{ $primary }}" x-on:mousedown.prevent="startPaint({{ $primary }})" x-on:mouseenter="dragTooth({{ $primary }})" x-on:touchstart.prevent="startPaint({{ $primary }})" :style="{ backgroundColor: colorFor({{ $primary }}) }" :title="labelFor({{ $primary }})" class="tooth-chart-tooth tc-tooth-primary">
                                <span class="tc-num">{{ $primary }}</span>
                                <span class="tc-code" x-text="codeFor({{ $primary }})"></span>
                            </button>
                        @else
                            <div class="tc-empty"></div>
                        @endif
                    @endforeach
                </div>
            </div>

            <canvas
                x-ref="canvas"
                class="tc-draw-layer"
                :style="{ pointerEvents: mode === 'draw' ? 'auto' : 'none' }"
                x-on:mousedown="startStroke($event)"
                x-on:touchstart.prevent="startStroke($event)"
            ></canvas>
        </div>

        <div class="tooth-chart-legend-note">Outer rows: permanent teeth (11&ndash;48). Inner rows: temporary/primary teeth (51&ndash;85), shown only where a primary tooth exists.</div>
    </div>

    <style>
        .tooth-chart { display: flex; flex-direction: column; gap: 1.25rem; user-select: none; }
        .tooth-chart.is-disabled { opacity: 0.6; pointer-events: none; }

        .tooth-chart-modes { display: flex; gap: 0.5rem; align-self: center; margin-bottom: 0.25rem; }
        .tc-mode-btn {
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            border: 1px solid rgba(0, 0, 0, 0.15);
            background: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            color: rgb(107 114 128);
            cursor: pointer;
        }
        .dark .tc-mode-btn { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.15); color: rgb(156 163 175); }
        .tc-mode-btn.is-active { background: rgb(8 145 178); border-color: rgb(8 145 178); color: #fff; }

        .tooth-chart-palette-groups { display: flex; flex-direction: column; gap: 1rem; }
        .tooth-chart-palette-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .tooth-chart-group-label {
            font-size: 0.688rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: rgb(107 114 128);
        }
        .dark .tooth-chart-group-label { color: rgb(156 163 175); }

        .tooth-chart-palette { display: flex; flex-wrap: wrap; align-items: center; gap: 0.625rem; row-gap: 0.625rem; }
        .tooth-chart-swatch {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: #fff;
            font-size: 0.75rem;
            font-weight: 500;
            color: rgb(9 9 11);
            cursor: pointer;
        }
        .dark .tooth-chart-swatch { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.15); color: rgb(250 250 250); }
        .tooth-chart-swatch.is-active { border-color: rgb(8 145 178); box-shadow: 0 0 0 1px rgb(8 145 178); }
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

        .tooth-chart-swatch-delete.is-active { border-color: rgb(220 38 38); box-shadow: 0 0 0 1px rgb(220 38 38); }
        .tooth-chart-dot-delete { background: rgb(107 114 128); color: #fff; font-size: 0.75rem; }

        .tooth-chart-clear {
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            border: 1px dashed rgba(0, 0, 0, 0.2);
            background: transparent;
            font-size: 0.75rem;
            font-weight: 500;
            color: rgb(107 114 128);
            cursor: pointer;
        }
        .dark .tooth-chart-clear { border-color: rgba(255, 255, 255, 0.2); color: rgb(156 163 175); }

        .tooth-chart-hint, .tooth-chart-legend-note { font-size: 0.75rem; color: rgb(107 114 128); margin: 0; text-align: center; }
        .dark .tooth-chart-hint, .dark .tooth-chart-legend-note { color: rgb(156 163 175); }

        .tc-canvas-wrap { position: relative; width: 100%; margin: 0 auto; padding: 1.5rem 0.5rem; box-sizing: border-box; }
        .tc-draw-layer { position: absolute; inset: 0; width: 100%; height: 100%; touch-action: none; cursor: crosshair; }

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
            cursor: pointer;
            padding: 2px;
            line-height: 1.1;
        }
        .dark .tooth-chart-tooth { background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.2); }
        .tooth-chart-tooth:hover { border-color: rgb(8 145 178); }
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
