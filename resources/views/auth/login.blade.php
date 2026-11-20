@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-slate-950 to-indigo-950">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
        <!-- Brand Mark -->
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-indigo-600 shadow-xl shadow-indigo-500/20 mb-4 ring-1 ring-white/20">
            <svg class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h1 class="text-3xl font-extrabold tracking-tight text-white font-heading">
            LOADORA
        </h1>
        <p class="mt-2 text-sm text-slate-400">
            Private Logistics & Dispatch Management System
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white/95 backdrop-blur-md py-8 px-6 sm:px-10 shadow-2xl shadow-black/40 rounded-2xl border border-slate-200/60">
            @if ($errors->any())
                <div class="mb-6 rounded-xl bg-rose-50 border border-rose-200 p-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-rose-500 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span class="text-sm font-medium text-rose-800">
                            {{ $errors->first() }}
                        </span>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5" id="loginForm">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Email Address
                    </label>
                    <div class="mt-1.5 relative">
                        <input id="email" name="email" type="email" autocomplete="email" required
                            value="{{ old('email') }}"
                            placeholder="admin@loadora.test"
                            class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 text-sm">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Password
                    </label>
                    <div class="mt-1.5">
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            placeholder="••••••••••••"
                            class="block w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-slate-900 shadow-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 text-sm">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-600">
                        <label for="remember" class="ml-2 block text-xs font-medium text-slate-600">
                            Remember this device
                        </label>
                    </div>
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl shadow-md shadow-indigo-600/30 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-600 transition-all duration-150">
                        Sign In to LOADORA
                    </button>
                </div>
            </form>

            <!-- Quick Access Profile -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider text-center mb-3">
                    Fast Access Profile
                </p>
                <div>
                    <button type="button" onclick="fillCreds('admin@loadora.test', 'Admin@12345')"
                        class="w-full flex items-center justify-between px-4 py-2.5 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 transition group">
                        <span class="text-xs font-bold text-slate-800 group-hover:text-indigo-600">Admin Account</span>
                        <span class="text-[11px] text-slate-400 font-mono">admin@loadora.test &bull; Full Access</span>
                    </button>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-slate-500 mt-6">
            &copy; {{ date('Y') }} LOADORA. Authorized personnel only.
        </p>
    </div>
</div>

<script>
function fillCreds(email, password) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = password;
}
</script>
@endsection
