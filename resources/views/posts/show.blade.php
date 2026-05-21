@extends('layouts.app')

@section('content')
    <article class="mx-auto max-w-3xl">
        <a href="/" class="text-sm text-stone-400 transition hover:text-amber-200">← Back to posts</a>

        <p class="mt-8 text-sm uppercase tracking-[0.25em] text-amber-200">{{ $post->published_at?->format('M d, Y') }}</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-white sm:text-5xl">{{ $post->title }}</h1>
        <p class="mt-5 text-lg leading-8 text-stone-300">{{ $post->excerpt }}</p>

        <div class="mt-10 space-y-6 text-lg leading-8 text-stone-300">
            @foreach (preg_split('/\n\n+/', trim($post->body)) as $paragraph)
                <p>{{ $paragraph }}</p>
            @endforeach
        </div>
    </article>
@endsection