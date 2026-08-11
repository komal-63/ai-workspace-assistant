<h1>Users</h1>

@foreach($users as $user)

  <p>Name:{{ $user->name }}</p>
  <p>Email:{{ $user->email }}</p>
  <p>Role:{{ $user->role }}</p>


@endforeach  