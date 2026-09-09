<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeepGoop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600&display=swap">
    <style>
        body {
            font-family: 'IBM Plex Sans Thai', 'Noto Sans Thai', 'Leelawadee UI', system-ui, sans-serif;
        }

        /* Money lines up in a column, so its digits are fixed-width. */
        .figure { font-variant-numeric: tabular-nums; }

        /* Shared text-input/select styling, used by every form on the page. */
        .field {
            width: 100%;
            border: 0;
            border-bottom: 1px solid #cbd5e1;
            background: transparent;
            padding: 0.375rem 0;
            font-size: 0.875rem;
        }
        .field:focus {
            border-bottom-color: #0f766e;
            outline: none;
        }

        :focus-visible {
            outline: 2px solid #0f766e;
            outline-offset: 2px;
        }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">

    @include('content.dashboard-header')

    <div class="mx-auto max-w-5xl grid grid-cols-1 md:grid-cols-[18rem_1fr] gap-8 px-6 py-8 items-start">

        <!-- Entry rail: the two create/edit forms. -->
        <aside class="space-y-9">
            @include('content.transaction-form')
            @include('content.category-panel')
        </aside>

        <!-- Reading side: every transaction, newest first. -->
        <main class="min-w-0">
            @include('content.transactions-panel')
        </main>
    </div>

</body>
</html>
