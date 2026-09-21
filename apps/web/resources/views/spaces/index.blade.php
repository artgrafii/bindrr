<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>bindrr — spaces</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; max-width: 40rem; margin: 2rem auto; padding: 0 1rem; color: #111; }
        h1 { font-size: 1.5rem; }
        .muted { color: #666; font-size: .9rem; }
        ul { padding-left: 1.2rem; }
        form { display: flex; gap: .5rem; margin: 1.5rem 0; }
        input, button { padding: .5rem .75rem; font: inherit; }
        a { color: #0b57d0; }
    </style>
</head>
<body>
    <h1>bindrr</h1>
    <p class="muted">Shared spaces for humans and agents. Slice 1 — create a space, upload files.</p>

    <form method="post" action="{{ route('spaces.store') }}">
        @csrf
        <input type="text" name="name" placeholder="Space name" required>
        <button type="submit">Create</button>
    </form>
    @error('name')<p style="color:#b00020">{{ $message }}</p>@enderror

    <h2>Spaces</h2>
    @if ($spaces->isEmpty())
        <p class="muted">아직 Space가 없습니다</p>
    @else
        <ul>
            @foreach ($spaces as $space)
                <li><a href="{{ route('spaces.show', $space) }}">{{ $space }}</a></li>
            @endforeach
        </ul>
    @endif
</body>
</html>
