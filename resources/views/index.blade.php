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

        /* Money lines up in a column, so its digits are fixed-width. The
           standalone balance figure keeps proportional digits — equal-width
           ones read loose at display sizes. */
        .figure { font-variant-numeric: tabular-nums; }

        /* Shared text-input/select styling — was copy-pasted as a Tailwind
           class string into three separate Blade files; one place to change
           it now. */
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

        /* Modals with no JavaScript: the dialog is hidden until its id is the
           URL fragment, and the backdrop link clears the fragment again. */
        .modal-target {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 50;
            align-items: center;
            justify-content: center;
            background: rgba(15, 23, 42, 0.4);
            padding: 1rem;
        }
        .modal-target:target { display: flex; }

        :focus-visible {
            outline: 2px solid #0f766e;
            outline-offset: 2px;
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { transition-duration: 0.01ms !important; }
        }
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">

    @include('content.dashboard-header')

    <div class="mx-auto max-w-[1600px] grid grid-cols-1 lg:grid-cols-[19rem_1fr] items-start">

        <!-- Entry rail: everything that writes to the ledger lives here. -->
        <aside class="lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto border-b lg:border-b-0 lg:border-r border-slate-200 px-6 py-7 space-y-9">
            @include('content.transaction-form')
            @include('content.category-panel')
        </aside>

        <!-- Reading side: filters scope everything below them. -->
        <main class="min-w-0 px-6 py-7 space-y-8">
            @include('content.filter-bar')
            @include('content.analytics')
            @include('content.transactions-panel')
        </main>
    </div>

</body>
</html>
