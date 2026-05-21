@extends('layouts.app')

@section('content')
    <section class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
        <div>
            <p class="mb-4 inline-flex rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-xs uppercase tracking-[0.3em] text-amber-200">Laravel blog</p>
            <h1 class="max-w-2xl text-4xl font-semibold tracking-tight text-white sm:text-5xl">A simple blog site built with Laravel.</h1>
            <p class="mt-5 max-w-2xl text-base leading-7 text-stone-300">Use this as your starting point for publishing posts, adding categories, and later connecting an admin dashboard.</p>
        </div>

        <div class="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-black/20 backdrop-blur">
            <p class="text-sm uppercase tracking-[0.25em] text-stone-400">What is included</p>
            <ul class="mt-4 space-y-3 text-stone-200">
                <li>Public home page</li>
                <li>Post detail pages</li>
                <li>Database-backed content</li>
            </ul>
        </div>
    </section>

    <section class="mt-14">
        <div class="mb-6 flex items-end justify-between gap-4">
            <h2 class="text-2xl font-semibold text-white">Latest posts</h2>
            <p class="text-sm text-stone-400">{{ $posts->count() }} published</p>
        </div>

        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($posts as $post)
                <article class="group rounded-3xl border border-white/10 bg-white/5 p-6 transition hover:-translate-y-1 hover:border-amber-400/30 hover:bg-white/10">
                    <p class="text-sm text-stone-400">{{ $post->published_at?->format('M d, Y') }}</p>
                    <h3 class="mt-3 text-xl font-semibold text-white group-hover:text-amber-200">
                        <a href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>
                    </h3>
                    <p class="mt-3 leading-7 text-stone-300">{{ $post->excerpt }}</p>
                    <a href="{{ route('posts.show', $post) }}" class="mt-5 inline-flex text-sm font-medium text-amber-200">Read post</a>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-white/15 bg-white/5 p-8 text-stone-300 md:col-span-2 xl:col-span-3">
                    No posts yet. Add some seeded content and run the migrations.
                </div>
            @endforelse
        </div>
    </section>
@endsection