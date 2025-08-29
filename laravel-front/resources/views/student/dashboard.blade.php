@extends('layouts.default')

@section('title', 'Admin Dashboard')

@section('content')
	<div class="container-fluid">
		<div class="row">
			
			<div class="col-md-8">
				<h3>Welcome, {{ auth()->user()->name ?? 'Student' }}</h3>
				<p class="text-muted">Access your surveys and see recent results.</p>

				<div class="card mb-3">
					<div class="card-body">
						<h5 class="card-title">My Surveys</h5>
						<ul class="list-group">
							@foreach($surveys as $s)
								<li class="list-group-item d-flex justify-content-between">
									<div>{{ $s['course'] }}</div>
									<div class="text-muted">{{ $s['status'] }} {{ $s['score'] ? '- ' . $s['score'] : '' }}</div>
								</li>
							@endforeach
						</ul>
					</div>
				</div>

				<div class="card">
					<div class="card-body">
						<h5 class="card-title">Recent Results</h5>
						@foreach($recentResults as $r)
							<div class="mb-2">
								<strong>{{ $r['course'] }}</strong> — Score: {{ $r['score'] }} <div class="small text-muted">Instructor: {{ $r['instructor'] }}</div>
							</div>
						@endforeach
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		window.__studentData = {
			surveys: {!! json_encode($surveys) !!},
			recentResults: {!! json_encode($recentResults) !!}
		};
	</script>
@endsection
