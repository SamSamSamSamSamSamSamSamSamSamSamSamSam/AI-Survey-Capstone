{{-- resources/views/admin/semester-setup/_upload_field.blade.php --}}
{{-- Variables: $stepId (int), $label (string e.g. 'students.csv') --}}

<div class="upload-area">
    <div class="upload-box" id="box-{{ $stepId }}"
         onclick="document.getElementById('file-{{ $stepId }}').click()"
         ondragover="event.preventDefault(); this.style.borderColor='#6366f1';"
         ondragleave="this.style.borderColor='';"
         ondrop="handleDrop(event, {{ $stepId }})">
        <div class="icon">📄</div>
        <p>Click or drag &amp; drop <strong>{{ $label }}</strong></p>
        <p style="font-size:.78rem;margin-top:.25rem;">CSV files only · Max 10 MB</p>
        <div class="file-name" id="name-{{ $stepId }}">No file selected</div>
    </div>
    <input type="file"
           id="file-{{ $stepId }}"
           name="csv_file"
           accept=".csv,text/csv,text/plain"
           style="display:none;"
           onchange="syncDrop({{ $stepId }}, this)">
</div>

<script>
function handleDrop(event, stepId) {
    event.preventDefault();
    const files = event.dataTransfer.files;
    if (files.length) {
        const input = document.getElementById('file-' + stepId);
        // DataTransfer lets us set files on the hidden input
        const dt = new DataTransfer();
        dt.items.add(files[0]);
        input.files = dt.files;
        input.dispatchEvent(new Event('change'));
    }
    document.getElementById('box-' + stepId).style.borderColor = '';
}

function syncDrop(stepId, input) {
    // Trigger the global change listener already attached via querySelectorAll
    // (already handled in wizard.blade.php's DOMContentLoaded block)
}
</script>
