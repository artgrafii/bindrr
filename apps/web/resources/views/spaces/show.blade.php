<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>bindrr — {{ $space }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, sans-serif; max-width: 40rem; margin: 2rem auto; padding: 0 1rem; color: #111; }
        h1 { font-size: 1.5rem; }
        .muted { color: #666; font-size: .9rem; }
        form { margin: 1.5rem 0; }
        input, button { padding: .5rem .75rem; font: inherit; }
        a { color: #0b57d0; }
        table { width: 100%; border-collapse: collapse; }
        td, th { text-align: left; padding: .4rem 0; border-bottom: 1px solid #eee; }
    </style>
</head>
<body>
    <p><a href="{{ route('spaces.index') }}">← all spaces</a></p>
    <h1>{{ $space }}</h1>
    <p class="muted">Upload a file (max 10 MB). Dogfood — no auth yet.</p>

    <form method="post" action="{{ route('spaces.upload', $space) }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
    @error('file')<p style="color:#b00020">{{ $message }}</p>@enderror

    <h2>Files</h2>
    @if ($files->isEmpty())
        <p class="muted">Empty.</p>
    @else
        <table>
            <thead><tr><th>Name</th><th>Size</th></tr></thead>
            <tbody>
            @foreach ($files as $file)
                <tr>
                    <td><a href="{{ route('spaces.download', [$space, $file['name']]) }}">{{ $file['name'] }}</a> <span class="muted">다운로드 {{ $file['download_count'] }}회</span></td>
                    <td class="muted">{{ $file['size'] }} B</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
