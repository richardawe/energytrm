{{--
  Market Data Feed Status widget — shown on the dashboard.
  Shows each index with its live-feed source, last sync time, and a colour-coded
  staleness badge. Admins get a "Refresh Now" button.

  Variables expected:
    $feedStatus = [
      'indices'        => Collection<IndexDefinition>,
      'currencies'     => Collection<Currency>,
      'risk_free_rate' => float|null,
    ]
--}}

<div class="card card-etrm mb-3">
    <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        <span>Market Data Feeds</span>
        <div class="d-flex align-items-center gap-2">
            @if($feedStatus['risk_free_rate'] !== null)
            <span class="badge bg-secondary" style="font-size:.7rem;">
                SOFR {{ number_format($feedStatus['risk_free_rate'] * 100, 3) }}%
            </span>
            @endif
            @if(auth()->user()?->isAdmin())
            <form method="POST" action="{{ route('admin.market-data.refresh') }}" class="d-inline m-0">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:.75rem;">
                    ↺ Refresh Now
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="card-body p-0">

        {{-- Commodity Prices --}}
        <div class="px-3 pt-2 pb-1">
            <div class="small fw-semibold text-muted text-uppercase mb-1" style="letter-spacing:.06em;font-size:.68rem;">
                Commodity Indices
            </div>
            <table class="table table-sm mb-0" style="font-size:.82rem;">
                <tbody>
                @foreach($feedStatus['indices'] as $idx)
                @php
                    $hasLiveFeed = ! empty($idx->live_feed_source) && $idx->live_feed_source !== 'manual';
                    $syncedToday = $idx->last_synced_at && $idx->last_synced_at->isToday();
                    $syncedRecently = $idx->last_synced_at && $idx->last_synced_at->gt(now()->subHours(26));

                    if (! $hasLiveFeed) {
                        $badgeClass = 'bg-secondary';
                        $badgeLabel = 'Manual';
                    } elseif ($syncedToday) {
                        $badgeClass = 'bg-success';
                        $badgeLabel = 'Live';
                    } elseif ($syncedRecently) {
                        $badgeClass = 'bg-warning text-dark';
                        $badgeLabel = 'Stale';
                    } else {
                        $badgeClass = 'bg-danger';
                        $badgeLabel = $idx->live_feed_source ? 'Not synced' : 'No feed';
                    }
                @endphp
                <tr>
                    <td class="ps-0 fw-semibold" style="width:45%">{{ $idx->index_name }}</td>
                    <td class="text-muted" style="font-size:.75rem;width:25%">
                        @if($hasLiveFeed)
                            {{ strtoupper($idx->live_feed_source) }}:{{ $idx->live_feed_series }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="width:30%" class="text-end">
                        <span class="badge {{ $badgeClass }}" style="font-size:.68rem;">{{ $badgeLabel }}</span>
                        @if($idx->last_synced_at)
                        <span class="text-muted ms-1" style="font-size:.72rem;">{{ $idx->last_synced_at->diffForHumans() }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- FX Rates --}}
        <div class="px-3 pt-1 pb-2 border-top mt-1">
            <div class="small fw-semibold text-muted text-uppercase mb-1" style="letter-spacing:.06em;font-size:.68rem;">
                FX Rates (per USD)
            </div>
            <div class="d-flex flex-wrap gap-3" style="font-size:.82rem;">
            @foreach($feedStatus['currencies'] as $cur)
            @php
                $fxSyncedToday = $cur->last_synced_at && $cur->last_synced_at->isToday();
            @endphp
            <div>
                <span class="fw-semibold">{{ $cur->code }}</span>
                <span class="text-muted ms-1">{{ number_format((float) $cur->fx_rate_to_usd, 4) }}</span>
                @if($fxSyncedToday)
                <span class="badge bg-success ms-1" style="font-size:.62rem;">Live</span>
                @elseif($cur->last_synced_at)
                <span class="badge bg-warning text-dark ms-1" style="font-size:.62rem;">Stale</span>
                @else
                <span class="badge bg-secondary ms-1" style="font-size:.62rem;">Static</span>
                @endif
            </div>
            @endforeach
            </div>
        </div>

    </div>
</div>
