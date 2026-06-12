<?php

declare(strict_types=1);

/** Échappe pour l'affichage HTML */
function e(mixed $v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** URL absolue */
function url(string $path = ''): string
{
    $base = rtrim(defined('BASE_URL') ? BASE_URL : '', '/');
    return $base . '/' . ltrim($path, '/');
}

/** Asset URL */
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Formatage monétaire XAF */
function money(float|int|string $amount, string $symbol = 'FCFA'): string
{
    return number_format((float)$amount, 0, ',', ' ') . ' ' . $symbol;
}

/** Formatage de date */
function dateFr(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '-';
    return (new \DateTime($date))->format($format);
}

/** Badges statut */
function badgeStatutCF(string $s): string
{
    $m = ['en_attente'=>['bg-amber-100 text-amber-800','En attente'],'validee'=>['bg-blue-100 text-blue-800','Validée'],'recue'=>['bg-emerald-100 text-emerald-800','Reçue'],'annulee'=>['bg-rose-100 text-rose-800','Annulée']];
    [$c,$l] = $m[$s] ?? ['bg-slate-100 text-slate-600',$s];
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '.$c.'">'.e($l).'</span>';
}

function badgeStatutCC(string $s): string
{
    $m = ['en_attente'=>['bg-amber-100 text-amber-800','En attente'],'validee'=>['bg-blue-100 text-blue-800','Validée'],'livree'=>['bg-emerald-100 text-emerald-800','Livrée'],'annulee'=>['bg-rose-100 text-rose-800','Annulée']];
    [$c,$l] = $m[$s] ?? ['bg-slate-100 text-slate-600',$s];
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '.$c.'">'.e($l).'</span>';
}

function badgePaiement(string $s): string
{
    $m = ['impayee'=>['bg-rose-100 text-rose-700','Impayée'],'partielle'=>['bg-amber-100 text-amber-700','Partielle'],'soldee'=>['bg-emerald-100 text-emerald-700','Soldée']];
    [$c,$l] = $m[$s] ?? ['bg-slate-100 text-slate-600',$s];
    return '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium '.$c.'">'.e($l).'</span>';
}

function paginationLinks(int $cur, int $total, string $base): string
{
    if ($total <= 1) return '';
    $html = '<nav class="flex items-center gap-1 mt-4">';
    for ($i = 1; $i <= $total; $i++) {
        $a = $i === $cur ? 'bg-violet-600 text-white' : 'bg-white text-slate-600 hover:bg-slate-100';
        $html .= '<a href="'.e($base.'?page='.$i).'" class="px-3 py-1.5 rounded text-sm font-medium border border-slate-200 '.$a.'">'.$i.'</a>';
    }
    return $html . '</nav>';
}

function csrfField(): string
{
    return '<input type="hidden" name="_csrf" value="'.e($_SESSION['_csrf'] ?? '').'">';
}

function flash(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}
