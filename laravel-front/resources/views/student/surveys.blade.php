@extends('layouts.admin')

@section('content')
	<x-sidebar />
	<div class="ml-4">
		<h1 class="h4">Surveys</h1>
		<table class="table mt-3">
			<thead><tr><th>Course</th><th>Status</th><th>Score</th></tr></thead>
			<tbody>
				@foreach($surveys as $s)
					<tr>
						<td>{{ $s['course'] }}</td>
						<td>{{ $s['status'] }}</td>
						<td>{{ $s['score'] ?? '-' }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection
