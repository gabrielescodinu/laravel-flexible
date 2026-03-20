@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Manrope', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap');

        @layer components {
            .shell {
                @apply mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8;
            }

            .panel {
                @apply rounded-2xl border border-slate-200/80 bg-white/90 p-6 shadow-sm;
            }

            .muted {
                @apply text-sm text-slate-500;
            }

            .btn {
                @apply inline-flex items-center justify-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition;
            }

            .btn-primary {
                @apply border-slate-900 bg-slate-900 text-white hover:bg-slate-700 hover:border-slate-700;
            }

            .btn-secondary {
                @apply border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50;
            }

            .btn-danger {
                @apply border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100;
            }

            .field {
                @apply space-y-2;
            }

            .field-label {
                @apply block text-sm font-medium text-slate-600;
            }

            .field-input {
                @apply w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none ring-0 transition placeholder:text-slate-400 focus:border-slate-400;
            }

            .alert {
                @apply rounded-xl border px-4 py-3 text-sm;
            }

            .alert-success {
                @apply border-emerald-200 bg-emerald-50 text-emerald-800;
            }

            .alert-error {
                @apply border-rose-200 bg-rose-50 text-rose-800;
            }

            .status-badge {
                @apply inline-flex rounded-full border px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide;
            }

            .status-published {
                @apply border-emerald-200 bg-emerald-50 text-emerald-700;
            }

            .status-draft {
                @apply border-amber-200 bg-amber-50 text-amber-700;
            }

            .builder-card {
                @apply rounded-2xl border border-slate-200 bg-slate-50/70 p-4;
            }

            .content-block {
                @apply rounded-3xl border border-slate-200 bg-white p-8 shadow-sm;
            }
        }
    </style>
@endif
