<?php

declare(strict_types=1);

namespace App\Services\System;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Lightweight health checks for the services the platform depends on.
 * Each check is isolated so one failing dependency never breaks the page.
 *
 * @phpstan-type Check array{name: string, ok: bool, detail: string, ms: float}
 */
class SystemStatusService
{
    /**
     * @return list<Check>
     */
    public function checks(): array
    {
        return [
            $this->check('Base de dados', fn (): string => $this->database()),
            $this->check('Cache', fn (): string => $this->cache()),
            $this->check('Redis', fn (): string => $this->redis()),
            $this->check('Filas', fn (): string => $this->queue()),
            $this->check('Armazenamento', fn (): string => $this->storage()),
            $this->check('E-mail', fn (): string => $this->mail()),
            $this->check('API pública', fn (): string => $this->api()),
        ];
    }

    /**
     * @param  callable(): string  $probe
     * @return Check
     */
    private function check(string $name, callable $probe): array
    {
        $start = microtime(true);

        try {
            $detail = $probe();
            $ok = true;
        } catch (Throwable $e) {
            $detail = $e->getMessage();
            $ok = false;
        }

        return [
            'name' => $name,
            'ok' => $ok,
            'detail' => $detail,
            'ms' => round((microtime(true) - $start) * 1000, 1),
        ];
    }

    private function database(): string
    {
        DB::connection()->select('select 1');

        return 'Ligação ' . DB::connection()->getName() . ' ok';
    }

    private function cache(): string
    {
        Cache::put('vaga:health', '1', 5);

        return Cache::get('vaga:health') === '1'
            ? 'Escrita/leitura ok (' . config('cache.default') . ')'
            : throw new \RuntimeException('Leitura da cache falhou');
    }

    private function redis(): string
    {
        /** @var mixed $pong */
        $pong = Redis::connection()->ping();

        return 'PING ' . (is_string($pong) ? $pong : 'PONG');
    }

    private function queue(): string
    {
        return 'Driver: ' . config('queue.default');
    }

    private function storage(): string
    {
        $disk = Storage::disk(config('filesystems.default'));
        $disk->put('vaga-health.txt', (string) now());
        $disk->delete('vaga-health.txt');

        return 'Disco "' . config('filesystems.default') . '" gravável';
    }

    private function mail(): string
    {
        return 'Mailer: ' . config('mail.default');
    }

    private function api(): string
    {
        return app('router')->has('api.auth.login')
            ? 'Rotas /api/v1 registadas'
            : throw new \RuntimeException('Rotas da API em falta');
    }
}
