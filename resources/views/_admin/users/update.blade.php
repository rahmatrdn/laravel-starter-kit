    @extends('_admin._layout.app')

    @section('title', 'Update User')

    @section('content')
        <div class="max-w-2xl mx-auto">
            <div class="bg-white border border-gray-200 rounded-xl shadow-2xs dark:bg-neutral-800 dark:border-neutral-700">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                        Update User
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-neutral-400">
                        Edit user account details.
                    </p>
                </div>

                <form id="update-form" class="p-6">
                    @csrf

                    <div class="max-w-sm mb-4">
                        <label for="name" class="block text-sm font-medium mb-2 dark:text-white">Name <span class="text-red-500">*</span></label>
                        <input type="text" id="name" name="name" value="{{ $data->name ?? '' }}" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="Enter full name" required>
                    </div>

                    <div class="max-w-sm mb-4">
                        <label for="email" class="block text-sm font-medium mb-2 dark:text-white">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="email" name="email" value="{{ $data->email ?? '' }}" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" placeholder="you@site.com" required>
                    </div>

                    <div class="max-w-sm mb-4">
                        <label for="access_type" class="block text-sm font-medium mb-2 dark:text-white">Access Type <span class="text-red-500">*</span></label>
                        <select id="access_type" name="access_type" class="py-2.5 sm:py-3 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" required>
                            <option value="">-- Select Access Type --</option>
                            <option value="admin" {{ ($data->access_type ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="user" {{ ($data->access_type ?? '') == 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-x-2">
                        <a href="{{ route('admin.users.index') }}" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-2xs hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-50 dark:bg-transparent dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                            Cancel
                        </a>
                        <button type="submit" class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700 focus:outline-hidden focus:bg-blue-700 disabled:opacity-50 disabled:pointer-events-none">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endsection

    @push('scripts')
    <script>
        document.getElementById('update-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const form = this;
            const formData = new FormData(form);
            
            fetch('{{ route("admin.users.doUpdate", $userId) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': formData.get('_token'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: formData.get('name'),
                    email: formData.get('email'),
                    access_type: formData.get('access_type'),
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || 'User updated successfully');
                    window.location.href = '{{ route("admin.users.index") }}';
                } else {
                    alert(data.message || 'Failed to update user');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating user');
            });
        });
    </script>
    @endpush
