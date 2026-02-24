<?php

namespace App\Support;

class LibraryData
{
    private string $path;

    public function __construct(?string $path = null)
    {
        $this->path = $path ?? dirname(__DIR__, 2) . '/storage/app/library.json';

        if (! file_exists($this->path)) {
            $this->write($this->seed());
        }
    }

    public function all(): array
    {
        $content = file_get_contents($this->path);

        if (! $content) {
            return $this->seed();
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : $this->seed();
    }

    public function write(array $data): void
    {
        if (! is_dir(dirname($this->path))) {
            mkdir(dirname($this->path), 0777, true);
        }

        file_put_contents($this->path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function seed(): array
    {
        return [
            'settings' => ['maxBooks' => 3, 'loanDays' => 14],
            'users' => [
                ['id' => 1, 'name' => 'Admin', 'email' => 'admin@biblioteca.local', 'role' => 'admin', 'penaltyUntil' => null, 'lateReturns' => 0],
                ['id' => 2, 'name' => 'Ana Lectura', 'email' => 'ana@biblioteca.local', 'role' => 'user', 'penaltyUntil' => null, 'lateReturns' => 1],
            ],
            'books' => [
                ['id' => 1, 'title' => 'Cien años de soledad', 'author' => 'G. G. Márquez', 'available' => true, 'timesLoaned' => 4],
                ['id' => 2, 'title' => 'Don Quijote de la Mancha', 'author' => 'M. de Cervantes', 'available' => true, 'timesLoaned' => 3],
                ['id' => 3, 'title' => 'Rayuela', 'author' => 'J. Cortázar', 'available' => true, 'timesLoaned' => 2],
            ],
            'loans' => [],
            'penalties' => [],
        ];
    }
}
