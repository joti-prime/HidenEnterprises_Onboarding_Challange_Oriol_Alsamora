@php
    $isAbuseChecker = $punishment->source && str_starts_with($punishment->source, 'abuse_checker_');
    $hasSuspendedOrders = is_array($punishment->metadata ?? null)
        && !empty($punishment->metadata['suspended_order_ids'] ?? null);
    $hasServerUuid = is_array($punishment->metadata ?? null)
        && (!empty($punishment->metadata['server_uuid'] ?? null) || !empty($punishment->metadata['short_uuid'] ?? null));
    $shortUuid = $punishment->metadata['short_uuid'] ?? (isset($punishment->metadata['server_uuid']) ? substr($punishment->metadata['server_uuid'], 0, 8) : null);
    $scanner = $punishment->metadata['scanner'] ?? null;
    $url = route('admin.bans.unban', $punishment->id);
    $pid = $punishment->id;
@endphp

<div id="unban-popup-{{ $pid }}"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:99999; align-items:center; justify-content:center; padding:16px;"
     onclick="if(event.target===this){this.style.display='none';}">
    <div style="background:#1f2128; color:#e6e6e6; border-radius:8px; max-width:560px; width:100%; padding:0; box-shadow:0 20px 60px rgba(0,0,0,0.6); max-height:90vh; overflow-y:auto; text-align:left;">

        <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 22px; border-bottom:1px solid #2c2f37;">
            <div style="font-size:17px; font-weight:600;">
                Unban {{ $punishment->user->username }}
                <span style="color:#7d8590; font-size:13px; font-weight:400;">#{{ $pid }}</span>
            </div>
            <span style="cursor:pointer; color:#7d8590; font-size:26px; line-height:1; user-select:none;"
                  onclick="document.getElementById('unban-popup-{{ $pid }}').style.display='none';">&times;</span>
        </div>

        <div style="padding:18px 22px;">

            <button type="button"
                    onclick="window.location.href='{{ $url }}?action=unban_only';"
                    style="display:block; width:100%; background:#d97706; border:none; color:#fff; padding:14px 16px; border-radius:6px; text-align:left; margin-bottom:10px; cursor:pointer; font-family:inherit;">
                <div style="font-size:14px; font-weight:600;">
                    <i class="fas fa-unlock-alt" style="margin-right:8px;"></i>Unban only
                </div>
                <div style="font-size:12px; margin-top:4px; opacity:0.9;">Just removes the ban. Orders stay as they are.</div>
            </button>

            <button type="button"
                    onclick="window.location.href='{{ $url }}?action=unban_and_unsuspend';"
                    style="display:block; width:100%; background:#16a34a; border:none; color:#fff; padding:14px 16px; border-radius:6px; text-align:left; margin-bottom:10px; cursor:pointer; font-family:inherit;">
                <div style="font-size:14px; font-weight:600;">
                    <i class="fas fa-play" style="margin-right:8px;"></i>Unban + Unsuspend orders
                </div>
                <div style="font-size:12px; margin-top:4px; opacity:0.9;">
                    Re-activates the order(s) this punishment force-suspended. Orders whose billing period already ended stay suspended.
                    @if(!$hasSuspendedOrders)
                        <span style="color:#fde68a;">No tracked orders on this punishment.</span>
                    @endif
                </div>
            </button>

            <button type="button"
                    onclick="window.location.href='{{ $url }}?action=unban_unsuspend_exclude';"
                    style="display:block; width:100%; background:#2563eb; border:none; color:#fff; padding:14px 16px; border-radius:6px; text-align:left; margin-bottom:0; cursor:pointer; font-family:inherit;">
                <div style="font-size:14px; font-weight:600;">
                    <i class="fas fa-shield-alt" style="margin-right:8px;"></i>Unban + Unsuspend + Exclude server
                </div>
                <div style="font-size:12px; margin-top:4px; opacity:0.9;">
                    Adds {{ $shortUuid ? $shortUuid : "this server's UUID" }} to {{ $scanner ? $scanner : 'the scanner' }} ignore list, auto-commits and pushes to repo.
                    @if(!$hasServerUuid)
                        <span style="color:#fde68a;">No server UUID on this punishment.</span>
                    @endif
                </div>
            </button>

        </div>

        <div style="padding:14px 22px; border-top:1px solid #2c2f37; text-align:right;">
            <button type="button"
                    onclick="document.getElementById('unban-popup-{{ $pid }}').style.display='none';"
                    style="background:#374151; border:none; color:#fff; padding:8px 18px; border-radius:5px; cursor:pointer; font-family:inherit;">
                Cancel
            </button>
        </div>

    </div>
</div>
