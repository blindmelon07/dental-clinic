<div
    x-data="{
        open: false,
        currentColor: localStorage.getItem('fi-color-theme') || 'cyan',
        currentBg:    localStorage.getItem('fi-bg-theme')    || 'default',

        colors: {
            cyan:    { label:'Cyan',    swatch:'oklch(0.715 0.143 215.221)', vars:{ 50:'oklch(0.984 0.019 200.873)',100:'oklch(0.956 0.045 203.388)',200:'oklch(0.917 0.08 205.041)',300:'oklch(0.865 0.127 207.078)',400:'oklch(0.789 0.154 211.53)',500:'oklch(0.715 0.143 215.221)',600:'oklch(0.609 0.126 221.723)',700:'oklch(0.52 0.105 223.128)',800:'oklch(0.45 0.085 224.283)',900:'oklch(0.398 0.07 227.392)',950:'oklch(0.302 0.056 229.695)' } },
            blue:    { label:'Blue',    swatch:'oklch(0.623 0.214 259.815)', vars:{ 50:'oklch(0.97 0.014 254.604)',100:'oklch(0.932 0.032 255.585)',200:'oklch(0.882 0.059 254.128)',300:'oklch(0.809 0.105 251.813)',400:'oklch(0.707 0.165 254.624)',500:'oklch(0.623 0.214 259.815)',600:'oklch(0.546 0.245 262.881)',700:'oklch(0.488 0.243 264.376)',800:'oklch(0.424 0.199 265.638)',900:'oklch(0.379 0.146 265.522)',950:'oklch(0.282 0.091 267.935)' } },
            violet:  { label:'Violet',  swatch:'oklch(0.606 0.25 292.717)',  vars:{ 50:'oklch(0.969 0.016 293.756)',100:'oklch(0.943 0.029 294.588)',200:'oklch(0.894 0.057 293.283)',300:'oklch(0.811 0.111 293.571)',400:'oklch(0.702 0.183 293.541)',500:'oklch(0.606 0.25 292.717)',600:'oklch(0.541 0.281 293.009)',700:'oklch(0.491 0.27 292.581)',800:'oklch(0.432 0.232 292.759)',900:'oklch(0.38 0.189 293.745)',950:'oklch(0.283 0.141 291.089)' } },
            rose:    { label:'Rose',    swatch:'oklch(0.645 0.246 16.439)',  vars:{ 50:'oklch(0.969 0.015 12.422)',100:'oklch(0.941 0.03 12.58)',200:'oklch(0.892 0.058 10.001)',300:'oklch(0.81 0.117 11.638)',400:'oklch(0.712 0.194 13.428)',500:'oklch(0.645 0.246 16.439)',600:'oklch(0.586 0.253 17.585)',700:'oklch(0.514 0.222 16.935)',800:'oklch(0.455 0.188 13.697)',900:'oklch(0.41 0.159 10.272)',950:'oklch(0.271 0.105 12.094)' } },
            amber:   { label:'Amber',   swatch:'oklch(0.769 0.188 70.08)',   vars:{ 50:'oklch(0.987 0.022 95.277)',100:'oklch(0.962 0.059 95.617)',200:'oklch(0.924 0.12 95.746)',300:'oklch(0.879 0.169 91.605)',400:'oklch(0.828 0.189 84.429)',500:'oklch(0.769 0.188 70.08)',600:'oklch(0.666 0.179 58.318)',700:'oklch(0.555 0.163 48.998)',800:'oklch(0.473 0.137 46.201)',900:'oklch(0.414 0.112 45.904)',950:'oklch(0.279 0.077 45.635)' } },
            emerald: { label:'Emerald', swatch:'oklch(0.696 0.17 162.48)',   vars:{ 50:'oklch(0.979 0.021 166.113)',100:'oklch(0.95 0.052 163.051)',200:'oklch(0.905 0.093 164.15)',300:'oklch(0.845 0.143 164.978)',400:'oklch(0.765 0.177 163.223)',500:'oklch(0.696 0.17 162.48)',600:'oklch(0.596 0.145 163.225)',700:'oklch(0.508 0.118 165.612)',800:'oklch(0.432 0.095 166.913)',900:'oklch(0.378 0.077 168.94)',950:'oklch(0.262 0.051 172.552)' } },
            gold:    { label:'Gold',    swatch:'oklch(0.72 0.11 82)',        vars:{ 50:'oklch(0.98 0.02 90)',100:'oklch(0.955 0.035 88)',200:'oklch(0.9 0.06 86)',300:'oklch(0.84 0.085 84)',400:'oklch(0.78 0.10 83)',500:'oklch(0.72 0.11 82)',600:'oklch(0.63 0.105 78)',700:'oklch(0.54 0.095 74)',800:'oklch(0.45 0.08 70)',900:'oklch(0.38 0.065 66)',950:'oklch(0.27 0.045 62)' } },
        },

        backgrounds: {
            default:  { label:'Default',  swatch:'#f1f5f9', css:'' },
            white:    { label:'White',     swatch:'#ffffff', css:'body{background:#ffffff!important;}' },
            warm:     { label:'Warm',      swatch:'#fdf8f0', css:'body{background:#fdf8f0!important;}' },
            sky:      { label:'Sky',       swatch:'#eff6ff', css:'body{background:#eff6ff!important;}' },
            mint:     { label:'Mint',      swatch:'#f0fdf4', css:'body{background:#f0fdf4!important;}' },
            lavender: { label:'Lavender',  swatch:'#f5f3ff', css:'body{background:#f5f3ff!important;}' },
            rose:     { label:'Rose',      swatch:'#fff1f2', css:'body{background:#fff1f2!important;}' },
            sand:     { label:'Sand',      swatch:'#fafaf5', css:'body{background:#fafaf5!important;}' },
            black:    { label:'Black',     swatch:'#000000', css:'body{background:#000000!important;}', dark:true },
            navy:     { label:'Dark Blue', swatch:'#0a1128', css:'body{background:#0a1128!important;}', dark:true },
            espresso: { label:'Espresso',  swatch:'#140f0a', css:'body{background:#140f0a!important;}', dark:true },
        },

        // Filament's own sidebar/topbar/main/card surfaces are painted from
        // the --gray-50..950 custom properties (same mechanism as --primary-*
        // above), not from `body`. Repaint them per dark background so the
        // whole panel — not just the page margins — takes on the chosen tint.
        grayPalettes: {
            default:  { 50:'oklch(0.984 0.003 247.858)',100:'oklch(0.968 0.007 247.896)',200:'oklch(0.929 0.013 255.508)',300:'oklch(0.869 0.022 252.894)',400:'oklch(0.704 0.04 256.788)',500:'oklch(0.554 0.046 257.417)',600:'oklch(0.446 0.043 257.281)',700:'oklch(0.372 0.044 257.287)',800:'oklch(0.279 0.041 260.031)',900:'oklch(0.208 0.042 265.755)',950:'oklch(0.129 0.042 264.695)' },
            black:    { 50:'oklch(0.985 0 0)',100:'oklch(0.96 0 0)',200:'oklch(0.90 0 0)',300:'oklch(0.82 0 0)',400:'oklch(0.64 0 0)',500:'oklch(0.50 0 0)',600:'oklch(0.40 0 0)',700:'oklch(0.30 0 0)',800:'oklch(0.20 0 0)',900:'oklch(0.14 0 0)',950:'oklch(0.07 0 0)' },
            navy:     { 50:'oklch(0.97 0.01 264)',100:'oklch(0.93 0.016 264)',200:'oklch(0.86 0.026 264)',300:'oklch(0.75 0.04 264)',400:'oklch(0.60 0.05 264)',500:'oklch(0.46 0.055 264)',600:'oklch(0.36 0.055 264)',700:'oklch(0.27 0.05 264)',800:'oklch(0.19 0.045 264)',900:'oklch(0.135 0.04 264)',950:'oklch(0.075 0.035 264)' },
            espresso: { 50:'oklch(0.97 0.015 60)',100:'oklch(0.93 0.022 58)',200:'oklch(0.86 0.032 55)',300:'oklch(0.75 0.045 52)',400:'oklch(0.60 0.05 48)',500:'oklch(0.46 0.055 45)',600:'oklch(0.36 0.05 42)',700:'oklch(0.27 0.045 40)',800:'oklch(0.19 0.038 38)',900:'oklch(0.13 0.032 36)',950:'oklch(0.07 0.026 34)' },
        },

        applyVars(prefix, vars) {
            let css = ':root{';
            for (const [shade, val] of Object.entries(vars)) {
                css += `--${prefix}-${shade}:${val};`;
            }
            css += '}';
            let el = document.getElementById(`fi-theme-${prefix}`);
            if (!el) {
                el = document.createElement('style');
                el.id = `fi-theme-${prefix}`;
                document.head.appendChild(el);
            }
            el.textContent = css;
        },

        setColor(name) {
            this.currentColor = name;
            localStorage.setItem('fi-color-theme', name);
            this.applyVars('primary', this.colors[name].vars);
        },

        setBg(name) {
            this.currentBg = name;
            localStorage.setItem('fi-bg-theme', name);
            let el = document.getElementById('fi-theme-bg');
            if (!el) {
                el = document.createElement('style');
                el.id = 'fi-theme-bg';
                document.head.appendChild(el);
            }
            el.textContent = this.backgrounds[name].css;

            // Dark backgrounds need Filament's own dark mode active too,
            // otherwise sidebar/headings/cards keep light-mode text colors
            // and become unreadable against a black/navy/espresso page.
            if (this.backgrounds[name].dark) {
                localStorage.setItem('theme', 'dark');
                document.documentElement.classList.add('dark');
            } else {
                localStorage.setItem('theme', 'light');
                document.documentElement.classList.remove('dark');
            }

            // Sidebar/topbar/main/cards read --gray-50..950, not `body`, so
            // repaint that scale too or they stay Filament's default slate.
            this.applyVars('gray', this.grayPalettes[name] ?? this.grayPalettes.default);
        },

        init() {
            this.applyVars('primary', this.colors[this.currentColor].vars);
            this.setBg(this.currentBg);
        },

        isDark() {
            return this.backgrounds[this.currentBg]?.dark === true;
        }
    }"
    x-on:click.outside="open = false"
    style="position:relative; display:flex; align-items:center;"
