<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $table = 'api_keys';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'api_version',
        'description',
        'secret',
        'permissions',
        'allowed_ips',
        'last_used_at',
        'expires_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<int, string>
     */
    protected $casts = [
        'permissions' => 'collection',
        'allowed_ips' => 'collection',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'secret',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hash a value using sha256 and a salt
     *
     * @param  string  $value
     * @return string
     */
    public static function hash($value)
    {
        // Obtener el salt con prioridad: .env > BD > generar nuevo
        $salt = self::getSalt();

        return hash('sha256', $value . $salt);
    }

    /**
     * Get salt with priority: .env > database > generate new
     * 
     * @return string
     */
    protected static function getSalt(): string
    {
        // Prioridad 1: Salt del archivo .env (encriptado)
        $envSalt = config('app.api_salt_encrypted');
        if (!empty($envSalt)) {
            // Si el salt del .env está encriptado, desencriptarlo
            try {
                if (str_starts_with($envSalt, 'eyJ')) {
                    return decrypt($envSalt);
                }
                return $envSalt;
            } catch (\Exception $e) {
                \Log::error('Failed to decrypt API salt from .env: ' . $e->getMessage());
            }
        }
        
        // Prioridad 2: Salt de la base de datos
        if (Settings::has('encrypted::salt')) {
            return settings('encrypted::salt'); // Esto ya lo desencripta automáticamente
        }
        
        // Prioridad 3: Generar nuevo salt solo si no existe en ningún lado
        \Log::warning('API Salt not found in .env or database. Generating new salt.');
        self::generateSalt();
        
        return settings('encrypted::salt');
    }
    
    /**
     * Generate salt for enhanced security.
     * Solo se ejecuta si no existe salt en .env ni en BD
     */
    protected static function generateSalt(): void
    {
        if (!Settings::has('encrypted::salt')) {
            $salt = Str::random(48);
            Settings::put('encrypted::salt', $salt);
            \Log::info('Generated new API salt. Consider adding it to .env for stability.');
        }
    }

    /**
     * Check if a given permission is allowed for this API key
     */
    public function hasPerm(string $permission): bool
    {
        if ($this->full_permissions) {
            return true;
        }

        if (empty($this->permissions)) {
            return false;
        }

        return $this->permissions->contains($permission);
    }

    /**
     * Email user when unauthorized access is attempted
     */
    public function unauthorizedIP(): void
    {
        $this->user->email([
            'subject' => 'Unauthorized IP address attempted to access your API key',
            'content' => 'An unauthorized IP address attempted to access your API key. <br><br>
            
            <strong>IP Address:</strong> ' . request()->ip() . '<br>
            <strong>API ID:</strong> ' . $this->id . '<br>
            <strong>API Key:</strong> ' . $this->description . '<br>
            <strong>User Agent:</strong> ' . request()->userAgent() . '<br>
            <strong>Time:</strong> ' . Carbon::now()->toDateTimeString() . '<br>
            <strong>URL:</strong> ' . request()->fullUrl() . '<br>
            ',
        ]);
    }
}
