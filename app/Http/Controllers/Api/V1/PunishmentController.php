<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Punishment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PunishmentController extends Controller
{
    /**
     * Keyword → category mapping. Used to derive a short human category from the
     * matches list. Each entry is (substring → category). First match wins per
     * trigger; the controller picks the most-frequent category across all
     * matches for the final reason line.
     */
    private const CATEGORY_RULES = [
        // Public proxy / VPN / tunneling gateway (ToS §3.2)
        'sing-box' => 'public proxy gateway',
        'singbox' => 'public proxy gateway',
        'xtls-rprx-vision' => 'public proxy gateway',
        'reality-keypair' => 'public proxy gateway',
        'reality-in' => 'public proxy gateway',
        'hy2-in' => 'public proxy gateway',
        'tuic-in' => 'public proxy gateway',
        'vless-argo-in' => 'public proxy gateway',
        'hysteria2://' => 'public proxy gateway',
        'tuic://' => 'public proxy gateway',
        'vless://' => 'public proxy gateway',
        'vmess://' => 'public proxy gateway',
        'hy2://' => 'public proxy gateway',
        'trojan://' => 'public proxy gateway',
        'eooce' => 'public proxy gateway',
        'nodejs-sb' => 'public proxy gateway',
        'nodejs-argo' => 'public proxy gateway',
        'argosbx' => 'public proxy gateway',
        'NEZHA_KEY' => 'public proxy gateway',
        'ssss.nyc.mn' => 'public proxy gateway',
        'saas.sin.fan' => 'public proxy gateway',
        'singbox_data' => 'public proxy gateway',
        'agsbx' => 'public proxy gateway',
        'sbxProcess' => 'public proxy gateway',
        'geosite.dat' => 'public proxy gateway',

        // Cryptocurrency mining (ToS §3.1)
        'xmrig' => 'cryptocurrency mining',
        'xmr-stak' => 'cryptocurrency mining',
        'phoenixminer' => 'cryptocurrency mining',
        'lolminer' => 'cryptocurrency mining',
        'nbminer' => 'cryptocurrency mining',
        'nanominer' => 'cryptocurrency mining',
        'teamredminer' => 'cryptocurrency mining',
        'minerd' => 'cryptocurrency mining',
        'cryptonight' => 'cryptocurrency mining',
        'stratum+' => 'cryptocurrency mining',
        'ethminer' => 'cryptocurrency mining',
        'gminer' => 'cryptocurrency mining',
        'cpuminer' => 'cryptocurrency mining',
        'cgminer' => 'cryptocurrency mining',
        'bfgminer' => 'cryptocurrency mining',
        'sgminer' => 'cryptocurrency mining',

        // Command & control (ToS §1.2)
        'cobaltstrike' => 'command and control (C2)',
        'cobalt strike' => 'command and control (C2)',
        'cobalt-strike' => 'command and control (C2)',
        'meterpreter' => 'command and control (C2)',
        'sliver-implant' => 'command and control (C2)',
        'sliver-server' => 'command and control (C2)',
        'sliver-client' => 'command and control (C2)',
        'empire-agent' => 'command and control (C2)',
        'beacon.exe' => 'command and control (C2)',
        'beacon_dll' => 'command and control (C2)',

        // DDoS / DoS / flooders (ToS §1.1)
        'tcp_flood' => 'DDoS tooling',
        'tcp-flood' => 'DDoS tooling',
        'udp_flood' => 'DDoS tooling',
        'udp-flood' => 'DDoS tooling',
        'syn_flood' => 'DDoS tooling',
        'syn-flood' => 'DDoS tooling',
        'http_flood' => 'DDoS tooling',
        'http-flood' => 'DDoS tooling',
        'paping' => 'DDoS tooling',
        'ddos_attack' => 'DDoS tooling',
        'ddos-attack' => 'DDoS tooling',
        'attack_layer7' => 'DDoS tooling',
        'attack_layer4' => 'DDoS tooling',
        'slowloris' => 'DDoS tooling',
        'fraggle' => 'DDoS tooling',

        // Crashers (ToS §1.3)
        'PermenMD' => 'Minecraft crasher',
        'permenmd' => 'Minecraft crasher',

        // Network scanning (ToS §1.4)
        'masscan' => 'network scanning',
        'zmap' => 'network scanning',
        'nmap' => 'network scanning',

        // Credential abuse (ToS §2.1)
        'hashcat' => 'credential cracking',
        'sqlmap' => 'credential cracking',

        // Spam / mass-messaging (ToS §4.1)
        'fca-unofficial' => 'Facebook mass-messaging spam',
        'appstate.json' => 'Facebook mass-messaging spam',
    ];

    /**
     * POST /api/v1/punishments
     *
     * Create a ban (or other punishment) with detailed match metadata. Used by
     * the on-node abuse checkers (check_ddos-content.py, check_ddos-filenames.py)
     * to replace the old flow of POST /orders/{id}/suspend + a direct Discord
     * webhook. Idempotent by metadata.server_uuid in the last 24h.
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'type' => 'sometimes|string|in:ban,warning',
            'source' => 'required|string|max:50',
            'external_reference' => 'required|string|max:100',
            'metadata' => 'required|array',
            'metadata.scanner' => 'sometimes|string',
            'metadata.node' => 'sometimes|string',
            'metadata.server_uuid' => 'sometimes|string',
            'metadata.short_uuid' => 'sometimes|string',
            'metadata.matches' => 'sometimes|array',
            'metadata.matches.*.type' => 'sometimes|string|in:content,filename',
            'metadata.matches.*.keyword' => 'sometimes|string',
            'metadata.matches.*.path' => 'sometimes|string',
            'metadata.matches.*.line' => 'sometimes|integer',
            'suspend_all_orders' => 'sometimes|boolean',
            'terminate_all_orders' => 'sometimes|boolean',
            'expires_at' => 'sometimes|nullable|date',
        ]);

        $user = User::find($payload['user_id']);

        // Idempotency: any active ban for the same external_reference short-circuits
        // creation, regardless of age. The previous 24h window let the on-node cron
        // create a duplicate ban whenever it ran more than 24h after the original
        // (Track B then only unbanned one of the two, leaving the user banned).
        $existing = Punishment::where('external_reference', $payload['external_reference'])
            ->where('user_id', $user->id)
            ->where('type', 'ban')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', Carbon::now());
            })
            ->latest('created_at')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'data' => [
                    'punishment_id' => $existing->id,
                    'deduped' => true,
                    'deduped_against_created_at' => $existing->created_at?->toIso8601String(),
                    'suspended_count' => 0,
                    'terminated_count' => 0,
                ],
            ], 200);
        }

        // Derive category + a rich reason that mirrors what the old Discord
        // notification used to dump.
        $category = $this->deriveCategory($payload['metadata']['matches'] ?? []);
        $reason = $this->composeReason($category, $payload['metadata']);

        // Second-strike check: if this user previously received a Track B
        // amnesty for an abuse_checker punishment, the current detection is
        // their final one. Mark the punishment so downstream consumers (the
        // form 13 middleware, the suspended page, admin tools) can enforce
        // "no further appeals" automatically.
        $previousAmnesty = Punishment::where('user_id', $user->id)
            ->where('source', 'like', 'abuse_checker_%')
            ->whereJsonContains('metadata->amnesty_granted', true)
            ->exists();

        // Save category back into metadata so the suspended page and other
        // consumers can show it without re-running deriveCategory.
        $metadataWithCategory = array_merge($payload['metadata'], ['category' => $category]);
        if ($previousAmnesty) {
            $metadataWithCategory['second_strike'] = true;
            $reason = "Final ban (second detection after one-time amnesty). " . $reason;
        }

        $punishment = $user->punish([
            'type' => $payload['type'] ?? 'ban',
            'reason' => $reason,
            'expiry_date' => $payload['expires_at'] ?? null,
            'metadata' => $metadataWithCategory,
            'source' => $payload['source'],
            'external_reference' => $payload['external_reference'],
            // staff_id falls back to null (system-issued) when no authed user
            'staff_id' => auth()->user()?->id,
        ]);

        $suspendedIds = [];
        $terminatedIds = [];

        if ($payload['terminate_all_orders'] ?? false) {
            $terminatedIds = $user->terminateAllOrders();
        } elseif ($payload['suspend_all_orders'] ?? false) {
            $suspendedIds = $user->suspendAllOrders();
        }

        // Persist which orders this punishment touched so the admin cascade
        // unban knows exactly which orders to consider re-activating (and can
        // ignore unrelated suspensions on the same user account).
        if ($suspendedIds || $terminatedIds) {
            $metadataWithCategory['suspended_order_ids'] = $suspendedIds;
            $metadataWithCategory['terminated_order_ids'] = $terminatedIds;
            $punishment->metadata = $metadataWithCategory;
            $punishment->saveQuietly();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'punishment_id' => $punishment->id,
                'deduped' => false,
                'category' => $category,
                'suspended_count' => count($suspendedIds),
                'terminated_count' => count($terminatedIds),
            ],
        ], 201);
    }

    /**
     * Return the dominant category given a list of matches.
     */
    private function deriveCategory(array $matches): string
    {
        if (empty($matches)) {
            return 'unspecified abuse';
        }

        $counts = [];
        foreach ($matches as $m) {
            $kw = $m['keyword'] ?? '';
            if ($kw === '') {
                continue;
            }
            $cat = $this->categoryForKeyword($kw);
            $counts[$cat] = ($counts[$cat] ?? 0) + 1;
        }

        if (empty($counts)) {
            return 'unspecified abuse';
        }

        arsort($counts);
        return array_key_first($counts);
    }

    private function categoryForKeyword(string $keyword): string
    {
        // Normalize separators so variants like 'udp flood', 'udp-flood', and
        // 'udp.flood' all match the canonical 'udp_flood' rule.
        $kwNorm = strtolower(preg_replace('/[-_. ]/', '_', $keyword));
        foreach (self::CATEGORY_RULES as $needle => $cat) {
            $needleNorm = strtolower(preg_replace('/[-_. ]/', '_', $needle));
            if (str_contains($kwNorm, $needleNorm)) {
                return $cat;
            }
        }
        return 'unspecified abuse';
    }

    /**
     * Build a human-readable reason string with the same level of detail the
     * checker used to dump into Discord: scanner, node, server_uuid, and an
     * itemized list of matches with paths.
     */
    private function composeReason(string $category, array $metadata): string
    {
        $lines = ["Automated detection: {$category}."];

        $scanner = $metadata['scanner'] ?? null;
        $node = $metadata['node'] ?? null;
        $serverUuid = $metadata['server_uuid'] ?? null;
        $shortUuid = $metadata['short_uuid'] ?? null;

        $context = [];
        if ($scanner) {
            $context[] = "scanner={$scanner}";
        }
        if ($node) {
            $context[] = "node={$node}";
        }
        if ($shortUuid) {
            $context[] = "server={$shortUuid}";
        } elseif ($serverUuid) {
            $context[] = "server={$serverUuid}";
        }
        if ($context) {
            $lines[] = '(' . implode(', ', $context) . ')';
        }

        $matches = $metadata['matches'] ?? [];
        if (!empty($matches)) {
            $lines[] = '';
            $lines[] = 'Triggers:';
            foreach ($matches as $m) {
                $type = $m['type'] ?? '?';
                $kw = $m['keyword'] ?? '?';
                $path = $m['path'] ?? '?';
                $line = isset($m['line']) ? ":{$m['line']}" : '';
                $lines[] = "  - {$type} `{$kw}` in `{$path}{$line}`";
            }
        }

        return implode("\n", $lines);
    }
}
