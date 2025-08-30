@extends('layouts.default')

@section('content')
 <div class="card profile-card p-3" style="width: 300px;">
    <div class="d-flex align-items-center mb-3">
      <img src="https://i.pravatar.cc/100?img=12" alt="Avatar" class="me-3">
      <div>
        <h5 class="mb-0 fw-bold">Admin Name</h5>
        <small class="text-muted">Coordinator</small>
      </div>
    </div>

    <hr>

    <p class="mb-4">DCISM Coordinator</p>

    <button class="evaluate-btn">Evaluate</button>
  </div>
@endsection
