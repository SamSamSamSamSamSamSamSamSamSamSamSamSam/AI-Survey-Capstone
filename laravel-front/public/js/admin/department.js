document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('facultySearch');
    const subjectFilter = document.getElementById('subjectFilter');
    const facultyCards = document.querySelectorAll('.faculty-card');
    
    function filterFaculty() {
        const searchTerm = searchInput.value.toLowerCase();
        const subjectId = subjectFilter.value;
        
        facultyCards.forEach(card => {
            const name = card.querySelector('.card-title').textContent.toLowerCase();
            const email = card.querySelector('.text-muted').textContent.toLowerCase();
            const subjects = card.getAttribute('data-subjects').split(',');
            
            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesSubject = subjectId === '' || subjects.includes(subjectId);
            
            if (matchesSearch && matchesSubject) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }
    
    searchInput.addEventListener('input', filterFaculty);
    subjectFilter.addEventListener('change', filterFaculty);
});