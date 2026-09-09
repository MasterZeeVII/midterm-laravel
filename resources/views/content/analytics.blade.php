@php
    $groups = ['day' => 'รายวัน', 'week' => 'รายสัปดาห์', 'month' => 'รายเดือน'];

    // JSON_HEX_TAG keeps a "</script>" inside any string from ending the block
    // early, so this stays safe even though the labels are generated server-side.
    $chartJson = json_encode([
        'labels' => $trend['labels'],
        'income' => $trend['income'],
        'expense' => $trend['expense'],
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
@endphp

<section aria-label="ภาพรวม" class="grid gap-8 xl:grid-cols-[minmax(0,3fr)_minmax(0,2fr)]">

    <!-- Trend: income against expense, one shared money axis. -->
    <div class="min-w-0">
        <div class="flex flex-wrap items-baseline justify-between gap-x-6 gap-y-2">
            <h2 class="text-base font-semibold">รายรับและรายจ่ายตามช่วงเวลา</h2>
            <nav aria-label="ความละเอียดของกราฟ" class="flex items-center gap-4 text-sm">
                @foreach ($groups as $key => $label)
                    @if ($key === $group)
                        <span class="font-medium text-teal-700" aria-current="true">{{ $label }}</span>
                    @else
                        <a href="{{ route('home', array_merge($filterQuery, ['group' => $key])) }}"
                           class="text-slate-500 hover:text-slate-900">{{ $label }}</a>
                    @endif
                @endforeach
            </nav>
        </div>

        @if ($trend['empty'])
            <p class="mt-6 border-t border-slate-200 pt-10 pb-10 text-center text-sm text-slate-400">
                ยังไม่มีข้อมูลในช่วงนี้ เพิ่มรายการทางซ้ายเพื่อเริ่มดูแนวโน้ม
            </p>
        @else
            <div class="mt-3 flex items-center gap-6 text-sm text-slate-500">
                <span class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-sm bg-teal-600"></span>รายรับ
                </span>
                <span class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-sm bg-red-600"></span>รายจ่าย
                </span>
            </div>

            <div class="mt-4 h-72">
                <canvas id="trend-chart" aria-label="กราฟแท่งเปรียบเทียบรายรับและรายจ่ายในแต่ละช่วงเวลา" role="img"></canvas>
            </div>

            <!-- The same numbers in text, for anyone the chart doesn't serve. -->
            <details class="mt-3 text-sm">
                <summary class="cursor-pointer text-slate-500 hover:text-slate-900">ดูเป็นตาราง</summary>
                <div class="mt-3 overflow-x-auto">
                <table class="w-full min-w-[22rem] border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-slate-500">
                            <th class="py-2 pr-4 font-normal">ช่วงเวลา</th>
                            <th class="py-2 pr-4 text-right font-normal">รายรับ</th>
                            <th class="py-2 text-right font-normal">รายจ่าย</th>
                        </tr>
                    </thead>
                    <tbody class="figure">
                        @foreach ($trend['labels'] as $i => $label)
                            <tr class="border-b border-slate-100">
                                <td class="py-1.5 pr-4">{{ $label }}</td>
                                <td class="py-1.5 pr-4 text-right text-teal-700">฿{{ number_format($trend['income'][$i], 2) }}</td>
                                <td class="py-1.5 text-right text-red-600">฿{{ number_format($trend['expense'][$i], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </details>

            <script type="application/json" id="trend-data">{!! $chartJson !!}</script>
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
            <script>
                (function () {
                    var canvas = document.getElementById('trend-chart');
                    if (!canvas || typeof Chart === 'undefined') return;

                    var data = JSON.parse(document.getElementById('trend-data').textContent);
                    var still = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    var baht = new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' });
                    var compact = new Intl.NumberFormat('th-TH', { notation: 'compact', maximumFractionDigits: 1 });

                    Chart.defaults.font.family = "'IBM Plex Sans Thai', 'Noto Sans Thai', 'Leelawadee UI', system-ui, sans-serif";
                    Chart.defaults.color = '#64748b';

                    new Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels: data.labels,
                            datasets: [
                                { label: 'รายรับ', data: data.income, backgroundColor: '#0d9488' },
                                { label: 'รายจ่าย', data: data.expense, backgroundColor: '#dc2626' }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            animation: still ? false : { duration: 400 },
                            // Thin marks with a surface gap between the pair.
                            barPercentage: 0.7,
                            categoryPercentage: 0.65,
                            borderRadius: 4,
                            borderSkipped: 'bottom',
                            interaction: { mode: 'index', intersect: false },
                            plugins: {
                                legend: { display: false },
                                tooltip: {
                                    backgroundColor: '#0f172a',
                                    padding: 10,
                                    displayColors: true,
                                    boxWidth: 8,
                                    boxHeight: 8,
                                    callbacks: {
                                        label: function (ctx) {
                                            return ctx.dataset.label + '  ' + baht.format(ctx.parsed.y);
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    grid: { display: false },
                                    border: { color: '#e2e8f0' },
                                    ticks: { padding: 6 }
                                },
                                y: {
                                    beginAtZero: true,
                                    grid: { color: '#f1f5f9', drawTicks: false },
                                    border: { display: false },
                                    ticks: { padding: 8, callback: function (v) { return compact.format(v); } }
                                }
                            }
                        }
                    });
                })();
            </script>
        @endif
    </div>

    <!-- Breakdown: where the money goes, ranked. Bars are plain CSS. -->
    <div class="min-w-0 space-y-7 xl:border-l xl:border-slate-200 xl:pl-8">
        @foreach ([['expense', 'จ่ายมากที่สุด', 'bg-red-600', 'text-red-600'], ['income', 'รับมากที่สุด', 'bg-teal-600', 'text-teal-700']] as [$key, $title, $barClass, $textClass])
            <div>
                <h2 class="text-base font-semibold">{{ $title }}</h2>

                @if (count($breakdown[$key]) === 0)
                    <p class="mt-4 text-sm text-slate-400">ยังไม่มีข้อมูลในช่วงนี้</p>
                @else
                    <ul class="mt-4 space-y-3">
                        @foreach ($breakdown[$key] as $row)
                            <li>
                                <div class="flex items-baseline justify-between gap-4 text-sm">
                                    <span class="truncate">{{ $row['name'] }}</span>
                                    <span class="figure shrink-0 font-medium {{ $textClass }}">฿{{ number_format($row['total'], 2) }}</span>
                                </div>
                                <div class="mt-1.5 flex items-center gap-3">
                                    <span class="h-1.5 flex-1 overflow-hidden rounded-sm bg-slate-100">
                                        <span class="block h-full rounded-sm {{ $barClass }}" style="width: {{ $row['width'] }}%"></span>
                                    </span>
                                    <span class="figure w-10 shrink-0 text-right text-xs text-slate-500">{{ number_format($row['share'], 0) }}%</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>
</section>