>
    {{-- Trigger button --}}
    <button
        type="button"
        x-on:click="open = !open"
        title="Appearance settings"
        style="display:flex; align-items:center; gap:6px; padding:6px 10px; border-radius:8px; border:1px solid rgba(128,128,128,0.2); background:transparent; cursor:pointer; font-size:13px; color:inherit;"
    >
        <span style="display:flex; gap:3px; align-items:center;">
            <span :style="`width:10px; height:10px; border-radius:50%; background:${colors[currentColor].swatch};`"></span>
            <span :style="`width:10px; height:10px; border-radius:50%; background:${backgrounds[currentBg].swatch};`"></span>
        </span>
        <span style="font-weight:500;">Theme</span>
        <svg style="width:12px;height:12px;opacity:0.5;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div
        x-show="open"
        x-transition
        :style="`
            position:absolute; top:calc(100% + 8px); right:0; z-index:9999; border-radius:14px; padding:16px;
            min-width:240px;
            background:var(--gray-${isDark() ? '900' : '50'});
            border:1px solid var(--gray-${isDark() ? '700' : '200'});
            color:var(--gray-${isDark() ? '100' : '900'});
            box-shadow:0 10px 30px rgba(0,0,0,${isDark() ? '0.5' : '0.13'});
        `"
        x-cloak
    >
        {{-- Primary Color --}}
        <p :style="`font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--gray-${isDark() ? '400' : '500'}); margin:0 0 10px;`">Primary Color</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:5px; margin-bottom:16px;">
            <template x-for="(c, name) in colors" :key="name">
                <button
                    type="button"
                    x-on:click="setColor(name)"
                    :style="`
                        display:flex; align-items:center; gap:8px;
                        padding:7px 10px; border-radius:8px; cursor:pointer;
                        border:2px solid ${currentColor === name ? c.swatch : 'transparent'};
                        background:${currentColor === name ? (isDark() ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.04)') : 'transparent'};
                        font-size:13px; font-weight:500; color:inherit;
                    `"
                >
                    <span :style="`width:14px; height:14px; border-radius:50%; background:${c.swatch}; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.2);`"></span>
                    <span x-text="c.label"></span>
                </button>
            </template>
        </div>

        <hr :style="`border:none; border-top:1px solid var(--gray-${isDark() ? '700' : '100'}); margin:0 0 14px;`">

        {{-- Background --}}
        <p :style="`font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:var(--gray-${isDark() ? '400' : '500'}); margin:0 0 10px;`">Background</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:5px;">
            <template x-for="(bg, name) in backgrounds" :key="name">
                <button
                    type="button"
                    x-on:click="setBg(name)"
                    :style="`
                        display:flex; align-items:center; gap:8px;
                        padding:7px 10px; border-radius:8px; cursor:pointer;
                        border:2px solid ${currentBg === name ? bg.swatch : 'transparent'};
                        background:${currentBg === name ? (isDark() ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.04)') : 'transparent'};
                        font-size:13px; font-weight:500; color:inherit;
                    `"
                >
                    <span :style="`width:14px; height:14px; border-radius:4px; background:${bg.swatch}; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.2);`"></span>
                    <span x-text="bg.label"></span>
                </button>
            </template>
        </div>
    </div>
</div>
