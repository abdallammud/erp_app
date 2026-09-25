<x-layouts.app title="Profile">
    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Profile</h1>
    <p class="mt-1 text-sm text-slate-500">Manage your account's profile, password, and data.</p>

    <div class="mt-6 space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <livewire:profile.update-profile-information-form />
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <livewire:profile.update-password-form />
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <livewire:profile.delete-user-form />
        </div>
    </div>
</x-layouts.app>
