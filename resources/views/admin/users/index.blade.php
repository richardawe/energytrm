<x-app-layout>
    <x-slot name="title">User Management</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <span class="small fw-semibold">User Management</span>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"
           style="background:var(--etrm-secondary);border-color:var(--etrm-secondary);">+ New User</a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card card-etrm">
        <div class="card-body p-0">
            <table class="table table-etrm table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @php
                                $badge = match($user->role) {
                                    'admin'       => 'danger',
                                    'trader'      => 'primary',
                                    'back_office' => 'secondary',
                                    default       => 'light',
                                };
                                $label = match($user->role) {
                                    'admin'       => 'Admin',
                                    'trader'      => 'Trader',
                                    'back_office' => 'Back Office',
                                    default       => $user->role,
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                        </td>
                        <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="btn btn-outline-secondary btn-xs py-0 px-2" style="font-size:.75rem;">Edit</a>
                            @if(auth()->id() !== $user->id)
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  class="d-inline"
                                  onsubmit="return confirm('Delete {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="btn btn-outline-danger btn-xs py-0 px-2"
                                        style="font-size:.75rem;">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-muted text-center py-3">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer py-2">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
