<header x-data="{ open: false }" class="border-b border-stone-200 bg-white">
    <nav class="mx-auto flex max-w-4xl items-center justify-between gap-4 px-5 py-4">
        <a href="{{ route('posts.index') }}" class="font-serif text-2xl font-semibold text-stone-900">Nukilan</a>

        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-stone-200 text-stone-600 sm:hidden" @click="open = ! open" aria-label="Toggle navigation">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path x-show="! open" stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                <path x-show="open" stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
            </svg>
        </button>

        <div class="hidden items-center gap-5 text-sm sm:flex">
            @auth
                <a href="{{ route('writer.posts.create') }}" class="font-medium text-accent hover:text-accent-light">Write</a>
                <a href="{{ route('writers.show', Auth::user()) }}" class="text-stone-600 hover:text-stone-900">{{ Auth::user()->name }}</a>
                <a href="{{ route('profile.edit') }}" class="text-stone-500 hover:text-stone-900">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-600 hover:text-stone-900">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-stone-600 hover:text-stone-900">Sign in</a>
                <a href="{{ route('register') }}" class="rounded-md bg-stone-900 px-3 py-2 font-medium text-white hover:bg-stone-700">Get started</a>
            @endauth
        </div>
    </nav>

    <div x-show="open" x-cloak class="border-t border-stone-100 px-5 py-3 sm:hidden">
        <div class="mx-auto flex max-w-4xl flex-col gap-3 text-sm">
            @auth
                <a href="{{ route('writer.posts.create') }}" class="font-medium text-accent">Write</a>
                <a href="{{ route('writers.show', Auth::user()) }}" class="text-stone-600">{{ Auth::user()->name }}</a>
                <a href="{{ route('profile.edit') }}" class="text-stone-500">Settings</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-stone-600">Sign out</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="text-stone-600">Sign in</a>
                <a href="{{ route('register') }}" class="font-medium text-accent">Get started</a>
            @endauth
        </div>
    </div>
</header>
