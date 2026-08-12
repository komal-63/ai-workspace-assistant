<h1>My Documents</h1>

@if(session('success'))
    <p>{{ session('success') }}</p>
@endif

<form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">

    @csrf

    <div>
        <label>Title</label>
        <input type="text" name="title">
    </div>

    <div>
        <label>File</label>
        <input type="file" name="file">
    </div>

    <button type="submit">
        Upload Document
    </button>

</form>

<hr>

@foreach($documents as $document)

    <div>
        <h3>{{ $document->title }}</h3>

        <p>
            Type: {{ $document->mime_type }}
        </p>

        <p>
            Path: {{ $document->file_path }}
        </p>
        <form
            action="{{ route('documents.destroy', $document) }}"
            method="POST"
        >
            @csrf
            @method('DELETE')

            <button type="submit">
                Delete
            </button>
</form>
    </div>

@endforeach