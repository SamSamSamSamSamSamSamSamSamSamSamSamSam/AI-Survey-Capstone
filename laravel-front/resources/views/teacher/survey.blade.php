@extends('layouts.default')

@section('content')
<div class="container mt-4">
    <h1 class="h4 mb-4">Surveys</h1>

    <div class="row">
        @foreach($survey as $s)
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">{{ $s->name }}</h5>
                        <p class="card-text">
                            <strong>Instructor:</strong> {{ $s->title}} <br>
                        </p>
                        <a href="#" class="btn btn-primary btn-sm">Evaluate</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
