@php
    $pid = $punishment->id;
    $deleteUrl = route('admin.bans.destroy', $pid);
@endphp

<div id="delete-popup-{{ $pid }}"
     style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.7); z-index:99999; align-items:center; justify-content:center; padding:16px;"
     onclick="if(event.target===this){this.style.display='none';}">
    <div style="background:#1f2128; color:#e6e6e6; border-radius:8px; max-width:480px; width:100%; padding:0; box-shadow:0 20px 60px rgba(0,0,0,0.6); text-align:left;">

        <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 22px; border-bottom:1px solid #2c2f37;">
            <div style="font-size:17px; font-weight:600; color:#fca5a5;">
                Delete punishment #{{ $pid }}?
            </div>
            <span style="cursor:pointer; color:#7d8590; font-size:26px; line-height:1; user-select:none;"
                  onclick="document.getElementById('delete-popup-{{ $pid }}').style.display='none';">&times;</span>
        </div>

        <div style="padding:18px 22px; text-align:left;">
            <p style="margin:0 0 12px 0; font-size:14px; text-align:left;">
                This will permanently remove the punishment row for
                <strong>{{ $punishment->user->username }}</strong> ({{ $punishment->user->email }}).
            </p>
            <p style="margin:0; font-size:12px; color:#7d8590; text-align:left;">
                Deleting is different from unbanning: it drops the audit record entirely.
                The user history will not show this ban. If you want to undo the ban but keep the record, use the <strong>Unban</strong> button instead.
            </p>
        </div>

        <div style="padding:14px 22px; border-top:1px solid #2c2f37; display:flex; justify-content:flex-end; gap:8px;">
            <button type="button"
                    onclick="document.getElementById('delete-popup-{{ $pid }}').style.display='none';"
                    style="background:#374151; border:none; color:#fff; padding:8px 18px; border-radius:5px; cursor:pointer; font-family:inherit;">
                Cancel
            </button>
            <button type="button"
                    onclick="window.location.href='{{ $deleteUrl }}';"
                    style="background:#dc2626; border:none; color:#fff; padding:8px 18px; border-radius:5px; cursor:pointer; font-family:inherit;">
                <i class="fas fa-trash" style="margin-right:4px;"></i> Delete permanently
            </button>
        </div>

    </div>
</div>
