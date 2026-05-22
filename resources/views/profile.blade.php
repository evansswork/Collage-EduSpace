<x-layouts.app title="Profile">
    <div class="max-w-2xl">
        <h2 class="mb-1 text-2xl font-semibold text-gray-900">Profile</h2>
        <p class="mb-6 text-sm text-gray-500">Ringkasan akun yang sedang digunakan.</p>

        <div class="rounded-lg border border-gray-200 bg-white p-5">
            <dl class="space-y-4 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Nama</dt>
                    <dd class="mt-1 text-gray-900">{{ auth()->user()->name }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-gray-900">{{ auth()->user()->email }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-gray-500">Role</dt>
                    <dd class="mt-1 text-gray-900">{{ auth()->user()->isLecturer() ? 'Dosen' : 'Mahasiswa' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts.app>
