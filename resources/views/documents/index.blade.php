<x-app-layout>

    <style>

        /* =========================
           Design Tokens
           ========================= */

        .documents-page {

            --paper: #FAF7F1;
            --paper-raised: #FFFFFF;
            --ink: #23241F;
            --ink-soft: #6B6A63;
            --ink-faint: #A6A399;
            --line: #E7E1D3;

            --brass: #9C6B30;
            --brass-soft: #F1E6D2;

            --moss: #55694A;
            --moss-soft: #E5EBDD;

            --danger: #9A4A3A;
            --danger-soft: #F4E4DF;

            --font-display: 'Fraunces', Georgia, serif;
            --font-body: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;

            min-height: calc(100vh - 68px);

            background: var(--paper);

            color: var(--ink);

            font-family: var(--font-body);
        }


        /* =========================
           Container
           ========================= */

        .documents-container {

            max-width: 900px;

            margin: 0 auto;

            padding: 50px 24px;
        }


        /* =========================
           Header
           ========================= */

        .page-header {

            margin-bottom: 32px;
        }

        .page-eyebrow {

            margin-bottom: 8px;

            color: var(--brass);

            font-size: 11px;

            font-weight: 700;

            letter-spacing: 0.12em;

            text-transform: uppercase;
        }

        .page-header h1 {

            margin: 0;

            font-family: var(--font-display);

            font-size: 34px;

            font-weight: 600;

            letter-spacing: -0.02em;
        }

        .page-header p {

            max-width: 600px;

            margin: 8px 0 0;

            color: var(--ink-soft);

            font-size: 14px;

            line-height: 1.6;
        }


        /* =========================
           Success Message
           ========================= */

        .success-message {

            margin-bottom: 24px;

            padding: 12px 14px;

            border: 1px solid #D5DEC9;

            border-radius: 9px;

            background: var(--moss-soft);

            color: var(--moss);

            font-size: 13px;

            font-weight: 500;
        }


        /* =========================
           Upload Card
           ========================= */

        .upload-card {

            margin-bottom: 38px;

            padding: 22px;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;
        }

        .upload-card-title {

            margin-bottom: 16px;

            font-family: var(--font-display);

            font-size: 19px;

            font-weight: 600;
        }


        /* =========================
           Form
           ========================= */

        .upload-form {

            display: grid;

            grid-template-columns: 1fr 1fr auto;

            gap: 12px;

            align-items: end;
        }


        .form-group {

            display: flex;

            flex-direction: column;

            gap: 7px;
        }


        .form-group label {

            color: var(--ink-soft);

            font-size: 11px;

            font-weight: 600;

            letter-spacing: 0.04em;
        }


        .form-input {

            width: 100%;

            height: 44px;

            box-sizing: border-box;

            padding: 10px 13px;

            border: 1px solid var(--line);

            border-radius: 9px;

            background: var(--paper);

            color: var(--ink);

            font-family: var(--font-body);

            font-size: 13px;
        }


        .form-input:focus {

            outline: none;

            border-color: var(--brass);

            box-shadow: 0 0 0 3px var(--brass-soft);

            background: var(--paper-raised);
        }


        .form-input[type="file"] {

            padding: 9px 10px;

        }


        .upload-btn {

            height: 44px;

            padding: 0 19px;

            border: 1px solid var(--ink);

            border-radius: 9px;

            background: var(--ink);

            color: var(--paper);

            font-family: var(--font-body);

            font-size: 13px;

            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.15s ease,
                color 0.15s ease;
        }


        .upload-btn:hover {

            background: var(--paper);

            color: var(--ink);
        }


        /* =========================
           Documents Header
           ========================= */

        .documents-section-header {

            display: flex;

            align-items: center;

            justify-content: space-between;

            margin-bottom: 12px;
        }


        .section-title {

            color: var(--ink-faint);

            font-size: 10px;

            font-weight: 700;

            letter-spacing: 0.12em;
        }


        .document-count {

            color: var(--ink-faint);

            font-size: 11px;
        }


        /* =========================
           Document List
           ========================= */

        .documents-list {

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;

            overflow: hidden;
        }


        .document-item {

            display: flex;

            align-items: center;

            gap: 15px;

            padding: 17px 18px;

            border-bottom: 1px solid var(--line);
        }


        .document-item:last-child {

            border-bottom: none;
        }


        /* =========================
           Document Icon
           ========================= */

        .document-icon {

            width: 40px;

            height: 40px;

            flex-shrink: 0;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 9px;

            background: var(--moss-soft);

            color: var(--moss);

            font-family: var(--font-display);

            font-size: 14px;

            font-weight: 600;
        }


        /* =========================
           Document Info
           ========================= */

        .document-info {

            flex: 1;

            min-width: 0;
        }


        .document-title {

            overflow: hidden;

            text-overflow: ellipsis;

            white-space: nowrap;

            margin-bottom: 5px;

            font-size: 14px;

            font-weight: 600;
        }


        .document-meta {

            display: flex;

            gap: 12px;

            color: var(--ink-faint);

            font-size: 11px;
        }

        .document-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-btn {
            padding: 8px 11px;
            border: 1px solid var(--line);
            border-radius: 7px;
            background: transparent;
            color: var(--ink);
            font-family: var(--font-body);
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
            transition:
                background 0.15s ease,
                border-color 0.15s ease;
        }

        .view-btn:hover {
            background: var(--brass-soft);
            border-color: var(--brass);
            color: var(--brass);
        }

        .view-btn:focus-visible {
            outline: 2px solid var(--brass);
            outline-offset: 2px;
        }
        /* =========================
           Delete Button
           ========================= */

        .delete-btn {

            padding: 8px 11px;

            border: 1px solid #E4CFC8;

            border-radius: 7px;

            background: transparent;

            color: var(--danger);

            font-family: var(--font-body);

            font-size: 11px;

            font-weight: 600;

            cursor: pointer;

            transition:
                background 0.15s ease,
                border-color 0.15s ease;
        }


        .delete-btn:hover {

            background: var(--danger-soft);

            border-color: #DDBDB4;
        }


        /* =========================
           Empty State
           ========================= */

        .empty-state {

            padding: 55px 20px;

            text-align: center;

            background: var(--paper-raised);

            border: 1px solid var(--line);

            border-radius: 12px;
        }


        .empty-icon {

            width: 58px;

            height: 58px;

            margin: 0 auto 16px;

            display: flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: var(--ink);

            color: var(--paper);

            font-family: var(--font-display);

            font-size: 17px;

            font-weight: 600;
        }


        .empty-state h3 {

            margin: 0;

            font-family: var(--font-display);

            font-size: 20px;

            font-weight: 600;
        }


        .empty-state p {

            max-width: 400px;

            margin: 7px auto 0;

            color: var(--ink-soft);

            font-size: 13px;

            line-height: 1.6;
        }


        /* =========================
           Mobile
           ========================= */

        @media (max-width: 767px) {

            .documents-container {

                padding: 35px 16px;
            }


            .page-header h1 {

                font-size: 29px;
            }


            .upload-form {

                grid-template-columns: 1fr;
            }


            .upload-btn {

                width: 100%;
            }


            .document-item {

                align-items: flex-start;

                padding: 14px;
            }


            .document-meta {

                flex-direction: column;

                gap: 3px;
            }


            .delete-btn {

                flex-shrink: 0;
            }
        }


        /* =========================
           Accessibility
           ========================= */

        .form-input:focus-visible,
        .upload-btn:focus-visible,
        .delete-btn:focus-visible {

            outline: 2px solid var(--brass);

            outline-offset: 2px;
        }


        @media (prefers-reduced-motion: reduce) {

            .documents-page * {

                transition: none !important;
            }
        }

    </style>


    <div class="documents-page">

        <div class="documents-container">


            {{-- Page Header --}}

            <div class="page-header">

                <div class="page-eyebrow">
                    AI Workspace
                </div>

                <h1>
                    My documents
                </h1>

                <p>
                    Upload and manage the documents that power your
                    document-grounded AI conversations.
                </p>

            </div>


            {{-- Success Message --}}

            @if(session('success'))

                <div class="success-message">
                    {{ session('success') }}
                </div>

            @endif


            {{-- Upload Card --}}

            <div class="upload-card">

                <div class="upload-card-title">
                    Upload a new document
                </div>


                <form
                    action="{{ route('documents.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    class="upload-form"
                >

                    @csrf


                    <div class="form-group">

                        <label for="title">
                            Document title
                        </label>

                        <input
                            id="title"
                            type="text"
                            name="title"
                            class="form-input"
                            placeholder="Give your document a title"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="file">
                            File
                        </label>

                        <input
                            id="file"
                            type="file"
                            name="file"
                            class="form-input"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="upload-btn"
                    >
                        Upload document
                    </button>

                </form>

            </div>


            {{-- Documents Section --}}

            <div class="documents-section-header">

                <div class="section-title">
                    YOUR DOCUMENTS
                </div>

                <div class="document-count">
                    {{ $documents->count() }}
                    {{ $documents->count() === 1 ? 'document' : 'documents' }}
                </div>

            </div>


            @if($documents->count())

                <div class="documents-list">

                   @foreach($documents as $document)

    <div class="document-item">

        <div class="document-icon">
            D
        </div>

        <div class="document-info">

            <div class="document-title">
                {{ $document->title }}
            </div>

            <div class="document-meta">

                <span>
                    {{ $document->mime_type }}
                </span>

                <span>
                    {{ $document->created_at->diffForHumans() }}
                </span>

            </div>

        </div>


        <div class="document-actions">

            <a
                href="{{ route('documents.view', $document) }}"
                target="_blank"
                class="view-btn"
            >
                Open
            </a>


            <form
                action="{{ route('documents.destroy', $document) }}"
                method="POST"
            >

                @csrf
                @method('DELETE')

                <button
                    type="submit"
                    class="delete-btn"
                >
                    Delete
                </button>

            </form>

        </div>

    </div>

@endforeach

                </div>

            @else

                <div class="empty-state">

                    <div class="empty-icon">
                        DOC
                    </div>

                    <h3>
                        No documents yet
                    </h3>

                    <p>
                        Upload your first document above to make it
                        available for your AI conversations.
                    </p>

                </div>

            @endif


        </div>

    </div>

</x-app-layout>