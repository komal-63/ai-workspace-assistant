<h1>User Details</h1>

<p>Name: {{ $user->name }}</p>

<p>Email: {{ $user->email }}</p>

<p>Role: {{ $user->role }}</p>

<a href="{{ route('admin.users') }}">Back to Users</a>

@can('update', $user)

    <h2>Change Role</h2>

    <form method="POST" action="{{ route('admin.users.update-role', $user) }}">

        @csrf
        @method('PUT')

        <select name="role">
            <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>
                User
            </option>

            <option value="manager" {{ $user->role === 'manager' ? 'selected' : '' }}>
                Manager
            </option>

            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                Admin
            </option>
        </select>

        <button type="submit">Update Role</button>

    </form>

@endcan

@can('delete', $user)

    <form method="POST"
          action="{{ route('admin.users.delete', $user) }}">

        @csrf
        @method('DELETE')

        <button type="submit">
            Delete User
        </button>

    </form>

@endcan