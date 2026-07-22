<?php

namespace App\Http\Controllers\Admin;

use App\Facades\AdminTheme as Theme;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Punishment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class PunishmentController extends Controller
{
    public function bans()
    {
        $punishments = Punishment::whereIn('type', ['ban', 'unbanned'])->latest()->paginate(15);

        return Theme::view('punishments.bans', compact('punishments'));
    }

    public function warnings()
    {
        $punishments = Punishment::where('type', 'warning')->latest()->paginate(15);

        return Theme::view('punishments.warnings', compact('punishments'));
    }

    /**
     * Unban a punishment with optional cascade actions.
     *
     * Supported ?action= values:
     *   unban_only              just flip type to 'unbanned' (default)
     *   unban_and_unsuspend     also re-activate orders this punishment
     *                           suspended, skipping any that have expired
     *   unban_unsuspend_exclude same as above + append the server UUID to
     *                           the relevant ignore_servers list(s) and
     *                           auto-commit the change to the dash repo so
     *                           a future scanner run skips this server
     */
    public function unban(Request $request, Punishment $punishment)
    {
        $action = $request->query('action', 'unban_only');
        $messages = [];

        // 1. Always unban
        $punishment->unban();
        $messages[] = $punishment->user->username . ' has been unbanned.';

        if (in_array($action, ['unban_and_unsuspend', 'unban_unsuspend_exclude'], true)) {
            [$reactivated, $skippedExpired] = $this->cascadeUnsuspend($punishment);
            if ($reactivated) {
                $messages[] = $reactivated . ' order(s) re-activated.';
            }
            if ($skippedExpired) {
                $messages[] = $skippedExpired . ' expired order(s) left suspended.';
            }
        }

        if ($action === 'unban_unsuspend_exclude') {
            $excludeResult = $this->excludeServer($punishment);
            if ($excludeResult) {
                $messages[] = $excludeResult;
            }
        }

        return redirect()->back()->with('success', implode(' ', $messages));
    }

    /**
     * Re-activate the orders this punishment force-suspended, but only if they
     * have not expired in the meantime. Returns [reactivated_count, skipped_expired_count].
     */
    private function cascadeUnsuspend(Punishment $punishment): array
    {
        $metadata = $punishment->metadata ?? [];
        $orderIds = $metadata['suspended_order_ids'] ?? [];
        if (!is_array($orderIds) || empty($orderIds)) {
            return [0, 0];
        }

        $reactivated = 0;
        $skippedExpired = 0;
        $now = now();

        foreach ($orderIds as $orderId) {
            $order = Order::find($orderId);
            if (!$order || $order->status !== 'suspended') {
                continue;
            }
            // If the order's billing period has already ended (due_date in
            // the past), leave it suspended; that suspension would be due to
            // expiry, not due to the punishment we are reverting.
            if ($order->due_date && $now->greaterThan($order->due_date)) {
                $skippedExpired++;
                continue;
            }
            try {
                $order->unsuspend();
                $reactivated++;
            } catch (\Throwable $e) {
                Log::warning('cascadeUnsuspend failed for order ' . $order->id . ': ' . $e->getMessage());
            }
        }

        return [$reactivated, $skippedExpired];
    }

    /**
     * Append the punishment's server UUID to the relevant ignore_servers list
     * file(s) and commit + push the change so the dash repo and Randy stay in
     * sync. Returns a human-friendly summary or null if no exclude was made.
     */
    private function excludeServer(Punishment $punishment): ?string
    {
        $metadata = $punishment->metadata ?? [];
        $shortUuid = $metadata['short_uuid'] ?? null;
        if (!$shortUuid) {
            $serverUuid = $metadata['server_uuid'] ?? null;
            $shortUuid = $serverUuid ? substr($serverUuid, 0, 8) : null;
        }
        if (!$shortUuid) {
            return null;
        }

        // Decide which scanner files to update. The scanner name in metadata
        // is the most reliable hint; fall back to the match types.
        $scanners = [];
        $scannerName = $metadata['scanner'] ?? null;
        if ($scannerName === 'check_ddos-content') {
            $scanners[] = 'check_ddos-content';
        } elseif ($scannerName === 'check_ddos-filenames') {
            $scanners[] = 'check_ddos-filenames';
        }
        // Cover the case where matches included both types: extra safety.
        foreach ($metadata['matches'] ?? [] as $m) {
            if (($m['type'] ?? null) === 'content' && !in_array('check_ddos-content', $scanners, true)) {
                $scanners[] = 'check_ddos-content';
            }
            if (($m['type'] ?? null) === 'filename' && !in_array('check_ddos-filenames', $scanners, true)) {
                $scanners[] = 'check_ddos-filenames';
            }
        }
        if (empty($scanners)) {
            return null;
        }

        $repoRoot = base_path();
        $changedFiles = [];
        foreach ($scanners as $scanner) {
            $relPath = "public/cfgs/api/free-nodes/{$scanner}/{$scanner}_ignore_servers_list.txt";
            $absPath = $repoRoot . '/' . $relPath;
            if (!is_writable(dirname($absPath))) {
                Log::warning("excludeServer: dir not writable {$absPath}");
                continue;
            }

            $existing = is_file($absPath) ? file($absPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
            if (in_array($shortUuid, $existing, true)) {
                continue; // already there
            }
            // Append with trailing newline preserved.
            $line = ($existing && end($existing) !== '' ? "\n" : '') . $shortUuid . "\n";
            if (file_put_contents($absPath, $line, FILE_APPEND | LOCK_EX) === false) {
                Log::warning("excludeServer: append failed {$absPath}");
                continue;
            }
            $changedFiles[] = $relPath;
        }

        if (empty($changedFiles)) {
            return "Server {$shortUuid} was already excluded.";
        }

        $gitMsg = $this->gitCommitAndPush($changedFiles, $shortUuid, $punishment->id);
        return "Excluded server {$shortUuid} from " . count($changedFiles) . ' scanner list(s). ' . $gitMsg;
    }

    /**
     * Stage the changed files, commit, and push from the dash working copy.
     * Returns a short summary string.
     */
    private function gitCommitAndPush(array $relFiles, string $shortUuid, int $punishmentId): string
    {
        $repoRoot = base_path();
        try {
            $addArgs = array_merge(['git', '-C', $repoRoot, 'add'], $relFiles);
            $add = new Process($addArgs);
            $add->run();
            if (!$add->isSuccessful()) {
                Log::warning('git add failed: ' . $add->getErrorOutput());
                return 'Git add failed; change saved locally.';
            }

            $msg = "[Admin] Exclude server {$shortUuid} (punishment #{$punishmentId})";
            $commit = new Process(['git', '-C', $repoRoot, 'commit', '-m', $msg]);
            $commit->run();
            if (!$commit->isSuccessful()) {
                $out = $commit->getOutput() . $commit->getErrorOutput();
                if (stripos($out, 'nothing to commit') !== false) {
                    return 'Already in repo; no commit needed.';
                }
                Log::warning('git commit failed: ' . $out);
                return 'Git commit failed; change saved locally.';
            }

            // Use the repo's configured credential helper (set to read /var/www/.git-credentials)
            // rather than overriding with an inline -c, which would shadow that path and prompt for tty.
            $push = new Process(['git', '-C', $repoRoot, 'push', 'origin', 'main']);
            $push->setTimeout(30);
            $push->run();
            if (!$push->isSuccessful()) {
                Log::warning('git push failed: ' . $push->getErrorOutput());
                return 'Committed locally but push failed; sync manually.';
            }
            return 'Synced to repo.';
        } catch (\Throwable $e) {
            Log::warning('exclude-server git op threw: ' . $e->getMessage());
            return 'Change saved on disk; git sync errored.';
        }
    }

    public function destroy(Punishment $punishment)
    {
        $punishment->delete();

        return redirect()->back()->with('success', 'Ban has been deleted');
    }
}
