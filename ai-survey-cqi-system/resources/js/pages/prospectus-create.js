/**
 * js/pages/prospectus-create.js
 * Dynamically loads curricula when a program is selected on the prospectus create form.
 */

(function () {
  'use strict';

  const programSelect    = document.getElementById('program_id');
  const curriculumSelect = document.getElementById('curriculum_id');

  if (!programSelect || !curriculumSelect) return;

  // Preserve the value that may have been re-populated on validation failure
  const preselectedCurriculum = curriculumSelect.value;

  programSelect.addEventListener('change', function () {
    const programId = this.value;
    loadCurricula(programId, null);
  });

  // On page load, if a program is already selected (e.g. via ?program_id= query param
  // or after a failed form submission), reload the curricula list so the select is populated.
  if (programSelect.value) {
    loadCurricula(programSelect.value, preselectedCurriculum);
  }

  /**
   * Fetch curricula for a given program and populate the curriculum <select>.
   *
   * @param {string} programId
   * @param {string|null} selectValue - the option value to pre-select after loading
   */
  function loadCurricula(programId, selectValue) {
    if (!programId) {
      resetSelect('Select curriculum…');
      return;
    }

    setSelectLoading(true);

    fetch(`/admin/curricula/by-program/${encodeURIComponent(programId)}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((res) => {
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        return res.json();
      })
      .then((data) => {
        populateSelect(data, selectValue);
      })
      .catch(() => {
        resetSelect('Error loading curricula — please try again');
        curriculumSelect.classList.add('is-invalid');
      })
      .finally(() => {
        setSelectLoading(false);
      });
  }

  function setSelectLoading(loading) {
    curriculumSelect.disabled = loading;
    if (loading) {
      curriculumSelect.innerHTML = '<option value="">Loading…</option>';
    }
  }

  function resetSelect(placeholder) {
    curriculumSelect.disabled = false;
    curriculumSelect.classList.remove('is-invalid');
    curriculumSelect.innerHTML = `<option value="">${placeholder}</option>`;
  }

  function populateSelect(curricula, selectValue) {
    curriculumSelect.classList.remove('is-invalid');
    curriculumSelect.innerHTML = '<option value="">Select curriculum…</option>';

    if (!curricula.length) {
      curriculumSelect.innerHTML = '<option value="">No curricula found for this program</option>';
      return;
    }

    curricula.forEach((c) => {
      const opt        = document.createElement('option');
      opt.value        = c.id;
      opt.textContent  = c.display_label + (c.is_active ? ' (Active)' : '');
      opt.selected     = String(c.id) === String(selectValue);
      curriculumSelect.appendChild(opt);
    });
  }
})();