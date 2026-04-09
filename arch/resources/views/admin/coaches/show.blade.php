@extends('layouts.admin')

@section('title', 'Coach Detail')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
                <p class="mt-1 text-sm text-gray-500">Coach Account #{{ $user->id }}</p>
            </div>
            <div class="text-right">
                @php$statusClass = match ($user->status) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'active' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                }; @endphp ?>
                <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $user->status_class }}">
                    {{ ucfirst($user->status) }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Account Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $user->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <p class="text-lg text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <p class="text-lg text-gray-900">{{ $user->phone ?? 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
                            <p class="text-lg text-gray-900">{{ $user->roles->pluck('name')->implode(', ') }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Registered</label>
                            <p class="text-lg text-gray-900">{{ $user->created_at->format('d M Y H:i') }}</p>
                        </div>
                        @if ($user->perguruan)
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Perguruan</label>
                                <p class="text-lg text-gray-900">{{ $user->perguruan->name }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($user->status === 'pending')
                    <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="flex space-x-3">
                            <button onclick="verifyCoach({{ $user->id }})"
                                class="px-6 py-2 bg-green-600 text-white font-medium rounded-md shadow-sm hover:bg-green-700">
                                ✅ Verify Coach
                            </button>
                            <button onclick="rejectCoach({{ $user->id }})"
                                class="px-6 py-2 bg-red-600 text-white font-medium rounded-md shadow-sm hover:bg-red-700">
                                ❌ Reject
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar Stats -->
            <div>
                <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-6 sticky top-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Coach Stats</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Athletes Managed</dt>
                            <dd class="text-2xl font-bold text-gray-900">{{ $user->athletes->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd>
                                <span
                                    class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $user->status_class }}">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Account Age</dt>
                            <dd class="text-lg font-semibold text-gray-900">
                                {{ $user->created_at->diffForHumans() }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Athletes Table -->
        @if ($user->athletes->count() > 0)
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Athletes ({{ $user->athletes->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gender</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Weight</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach ($user->athletes as $athlete)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $athlete->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ ucfirst($athlete->gender) }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $athlete->weight }} kg</td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $athlete->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ $athlete->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white shadow-sm border border-gray-200 rounded-lg p-8 text-center">
                <p class="text-gray-500 text-lg">No athletes registered under this coach yet.</p>
            </div>
        @endif
    </div>

    <!-- Modals (shared with index) -->
    <div id="verifyModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Verify Coach Account</h3>
            <form id="verifyForm">
                <input type="hidden" name="user_id" id="verifyUserId">
                <p class="text-sm text-gray-600 mb-4">Are you sure you want to activate this coach account?</p>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('verifyModal')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md shadow-sm hover:bg-green-700">Verify</button>
                </div>
            </form>
        </div>
    </div>

    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Coach Account</h3>
            <form id="rejectForm">
                <input type="hidden" name="user_id" id="rejectUserId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason</label>
                    <textarea name="reason" rows="3" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('rejectModal')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md shadow-sm hover:bg-red-700">Reject</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function verifyCoach(userId) {
            document.getElementById('verifyUserId').value = userId;
            document.getElementById('verifyModal').classList.remove('hidden');
        }

        function rejectCoach(userId) {
            document.getElementById('rejectUserId').value = userId;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        document.getElementById('verifyForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(`/admin/coaches/${formData.get('user_id')}/verify`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') location.reload();
                else alert(data.message);
            });
        });

        document.getElementById('rejectForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(`/admin/coaches/${formData.get('user_id')}/reject`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content'),
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            }).then(res => res.json()).then(data => {
                if (data.status === 'success') location.reload();
                else alert(data.message);
            });
        });
    </script>

@endsection
