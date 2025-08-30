@extends('layouts.default')

@section('header')
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
	<style>
		/* teacher dashboard small styles */
		.teacher-welcome { margin-bottom: 1rem; }
		.card-compact { border-radius:.5rem; box-shadow:0 6px 18px rgba(0,0,0,0.03); }
	</style>
@endsection

@section('content')
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-8">
				<div class="teacher-welcome">
					<h3>Welcome, {{ auth()->user()->name ?? 'Teacher' }}</h3>
					<p class="text-muted">This is your faculty dashboard.</p>
				</div>

				<div class="card card-compact p-3 mb-3">
					<h5>My Classes</h5>
					<ul class="list-group list-group-flush">
						@foreach($classes as $c)
							<li class="list-group-item d-flex justify-content-between">
								<div>{{ $c['code'] }} — {{ $c['title'] }}</div>
								<div class="text-muted">{{ $c['students'] }} students</div>
							</li>
						@endforeach
					</ul>
				</div>

				<div class="card card-compact p-3">
					<h5>Feedback Summary</h5>
					<p class="mb-1">Average score: <strong id="teacher-avg-score">{{ $feedbackSummary['average_score'] ?? '--' }}</strong></p>
					<div>
						<h6 class="small text-muted">Recent Comments</h6>
						<ul class="list-group">
							@foreach($feedbackSummary['recent_comments'] as $cm)
								<li class="list-group-item">{{ $cm['author'] }}: {{ $cm['text'] }}</li>
							@endforeach
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		window.__teacherData = {
			classes: {!! json_encode($classes) !!},
			feedbackSummary: {!! json_encode($feedbackSummary) !!}
		};
		// future: frontend code can call APIs and update window.__teacherData then re-render UI
	</script>
@endsection
