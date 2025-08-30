@extends('layouts.default')

@section('content')
	<div class="ml-4">
		<h1 class="h4">Student Feedback</h1>
		<ul class="list-group mt-3">
			@foreach($reviews as $r)
				<li class="list-group-item">
					<div><strong>{{ $r['student'] }}</strong> — Rating: {{ $r['rating'] }}</div>
					<div class="small text-muted">{{ $r['comment'] }}</div>
				</li>
			@endforeach
		</ul>
	</div>
@endsection
