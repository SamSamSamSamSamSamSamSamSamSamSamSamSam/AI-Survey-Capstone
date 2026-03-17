@extends('layouts.default')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 text-center">
            <h3 class="mb-4">Upload Your Study Load</h3>

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('onboarding.process') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="study_load" class="form-control mb-3" accept=".jpg,.jpeg,.png,.pdf" required>
                <button type="submit" class="btn btn-primary w-100">Scan and Extract</button>
            </form>
        </div>
    </div>
</div>
@endsection
