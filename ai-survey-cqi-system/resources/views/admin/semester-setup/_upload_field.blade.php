{{-- ============================================================
     admin/semester-setup/_upload_field.blade.php
     Variables: $stepId (int), $label (string e.g. 'students.csv')
     ============================================================ --}}

<label class="upload-zone upload-zone--ajax" id="uploadZone-{{ $stepId }}"
       for="file-{{ $stepId }}">

    <div class="upload-zone__state upload-zone__state--idle" id="zoneIdle-{{ $stepId }}">
        <i class="bi bi-cloud-upload upload-zone__icon"></i>
        <p class="upload-zone__text">
            Click or drag &amp; drop <strong>{{ $label }}</strong>
        </p>
        <p class="upload-zone__hint">CSV files only · Max 10 MB</p>
    </div>

    <div class="upload-zone__state upload-zone__state--selected d-none" id="zoneSelected-{{ $stepId }}">
        <i class="bi bi-file-earmark-check upload-zone__icon upload-zone__icon--selected"></i>
        <p class="upload-zone__filename" id="zoneName-{{ $stepId }}">—</p>
        <p class="upload-zone__hint">File ready — validating…</p>
    </div>

    <input type="file"
           id="file-{{ $stepId }}"
           name="csv_file"
           accept=".csv,text/csv,text/plain"
           class="upload-zone__input">
</label>