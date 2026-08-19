<?php

declare(strict_types=1);

namespace App\Services;

final class PlanilhaService
{
    public function ler(string $caminho, string $extensao): array
    {
        $extensao = strtolower($extensao);

        if ($extensao === 'xlsx') {
            $linhas = $this->lerXlsx($caminho);
        } elseif ($extensao === 'csv') {
            $linhas = $this->lerCsv($caminho);
        } else {
            throw new \RuntimeException('Formato não suportado. Envie um arquivo .xlsx ou .csv.');
        }

        return $this->normalizarColunas($linhas);
    }

    private function lerXlsx(string $caminho): array
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \RuntimeException('Extensão ZIP não está disponível no servidor.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($caminho) !== true) {
            throw new \RuntimeException('Não foi possível abrir o arquivo .xlsx.');
        }

        $shared = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $doc = new \SimpleXMLElement($sharedXml);
            foreach ($doc->si as $si) {
                $shared[] = $this->nodeTexto($si);
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($sheetXml === false) {
            throw new \RuntimeException('Nenhuma aba com dados encontrada no arquivo.');
        }

        $doc = new \SimpleXMLElement($sheetXml);
        $linhas = [];
        foreach ($doc->sheetData->row as $row) {
            $linha = [];
            foreach ($row->c as $cell) {
                $ref = (string) $cell['r'];
                $coluna = preg_replace('/\d+/', '', $ref) ?: 'A';
                $idx = $this->colunaParaIndice($coluna);
                $tipo = (string) $cell['t'];
                $v = trim((string) $cell->v);

                if ($tipo === 's') {
                    $valor = $shared[(int) $v] ?? '';
                } elseif ($tipo === 'inlineStr' && isset($cell->is)) {
                    $valor = $this->nodeTexto($cell->is);
                } else {
                    $valor = $v;
                }

                $linha[$idx] = $valor;
            }
            $linhas[] = $linha;
        }

        return $linhas;
    }

    private function lerCsv(string $caminho): array
    {
        $handle = fopen($caminho, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Não foi possível ler o arquivo CSV.');
        }

        $primeira = fgetcsv($handle, 0, ';');
        $delimitador = (is_array($primeira) && count($primeira) > 1) ? ';' : ',';
        fclose($handle);

        $handle = fopen($caminho, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Não foi possível ler o arquivo CSV.');
        }

        $linhas = [];
        while (($linha = fgetcsv($handle, 0, $delimitador)) !== false) {
            $linhas[] = array_map(
                static fn ($valor): string => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $valor)),
                $linha
            );
        }
        fclose($handle);

        return $linhas;
    }

    private function normalizarColunas(array $linhas): array
    {
        if (empty($linhas)) {
            return [];
        }

        $cabecalhoIdx = null;
        foreach ($linhas as $i => $linha) {
            if ($this->linhaTemConteudo($linha)) {
                $cabecalhoIdx = $i;
                break;
            }
        }

        if ($cabecalhoIdx === null) {
            return [];
        }

        $cabecalho = array_map(
            static fn ($c): string => strtolower(trim((string) $c)),
            $linhas[$cabecalhoIdx]
        );

        $mapa = ['nome' => null, 'telefone' => null, 'email' => null];
        foreach ($cabecalho as $idx => $nomeColuna) {
            if (in_array($nomeColuna, ['nome', 'name', 'aluno', 'nome completo'], true)) {
                $mapa['nome'] = $idx;
            } elseif (in_array($nomeColuna, ['telefone', 'tel', 'phone', 'telefone/whatsapp', 'whatsapp'], true)) {
                $mapa['telefone'] = $idx;
            } elseif (in_array($nomeColuna, ['email', 'e-mail', 'mail', 'email:', 'e-mail:'], true)) {
                $mapa['email'] = $idx;
            }
        }

        $reconhecido = $mapa['nome'] !== null && $mapa['email'] !== null;
        $resultado = [];

        for ($i = $cabecalhoIdx + 1, $total = count($linhas); $i < $total; $i++) {
            $linha = $linhas[$i];
            if (!$this->linhaTemConteudo($linha)) {
                continue;
            }

            if ($reconhecido) {
                $resultado[] = [
                    'nome' => trim((string) ($linha[$mapa['nome']] ?? '')),
                    'telefone' => trim((string) ($linha[$mapa['telefone']] ?? '')),
                    'email' => trim((string) ($linha[$mapa['email']] ?? '')),
                ];
            } else {
                $resultado[] = [
                    'nome' => trim((string) ($linha[0] ?? '')),
                    'telefone' => trim((string) ($linha[1] ?? '')),
                    'email' => trim((string) ($linha[2] ?? '')),
                ];
            }
        }

        return $resultado;
    }

    private function nodeTexto(\SimpleXMLElement $el): string
    {
        $texto = '';
        foreach ($el->t as $t) {
            $texto .= (string) $t;
        }
        return trim($texto);
    }

    private function colunaParaIndice(string $coluna): int
    {
        $coluna = strtoupper($coluna);
        $idx = 0;
        $len = strlen($coluna);
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($coluna[$i]) - 64);
        }
        return $idx - 1;
    }

    private function linhaTemConteudo(array $linha): bool
    {
        foreach ($linha as $valor) {
            if (trim((string) $valor) !== '') {
                return true;
            }
        }
        return false;
    }
}