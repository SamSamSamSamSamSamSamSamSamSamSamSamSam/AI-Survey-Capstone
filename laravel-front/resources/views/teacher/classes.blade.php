@extends('layouts.admin')

@section('content')
	<x-sidebar />
	<div class="ml-4">
		<h1 class="h4">My Classes</h1>
		<table class="table table-striped mt-3">
			<thead><tr><th>Code</th><th>Title</th><th>Students</th><th>Term</th></tr></thead>
			<tbody>
				@foreach($classes as $c)
					<tr>
						<td>{{ $c['code'] }}</td>
						<td>{{ $c['title'] }}</td>
						<td>{{ $c['students'] }}</td>
						<td>{{ $c['term'] ?? '-' }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection
