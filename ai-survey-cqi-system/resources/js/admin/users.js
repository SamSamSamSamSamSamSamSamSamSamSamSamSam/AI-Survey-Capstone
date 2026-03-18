// =============================================================================
// users.js
// Users management — live search, role filter, delete modal
// =============================================================================

document.addEventListener('DOMContentLoaded', () => {

    // -------------------------------------------------------------------------
    // Element refs
    // -------------------------------------------------------------------------
    const searchInput  = document.getElementById('userSearch');
    const searchClear  = document.getElementById('searchClear');
    const roleFilter   = document.getElementById('roleFilter');
    const resultsCount = document.getElementById('resultsCount');
    const noResults    = document.getElementById('noResults');
    const rows         = document.querySelectorAll('.user-row');

    // -------------------------------------------------------------------------
    // Live filter — runs on search input or role select change
    // -------------------------------------------------------------------------
    const applyFilters = () => {
        const query      = searchInput.value.trim().toLowerCase();
        const role       = roleFilter.value;

        let visibleCount = 0;

        rows.forEach(row => {
            const name   = row.dataset.name  ?? '';
            const email  = row.dataset.email ?? '';
            const roles  = JSON.parse(row.dataset.roles ?? '[]');

            const matchesSearch = !query || name.includes(query) || email.includes(query);
            const matchesRole   = role === 'all' || roles.includes(role);

            if (matchesSearch && matchesRole) {
                row.classList.remove('is-hidden');
                visibleCount++;
            } else {
                row.classList.add('is-hidden');
            }
        });

        // Update count label
        const label = visibleCount === 1 ? 'user' : 'users';
        resultsCount.textContent = `${visibleCount} ${label}`;

        // Toggle no-results empty state
        noResults?.classList.toggle('d-none', visibleCount > 0);
    };

    // -------------------------------------------------------------------------
    // Search input — live filter + clear button
    // -------------------------------------------------------------------------
    searchInput?.addEventListener('input', () => {
        searchClear?.classList.toggle('d-none', searchInput.value === '');
        applyFilters();
    });

    searchClear?.addEventListener('click', () => {
        searchInput.value = '';
        searchClear.classList.add('d-none');
        searchInput.focus();
        applyFilters();
    });

    // -------------------------------------------------------------------------
    // Role select filter
    // -------------------------------------------------------------------------
    roleFilter?.addEventListener('change', applyFilters);

    // -------------------------------------------------------------------------
    // Delete modal — wire up each delete button
    // -------------------------------------------------------------------------
    const deleteModal    = document.getElementById('deleteModal');
    const deleteForm     = document.getElementById('deleteForm');
    const deleteUserName = document.getElementById('deleteUserName');

    if (deleteModal && deleteForm) {
        const bsModal = new bootstrap.Modal(deleteModal);

        document.querySelectorAll('.users-delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                // Populate modal with the target user's info
                deleteUserName.textContent  = btn.dataset.userName;
                deleteForm.action           = btn.dataset.deleteUrl;

                bsModal.show();
            });
        });
    }

});