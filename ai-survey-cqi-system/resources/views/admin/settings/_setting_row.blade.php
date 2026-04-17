{{-- ============================================================
     admin/settings/_setting_row.blade.php
     Variable: $s (Setting model instance)
     ============================================================ --}}

<div class="setting-row">

    {{-- Label + description --}}
    <div class="setting-row__meta">
        <label class="setting-row__label" for="setting_{{ $s->key }}">
            {{ $s->label }}
            @if ($s->is_readonly)
                <span class="setting-badge setting-badge--readonly">Read-only</span>
            @endif
            @if ($s->is_sensitive)
                <span class="setting-badge setting-badge--sensitive">Sensitive</span>
            @endif
        </label>
        @if ($s->description)
            <p class="setting-row__desc">{{ $s->description }}</p>
        @endif
    </div>

    {{-- Input --}}
    <div class="setting-row__input">

        @if ($s->is_readonly)
            <input type="text"
                   id="setting_{{ $s->key }}"
                   class="form-control"
                   value="{{ $s->value ?? config(str_replace('.', '.', $s->key), '—') }}"
                   readonly>

        @elseif ($s->type === 'boolean')
            <input type="hidden" name="{{ $s->key }}" value="0">
            <div class="setting-toggle-wrap">
                <label class="setting-toggle">
                    <input type="checkbox"
                           id="setting_{{ $s->key }}"
                           name="{{ $s->key }}"
                           value="1"
                           class="setting-toggle__input"
                           {{ $s->cast_value ? 'checked' : '' }}>
                    <span class="setting-toggle__slider"></span>
                </label>
                <span class="setting-toggle__label" id="label_{{ $s->key }}">
                    {{ $s->cast_value ? 'Enabled' : 'Disabled' }}
                </span>
            </div>
            <script>
            (function () {
                const cb  = document.getElementById('setting_{{ $s->key }}');
                const lbl = document.getElementById('label_{{ $s->key }}');
                if (cb && lbl) {
                    cb.addEventListener('change', function () {
                        lbl.textContent = this.checked ? 'Enabled' : 'Disabled';
                    });
                }
            })();
            </script>

        @elseif ($s->type === 'file')
            <div class="setting-file-wrap">
                @if ($s->value)
                    <div class="setting-file-preview">
                        @if (Str::endsWith($s->value, ['.png', '.jpg', '.jpeg', '.gif', '.webp']))
                            <img src="{{ Storage::url($s->value) }}" alt="{{ $s->label }}"
                                 class="setting-file-preview__img">
                        @else
                            <span class="text-muted-sm">
                                <i class="bi bi-file-earmark me-1"></i>{{ basename($s->value) }}
                            </span>
                        @endif
                    </div>
                @else
                    <span class="text-muted-sm">No file uploaded</span>
                @endif
                <label class="setting-file-label" for="setting_{{ $s->key }}">
                    <i class="bi bi-paperclip me-1"></i> Choose File
                    <input type="file"
                           id="setting_{{ $s->key }}"
                           name="{{ $s->key }}"
                           accept="image/*"
                           class="setting-file-input">
                </label>
            </div>

        @elseif ($s->key === 'app.primary_color')
            <div class="setting-color-wrap">
                <input type="color"
                       id="setting_{{ $s->key }}"
                       name="{{ $s->key }}"
                       value="{{ $s->value ?? '#3498db' }}"
                       class="setting-color-swatch">
                <input type="text"
                       id="color_text_{{ $s->key }}"
                       value="{{ $s->value ?? '#3498db' }}"
                       class="form-control setting-color-text"
                       placeholder="#3498db"
                       maxlength="7">
            </div>
            <script>
            (function () {
                const picker = document.getElementById('setting_{{ $s->key }}');
                const text   = document.getElementById('color_text_{{ $s->key }}');
                if (!picker || !text) return;
                picker.addEventListener('input', function () { text.value  = this.value; });
                text.addEventListener('input',   function () {
                    if (/^#[0-9a-fA-F]{6}$/.test(this.value)) picker.value = this.value;
                });
            })();
            </script>

        @elseif ($s->key === 'locale.timezone')
            @php
                $timezones = [
                    'Asia/Manila'        => 'Asia/Manila (PHT, UTC+8)',
                    'UTC'                => 'UTC',
                    'Asia/Singapore'     => 'Asia/Singapore (SGT, UTC+8)',
                    'Asia/Tokyo'         => 'Asia/Tokyo (JST, UTC+9)',
                    'Asia/Hong_Kong'     => 'Asia/Hong Kong (HKT, UTC+8)',
                    'Asia/Jakarta'       => 'Asia/Jakarta (WIB, UTC+7)',
                    'America/New_York'   => 'America/New York (EST/EDT)',
                    'America/Chicago'    => 'America/Chicago (CST/CDT)',
                    'America/Los_Angeles'=> 'America/Los Angeles (PST/PDT)',
                    'Europe/London'      => 'Europe/London (GMT/BST)',
                    'Europe/Paris'       => 'Europe/Paris (CET/CEST)',
                    'Australia/Sydney'   => 'Australia/Sydney (AEDT)',
                ];
            @endphp
            <select name="{{ $s->key }}" id="setting_{{ $s->key }}" class="form-select">
                @foreach ($timezones as $tz => $label)
                    <option value="{{ $tz }}" @selected($s->value === $tz)>{{ $label }}</option>
                @endforeach
            </select>

        @elseif ($s->key === 'ai.gemini_model')
            <select name="{{ $s->key }}" id="setting_{{ $s->key }}" class="form-select">
                <option value="gemini-1.5-flash" @selected($s->value === 'gemini-1.5-flash')>
                    gemini-1.5-flash — Fast, recommended
                </option>
                <option value="gemini-1.5-pro" @selected($s->value === 'gemini-1.5-pro')>
                    gemini-1.5-pro — More capable
                </option>
                <option value="gemini-2.0-flash" @selected($s->value === 'gemini-2.0-flash')>
                    gemini-2.0-flash — Latest
                </option>
            </select>

        @elseif ($s->key === 'maintenance.banner_type')
            <select name="{{ $s->key }}" id="setting_{{ $s->key }}" class="form-select setting-select--short">
                @foreach (['info' => 'Info (blue)', 'success' => 'Success (green)', 'warning' => 'Warning (yellow)', 'error' => 'Error (red)'] as $val => $lbl)
                    <option value="{{ $val }}" @selected($s->value === $val)>{{ $lbl }}</option>
                @endforeach
            </select>

        @elseif ($s->type === 'integer')
            <input type="number"
                   id="setting_{{ $s->key }}"
                   name="{{ $s->key }}"
                   value="{{ $s->value }}"
                   class="form-control setting-input--short"
                   min="0">

        @elseif ($s->is_sensitive)
            <input type="password"
                   id="setting_{{ $s->key }}"
                   name="{{ $s->key }}"
                   value="{{ $s->value }}"
                   class="form-control"
                   autocomplete="new-password"
                   placeholder="••••••••••••">

        @else
            <input type="text"
                   id="setting_{{ $s->key }}"
                   name="{{ $s->key }}"
                   value="{{ $s->value }}"
                   class="form-control">
        @endif

    </div>
</div>