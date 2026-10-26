<?php

declare(strict_types=1);

namespace App\Services;

use SimpleXMLElement;

class XlsxReader
{
    private const NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';


    private array $sharedStrings = [];


    private array $rows = [];

    private string $extractDir;



    public function __construct(string $path, private readonly int $sheetIndex = 0)
    {
        $this->extractDir = sys_get_temp_dir().'/xlsx_'.bin2hex(random_bytes(16));

        $this->extract($path);
        $this->loadSharedStrings();
        $this->loadSheet($sheetIndex);
    }


    private function extract(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new \RuntimeException('فایل اکسل قابل خواندن نیست.');
        }

        if (!class_exists(\PharData::class)) {
            throw new \RuntimeException('افزونهٔ Phar برای خواندن فایل اکسل در دسترس نیست.');
        }

        if (!is_dir($this->extractDir)) {
            mkdir($this->extractDir, 0700, true);
        }






        $zipCopy = $this->extractDir . '/source.zip';
        copy($path, $zipCopy);

        $phar = new \PharData($zipCopy);
        self::extractAll($phar, $this->extractDir);
    }

    private static function extractAll(\PharData $phar, string $targetDir): void
    {



        try {
            $phar->extractTo($targetDir, null, true);
        } catch (\Throwable $e) {
            throw new \RuntimeException('فایل اکسل قابل استخراج نیست: ' . $e->getMessage(), 0, $e);
        }
    }


    private function loadSharedStrings(): void
    {
        $file = $this->extractDir.'/xl/sharedStrings.xml';
        if (!is_file($file)) {
            return;
        }

        $xml = @simplexml_load_file($file);
        if ($xml === false) {
            return;
        }

        foreach ($xml->si as $si) {
            $text = '';
            foreach ($si->t as $t) {
                $text .= (string) $t;
            }
            foreach ($si->r as $r) {
                foreach ($r->t as $t) {
                    $text .= (string) $t;
                }
            }
            $this->sharedStrings[] = $text;
        }
    }


    private function loadSheet(int $index): void
    {
        $file = $this->extractDir.'/xl/worksheets/sheet'.($index + 1).'.xml';

        if (!is_file($file)) {

            $files = glob($this->extractDir.'/xl/worksheets/sheet*.xml') ?: [];
            sort($files);
            $file = $files[$index] ?? null;
            if ($file === null) {
                throw new \RuntimeException('برگهٔ اکسل یافت نشد.');
            }
        }

        $xml = @simplexml_load_file($file);
        if ($xml === false) {
            throw new \RuntimeException('محتوای برگهٔ اکسل قابل خواندن نیست.');
        }

        foreach ($xml->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $ref = (string) ($c['r'] ?? '');
                $col = preg_replace('/[0-9]/', '', $ref) ?: 'A';
                $idx = $this->colToIndex($col);
                $type = (string) ($c['t'] ?? 'n');
                $value = '';

                $inline = $c->is ?? null;
                if ($inline !== null) {
                    foreach ($inline->t as $t) {
                        $value .= (string) $t;
                    }
                    foreach ($inline->r as $r) {
                        foreach ($r->t as $t) {
                            $value .= (string) $t;
                        }
                    }
                } elseif (isset($c->v)) {
                    $raw = (string) $c->v;
                    if ($type === 's') {
                        $value = (string) ($this->sharedStrings[(int) $raw] ?? '');
                    } else {
                        $value = $raw;
                    }
                }

                $cells[$idx] = $value;
            }

            if (count($cells) > 0) {
                ksort($cells);
                $this->rows[] = array_values($cells);
            }
        }
    }


    private function colToIndex(string $col): int
    {
        $col = strtoupper($col);
        $result = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $result = $result * 26 + (ord($col[$i]) - 64);
        }

        return $result - 1;
    }


    public function rows(): array
    {
        return $this->rows;
    }


    public function dataRows(bool $skipHeader = true): array
    {
        return $skipHeader ? array_slice($this->rows, 1) : $this->rows;
    }

    public function headerRow(): ?array
    {
        return $this->rows[0] ?? null;
    }

    public function __destruct()
    {
        if (isset($this->extractDir) && is_dir($this->extractDir)) {
            $this->removeDir($this->extractDir);
        }
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
