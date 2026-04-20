{{--
    resources/views/survey/_notification_toggles.blade.php

    Include at the BOTTOM of your survey.take form, just before the submit button:

        @include('survey._notification_toggles')
        <button type="submit" ...>Submit Survey</button>
--}}

<div class="d-flex align-items-center flex-wrap gap-3 mt-3">

    {{-- Email toggle --}}
    <div class="d-flex align-items-center gap-2">
        <input type="hidden" name="notify_email" value="0">
        <label class="nt-switch" for="notify_email">
            <input type="checkbox"
                   id="notify_email"
                   name="notify_email"
                   value="1"
                   form="surveyForm"
                   class="nt-switch__input">
            <span class="nt-switch__track"></span>
            <span class="nt-switch__thumb"></span>
        </label>
        <label for="notify_email" class="nt-label"
            data-bs-toggle="tooltip" 
            data-bs-placement="right" 
            title="Receive an email confirmation once your submission is successfully processed.">
            {{-- <i class="bi bi-envelope me-1"></i> --}}
            Email me
        </label>
    </div>

    {{-- Dashboard toggle --}}
    <div class="d-flex align-items-center gap-2">
        <input type="hidden" name="notify_dashboard" value="0">
        <label class="nt-switch" for="notify_dashboard">
            <input type="checkbox"
                   id="notify_dashboard"
                   name="notify_dashboard"
                   value="1"
                   form="surveyForm"
                   class="nt-switch__input">
            <span class="nt-switch__track"></span>
            <span class="nt-switch__thumb"></span>
        </label>
        <label for="notify_dashboard" class="nt-label"
            data-bs-toggle="tooltip" 
            data-bs-placement="right" 
            title="Receive an on-screen notification in your dashboard upon successful submission.">
            {{-- <i class="bi bi-bell me-1"></i> --}}
            Notify me
        </label>
    </div>

</div>

<style>
.nt-label {
    font-size: .8rem;
    font-weight: 500;
    color: #6b7280;
    cursor: pointer;
    margin: 0;
    user-select: none;
    transition: color .2s;
}

.nt-switch {
    position: relative;
    display: inline-block;
    width: 34px;
    height: 18px;
    cursor: pointer;
    margin: 0;
    flex-shrink: 0;
}

.nt-switch__input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}

.nt-switch__track {
    position: absolute;
    inset: 0;
    background: #e5e7eb;
    border-radius: 999px;
    transition: background .2s;
}

.nt-switch__thumb {
    position: absolute;
    width: 12px;
    height: 12px;
    left: 3px;
    top: 3px;
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 1px 2px rgba(0,0,0,.2);
    transition: transform .2s;
}

.nt-switch__input:checked ~ .nt-switch__track {
    background: var(--bs-primary, #0d6efd);
}

.nt-switch__input:checked ~ .nt-switch__thumb {
    transform: translateX(16px);
}
</style>

<script>
(function () {
    document.querySelectorAll('.nt-switch__input').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const label = this.closest('.d-flex').querySelector('.nt-label');
            if (label) label.style.color = this.checked ? 'var(--bs-primary, #0d6efd)' : '#6b7280';
        });
    });
})();

document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>