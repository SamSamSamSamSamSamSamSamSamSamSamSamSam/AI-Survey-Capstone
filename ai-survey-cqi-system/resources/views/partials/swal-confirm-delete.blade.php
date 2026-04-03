<script>
    function confirmDelete(itemId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('delete-form-' + itemId);
                if (form) {
                    form.submit();
                } else {
                    console.error('Form not found for item ID:', itemId);
                }
            }
        });
    }
</script>