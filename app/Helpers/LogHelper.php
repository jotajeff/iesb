<?php

declare(strict_types=1);

namespace App\Helpers;

final class LogHelper
{
    /**
     * @return array{icon: string, color: string}
     */
    public static function acao(string $acao): array
    {
        return match (strtolower(trim($acao))) {
            'login' => ['icon' => 'bi-box-arrow-in-right', 'color' => 'text-primary'],
            'logout' => ['icon' => 'bi-box-arrow-left', 'color' => 'text-secondary'],
            'create', 'cadastro', 'cadastrar', 'criar' => ['icon' => 'bi-plus-circle', 'color' => 'text-success'],
            'update', 'editar', 'alterar' => ['icon' => 'bi-pencil-square', 'color' => 'text-warning-emphasis'],
            'delete', 'remover', 'excluir' => ['icon' => 'bi-trash', 'color' => 'text-danger'],
            'view', 'visualizar' => ['icon' => 'bi-eye', 'color' => 'text-info'],
            'matricular', 'matricula' => ['icon' => 'bi-journal-plus', 'color' => 'text-success'],
            'restaurar_senha', 'restaurar senha' => ['icon' => 'bi-key', 'color' => 'text-warning'],
            'trocar_turma', 'troca' => ['icon' => 'bi-arrow-left-right', 'color' => 'text-info-emphasis'],
            'gerar_boleto', 'boleto' => ['icon' => 'bi-receipt', 'color' => 'text-primary'],
            'upload_imagem', 'imagem' => ['icon' => 'bi-cloud-upload', 'color' => 'text-primary'],
            'atualizar', 'atualizar' => ['icon' => 'bi-repeat', 'color' => 'text-primary'],
            'redefinir_senha', 'redefinir senha' => ['icon' => 'bi-lock', 'color' => 'text-primary'],
            default => ['icon' => 'bi-record-circle', 'color' => 'text-muted'],
        };
    }

    public static function render(string $acao): string
    {
        $data = self::acao($acao);
        return sprintf(
            '<span class="%s"><i class="bi %s me-1"></i>%s</span>',
            $data['color'],
            $data['icon'],
            htmlspecialchars($acao, ENT_QUOTES, 'UTF-8')
        );
    }
}
