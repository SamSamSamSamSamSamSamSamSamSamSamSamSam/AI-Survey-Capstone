{{-- resources/views/admin/surveys/questions/_form.blade.php --}}

{{-- Question Text Field --}}
<div class="mb-4">
    <label class="form-label" for="question_text">
        Question Text <span class="text-danger">*</span>
    </label>
    <textarea name="question_text" id="question_text" rows="3"
              class="form-control @error('question_text') is-invalid @enderror"
              placeholder="e.g. The instructor explains lessons clearly and effectively.">{{ old('question_text', $question->question_text ?? '') }}</textarea>
    @error('question_text')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Question Type and Category --}}
<div class="mb-4" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
    <div>
        <label class="form-label" for="question_type">
            Question Type <span class="text-danger">*</span>
        </label>
        <select name="question_type" id="question_type" class="form-control @error('question_type') is-invalid @enderror" onchange="toggleScaleField(this.value)">
            <option value="rating" @selected(old('question_type', $question->question_type ?? 'rating') === 'rating')>Likert Scale (Rating)</option>
            <option value="text" @selected(old('question_type', $question->question_type ?? '') === 'text')>Open-ended (Text)</option>
        </select>
        @error('question_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div>
        <label class="form-label" for="category_id">Category</label>
        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror">
            <option value="">— No Category —</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $question->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
        @error('category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

{{-- Scale Selection (Shown only for rating questions) --}}
<div class="mb-4" id="scale-field">
    <label class="form-label" for="scale_id">
        Scale
    </label>
    <select name="scale_id" id="scale_id" class="form-control @error('scale_id') is-invalid @enderror">
        <option value="">— None —</option>
        @foreach ($scales as $scale)
            <option value="{{ $scale->id }}" @selected(old('scale_id', $question->scale_id ?? '') == $scale->id)>{{ $scale->name }} ({{ $scale->min_value }}–{{ $scale->max_value }})</option>
        @endforeach
    </select>
    <p class="form-text">Required for Likert scale questions.</p>
    @error('scale_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
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
