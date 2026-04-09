@extends('layouts.admin')

@section('title', 'Coach Accounts - Verification')

@section('content')
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Coach Verification</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Manage and verify coach account registrations ({{ $coaches->total() }} total)
                </p>
            </div>
        </div>

        <!-- Search & Filter -->
        <form method="GET" class="bg-white shadow-sm border border-gray-200 rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="flex items-end space-x-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md shadow-sm hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                        Filter
                    </button>
                    @if (request('search') || request('status'))
                        <a href="{{ route('admin.coaches.index') }}"
                            class="px-4 py-2 text-sm text-gray-700 underline hover:text-gray-900">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white shadow-sm border border-gray-200 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Registered</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($coaches as $coach)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $coach->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $coach->roles->pluck('name')->implode(', ') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $coach->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $coach->phone ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = match ($coach->status) {
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'active' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statusClass }}">
                                        {{ ucfirst($coach->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $coach->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-1">
                                    <a href="{{ route('admin.coaches.show', $coach) }}"
                                        class="text-indigo-600 hover:text-indigo-900">View</a>
                                    @if ($coach->status === 'pending')
                                        <button onclick="verifyCoach({{ $coach->id }})"
                                            class="text-green-600 hover:text-green-900 font-medium">Verify</button>
                                        <button onclick="rejectCoach({{ $coach->id }})"
                                            class="text-red-600 hover:text-red-900 font-medium">Reject</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No coach accounts found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t">
                {{ $coaches->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Verify Modal -->
    <div id="verifyModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Verify Coach Account</h3>
            <form id="verifyForm">
                <input type="hidden" name="user_id" id="verifyUserId">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-4">Are you sure you want to verify this coach account?</p>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('verifyModal')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700">Verify</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Coach Account</h3>
            <form id="rejectForm">
                <input type="hidden" name="user_id" id="rejectUserId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reason (required)</label>
                    <textarea name="reason" rows="3" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        placeholder="Explain why this registration is rejected..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('rejectModal')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700">Reject</button>
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

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className =
                `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${type === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        async function sendRequest(url, body) {
            const response = await fetch(url, {
                method: 'POST', // ← pakai POST
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-HTTP-Method-Override': 'PATCH' // ← override ke PATCH
                },
                body: JSON.stringify({
                    ...body,
                    _method: 'PATCH' // ← Laravel method spoofing
                })
            });

            if (response.status === 419) {
                showToast('Session expired. Refreshing...', 'error');
                setTimeout(() => location.reload(), 1500);
                return null;
            }

            if (response.status === 403) {
                showToast('Akses ditolak.', 'error');
                return null;
            }

            return response.json();
        }

        // Verify
        document.getElementById('verifyForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const userId = document.getElementById('verifyUserId').value;
            const url = `{{ route('admin.coaches.verify', ':user') }}`.replace(':user', userId);

            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            const data = await sendRequest(url, {
                user_id: userId
            });

            if (data?.status === 'success') {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else if (data) {
                showToast(data.message || 'Terjadi kesalahan.', 'error');
                btn.disabled = false;
                btn.textContent = 'Verify';
            }
        });

        // Reject
        document.getElementById('rejectForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const userId = document.getElementById('rejectUserId').value;
            const reason = this.querySelector('textarea[name="reason"]').value;
            const url = `{{ route('admin.coaches.reject', ':user') }}`.replace(':user', userId);

            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.textContent = 'Processing...';

            const data = await sendRequest(url, {
                user_id: userId,
                reason: reason
            });

            if (data?.status === 'success') {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else if (data) {
                showToast(data.message || 'Terjadi kesalahan.', 'error');
                btn.disabled = false;
                btn.textContent = 'Reject';
            }
        });
    </script>

@endsection
