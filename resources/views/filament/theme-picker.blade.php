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
        },

        init() {
            this.applyVars('primary', this.colors[this.currentColor].vars);
            this.setBg(this.currentBg);
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
        style="position:absolute; top:calc(100% + 8px); right:0; z-index:9999; background:white; border:1px solid #e5e7eb; border-radius:14px; padding:16px; box-shadow:0 10px 30px rgba(0,0,0,0.13); min-width:240px;"
        x-cloak
    >
        {{-- Primary Color --}}
        <p style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 10px;">Primary Color</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:5px; margin-bottom:16px;">
            <template x-for="(c, name) in colors" :key="name">
                <button
                    type="button"
                    x-on:click="setColor(name)"
                    :style="`
                        display:flex; align-items:center; gap:8px;
                        padding:7px 10px; border-radius:8px; cursor:pointer;
                        border:2px solid ${currentColor === name ? c.swatch : 'transparent'};
                        background:${currentColor === name ? 'rgba(0,0,0,0.04)' : 'transparent'};
                        font-size:13px; font-weight:500; color:inherit;
                    `"
                >
                    <span :style="`width:14px; height:14px; border-radius:50%; background:${c.swatch}; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.2);`"></span>
                    <span x-text="c.label"></span>
                </button>
            </template>
        </div>

        <hr style="border:none; border-top:1px solid #f3f4f6; margin:0 0 14px;">

        {{-- Background --}}
        <p style="font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; color:#6b7280; margin:0 0 10px;">Background</p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:5px;">
            <template x-for="(bg, name) in backgrounds" :key="name">
                <button
                    type="button"
                    x-on:click="setBg(name)"
                    :style="`
                        display:flex; align-items:center; gap:8px;
                        padding:7px 10px; border-radius:8px; cursor:pointer;
                        border:2px solid ${currentBg === name ? bg.swatch : 'transparent'};
                        background:${currentBg === name ? 'rgba(0,0,0,0.04)' : 'transparent'};
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
