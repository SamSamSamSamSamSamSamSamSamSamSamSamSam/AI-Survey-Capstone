// =============================================================================
// department.js
// Faculty directory — live search + course filter
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------------------------------------------------
    // Element refs
    // -------------------------------------------------------------------------
    const searchInput   = document.getElementById('facultySearch');
    const searchClear   = document.getElementById('searchClear');
    const subjectFilter = document.getElementById('subjectFilter');
    const resultsCount  = document.getElementById('resultsCount');
    const noResults     = document.getElementById('noResults');
    const cards         = document.querySelectorAll('.faculty-card');

    // -------------------------------------------------------------------------
    // Core filter function
    // Runs on every keystroke or select change.
    // -------------------------------------------------------------------------
    const applyFilters = () => {
        const query     = searchInput.value.trim().toLowerCase();
        const subjectId = subjectFilter.value;

        let visibleCount = 0;

        cards.forEach(card => {
            const name     = card.dataset.name  ?? '';
            const email    = card.dataset.email ?? '';
            const subjects = card.dataset.subjects ? card.dataset.subjects.split(',') : [];

            const matchesSearch  = !query || name.includes(query) || email.includes(query);
            const matchesSubject = !subjectId || subjects.includes(subjectId);

            if (matchesSearch && matchesSubject) {
                card.classList.remove('is-hidden');
                visibleCount++;
            } else {
                card.classList.add('is-hidden');
            }
        });

        // Update results count
        const label = visibleCount === 1 ? 'member' : 'members';
        resultsCount.textContent = `${visibleCount} ${label}`;

        // Toggle "no results" empty state
        noResults.classList.toggle('d-none', visibleCount > 0);
    };

    // -------------------------------------------------------------------------
    // Search input — live filter with clear button toggle
    // -------------------------------------------------------------------------
    searchInput?.addEventListener('input', () => {
        // Show or hide the × clear button
        searchClear.classList.toggle('d-none', searchInput.value === '');
        applyFilters();
    });

    // -------------------------------------------------------------------------
    // Clear button — resets the search field
    // -------------------------------------------------------------------------
    searchClear?.addEventListener('click', () => {
        searchInput.value = '';
        searchClear.classList.add('d-none');
        searchInput.focus();
        applyFilters();
    });

    // -------------------------------------------------------------------------
    // Subject / course select filter
    // -------------------------------------------------------------------------
    subjectFilter?.addEventListener('change', applyFilters);

});