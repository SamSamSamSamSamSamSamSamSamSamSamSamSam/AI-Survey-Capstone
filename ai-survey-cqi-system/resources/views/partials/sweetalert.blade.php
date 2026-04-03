@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops... Something went wrong!',
            html: '{!! implode("", $errors->all("<li>:message</li>")) !!}', // This compiles the error messages into list items
        });
    </script>
@endif

@if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '{{ session('success') }}',
        });
    </script>
@endif

@if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '{{ session('error') }}',
        });
    </script>
@endif