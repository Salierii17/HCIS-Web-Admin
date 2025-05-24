<form action="{{ route('admin.assign.training') }}" method="POST">
    @csrf
    <label>User ID: <input type="number" name="user_id"></label><br>
    <label>Package ID: <input type="number" name="package_id"></label><br>
    <button type="submit">Send Email</button>
    <select name="user_id">
        @foreach ($users as $user)
            <option value="{{ $user->id }}">{{ $user->name }}</option>
        @endforeach
    </select>

    <select name="package_id">
        @foreach ($packages as $package)
            <option value="{{ $package->id }}">{{ $package->title }}</option>
        @endforeach
    </select>
</form>
