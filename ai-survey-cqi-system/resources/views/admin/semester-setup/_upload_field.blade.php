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

        {{-- Input MUST be inside .upload-box so closest('.upload-box') works in the wizard JS --}}
        <input type="file"
               id="file-{{ $stepId }}"
               name="csv_file"
               accept=".csv,text/csv,text/plain"
               style="display:none;">
    </div>
</div>