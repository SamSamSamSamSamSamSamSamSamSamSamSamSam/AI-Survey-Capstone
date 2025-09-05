@extends('layouts.default')

@section('content')
	<div class="ml-4">
		<h1 class="h4">Results</h1>
		@foreach($results as $r)
			<div class="card mb-2">
				<div class="card-body">
					<h5>{{ $r['course'] }}</h5>
					<p>Score: {{ $r['score'] }}</p>
					@if(!empty($r['comments']))
						<h6 class="small text-muted">Comments</h6>
						<ul>
							@foreach($r['comments'] as $c)
								<li>{{ $c }}</li>
							@endforeach
						</ul>
					@endif
				</div>
			</div>
		@endforeach
	</div>
@endsection
