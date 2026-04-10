{{-- resources/views/admin/surveys/questions/_form.blade.php --}}

<div class="form-group">
    <label class="form-label">Question Text <span style="color:#dc2626">*</span></label>
    <textarea name="question_text" rows="3"
              class="form-control {{ $errors->has('question_text') ? 'is-invalid' : '' }}"
              placeholder="e.g. The instructor explains lessons clearly and effectively.">{{ old('question_text', $question->question_text ?? '') }}</textarea>
    @error('question_text') <p class="invalid-feedback">{{ $message }}</p> @enderror
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">

    <div class="form-group">
        <label class="form-label">Type <span style="color:#dc2626">*</span></label>
        <select name="question_type" id="q-type" class="form-control" onchange="toggleScaleField(this.value)">
            <option value="rating" @selected(old('question_type', $question->question_type ?? 'rating') === 'rating')>Likert Scale (Rating)</option>
            <option value="text"   @selected(old('question_type', $question->question_type ?? '') === 'text')>Open-ended (Text)</option>
        </select>
        @error('question_type') <p class="invalid-feedback">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
        <label class="form-label">Category</label>
        <select name="category_id" class="form-control">
            <option value="">— No Category —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}"
                        @selected(old('category_id', $question->category_id ?? '') == $cat->id)>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group" id="scale-field">
        <label class="form-label">Scale</label>
        <select name="scale_id" class="form-control {{ $errors->has('scale_id') ? 'is-invalid' : '' }}">
            <option value="">— None —</option>
            @foreach ($scales as $scale)
                <option value="{{ $scale->id }}"
                        @selected(old('scale_id', $question->scale_id ?? '') == $scale->id)>
                    {{ $scale->name }} ({{ $scale->min_value }}–{{ $scale->max_value }})
                </option>
            @endforeach
        </select>
        <p class="form-text">Required for Likert scale questions.</p>
        @error('scale_id') <p class="invalid-feedback">{{ $message }}</p> @enderror
    </div>

</div>

<script>
function toggleScaleField(type) {
    const field = document.getElementById('scale-field');
    if (field) field.style.display = type === 'rating' ? '' : 'none';
}
// Run on page load
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('q-type');
    if (sel) toggleScaleField(sel.value);
});
</script>
