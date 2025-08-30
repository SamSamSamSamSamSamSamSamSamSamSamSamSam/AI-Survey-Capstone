@extends('layouts.default')

@section('content')
	<div class="ml-4">
		<h1 class="text-2xl font-bold mb-4">Users</h1>
		<table class="min-w-full bg-white">
			<thead>
				<tr>
					<th class="px-4 py-2 text-left">Name</th>
					<th class="px-4 py-2 text-left">Email</th>
					<th class="px-4 py-2 text-left">Role</th>
				</tr>
			</thead>
			<tbody>
				@foreach($users as $u)
					<tr>
						<td class="border px-4 py-2">{{ $u->name }}</td>
						<td class="border px-4 py-2">{{ $u->email }}</td>
						<td class="border px-4 py-2">{{ $u->role }}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
@endsection
