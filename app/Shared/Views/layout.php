<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? $_pageTitle ?? 'StockManager') ?> - StockManager</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <script src="<?= asset('fontawesome/js/all.min.js') ?>"></script>
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased">

<!-- SIDEBAR -->
<aside id="sidebar"
    class="fixed top-0 left-0 h-screen w-60 bg-slate-900 flex flex-col z-30
           transition-transform duration-300 lg:translate-x-0 -translate-x-full"
    aria-label="Navigation principale">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800">
        <div class="flex-shrink-0 w-9 h-9 rounded-xl bg-violet-600 flex items-center justify-center shadow-lg shadow-violet-900/50">
            <i class="fa-solid fa-boxes-stacking text-base text-white"></i>
        </div>
        <div>
            <span class="font-bold text-white text-base leading-tight block">StockManager</span>
            <span class="text-slate-500 text-[10px] uppercase tracking-widest">Gestion de stock</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-0.5" aria-label="Menu">

        <?php
        $currentUri = strtok($_SERVER['REQUEST_URI'], '?');
        // Retirer le préfixe sous-dossier
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptDir !== '/' && str_starts_with($currentUri, $scriptDir)) {
            $currentUri = substr($currentUri, strlen($scriptDir));
        }

        function navItem(string $href, string $icon, string $label, string $currentUri): string {
            $active = str_starts_with($currentUri, $href) && $href !== '/';
            $cls = $active
                ? 'bg-slate-700 text-white'
                : 'text-slate-400 hover:bg-slate-800 hover:text-white';
            $dot = $active ? '<span class="w-1.5 h-1.5 rounded-full bg-violet-400 ml-auto"></span>' : '';
            return '<a href="' . url(ltrim($href,'/')) . '" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition ' . $cls . '">'
                 . '<i class="' . $icon . ' w-4 text-center flex-shrink-0"></i>'
                 . '<span>' . $label . '</span>'
                 . $dot
                 . '</a>';
        }

        $droits = $_SESSION['droits'] ?? [];
        ?>

        <?= navItem('/dashboard', 'fa-solid fa-chart-line', 'Tableau de bord', $currentUri) ?>

        <?php if (!empty($droits['approvisionnement.consulter'])): ?>
        <div class="pt-3 pb-1 px-3">
            <span class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">Approvisionnement</span>
        </div>
        <?= navItem('/approvisionnement/commandes',  'fa-solid fa-file-lines',   'Commandes',   $currentUri) ?>
        <?= navItem('/approvisionnement/receptions', 'fa-solid fa-truck-ramp-box','Réceptions', $currentUri) ?>
        <?= navItem('/approvisionnement/factures',   'fa-solid fa-file-invoice', 'Factures',    $currentUri) ?>
        <?= navItem('/approvisionnement/dons',       'fa-solid fa-gift',         'Dons',        $currentUri) ?>
        <?php endif; ?>

        <?php if (!empty($droits['vente.consulter'])): ?>
        <div class="pt-3 pb-1 px-3">
            <span class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">Vente</span>
        </div>
        <?= navItem('/vente/commandes',   'fa-solid fa-cart-shopping',  'Commandes',    $currentUri) ?>
        <?= navItem('/vente/comptant',    'fa-solid fa-cash-register',  'Vente comptant',$currentUri) ?>
        <?= navItem('/vente/livraisons',  'fa-solid fa-truck',          'Livraisons',   $currentUri) ?>
        <?= navItem('/vente/factures',    'fa-solid fa-file-invoice-dollar','Factures', $currentUri) ?>
        <?= navItem('/vente/sorties',     'fa-solid fa-box-open',       'Bons de sortie',$currentUri) ?>
        <?php endif; ?>

        <?php if (!empty($droits['structure.consulter'])): ?>
        <div class="pt-3 pb-1 px-3">
            <span class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">Structure</span>
        </div>
        <?= navItem('/structure/produits',    'fa-solid fa-boxes-stacking', 'Produits',    $currentUri) ?>
        <?= navItem('/structure/familles',    'fa-solid fa-sitemap',        'Familles',    $currentUri) ?>
        <?= navItem('/structure/fournisseurs','fa-solid fa-industry',       'Fournisseurs',$currentUri) ?>
        <?= navItem('/structure/clients',     'fa-solid fa-users',          'Clients',     $currentUri) ?>
        <?= navItem('/structure/banques',     'fa-solid fa-building-columns','Banques',    $currentUri) ?>
        <?php endif; ?>

        <?php if (!empty($droits['securite.consulter'])): ?>
        <div class="pt-3 pb-1 px-3">
            <span class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">Administration</span>
        </div>
        <?= navItem('/utilisateurs/comptes',  'fa-solid fa-users-cog',      'Utilisateurs',$currentUri) ?>
        <?= navItem('/utilisateurs/groupes',  'fa-solid fa-shield-halved',  'Groupes & droits',$currentUri) ?>
        <?php endif; ?>

        <div class="pt-3 pb-1 px-3">
            <span class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">Système</span>
        </div>
        <?= navItem('/audit',   'fa-solid fa-clock-rotate-left', 'Journal d\'audit', $currentUri) ?>
        <?= navItem('/archive', 'fa-solid fa-trash-can',         'Corbeille',        $currentUri) ?>

    </nav>

    <!-- Utilisateur connecté -->
    <div class="border-t border-slate-800 px-4 py-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-violet-700 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                <?= strtoupper(substr($_SESSION['user_prenom'] ?? 'U', 0, 1) . substr($_SESSION['user_nom'] ?? '', 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-white truncate">
                    <?= e(($_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['user_nom'] ?? '')) ?>
                </div>
                <div class="text-xs text-slate-500 truncate">
                    <?= e($_SESSION['groupe_libelle'] ?? '') ?>
                </div>
            </div>
            <form method="POST" action="<?= url('logout') ?>" class="flex-shrink-0">
                <?= csrfField() ?>
                <button type="submit" title="Se déconnecter"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-500 hover:text-rose-400 hover:bg-slate-800 transition">
                    <i class="fa-solid fa-right-from-bracket text-sm"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- ═══════════════════════════════════════ OVERLAY mobile -->
<div id="overlay" class="fixed inset-0 bg-black/60 z-20 hidden lg:hidden" onclick="closeSidebar()"></div>

<!-- ═══════════════════════════════════════ ZONE PRINCIPALE -->
<div class="lg:ml-60 min-h-screen flex flex-col">

    <!-- ─── Header ─────────────────────────────────────── -->
    <header class="sticky top-0 z-10 bg-white border-b border-slate-200 px-4 sm:px-6 py-3 flex items-center gap-4">

        <!-- Burger mobile -->
        <button id="burgerBtn" onclick="openSidebar()"
            class="lg:hidden flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:bg-slate-100 transition">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Breadcrumb -->
        <nav class="flex-1 flex items-center gap-1.5 text-sm text-slate-500" aria-label="Fil d'ariane">
            <a href="<?= url('dashboard') ?>" class="hover:text-violet-600 transition">
                <i class="fa-solid fa-house text-xs"></i>
            </a>
            <?php if (!empty($_pageTitle)): ?>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-slate-800 font-medium"><?= e($_pageTitle) ?></span>
            <?php endif; ?>
        </nav>

        <!-- Actions header droite -->
        <div class="flex items-center gap-2">

            <!-- Alertes stock -->
            <?php
            try {
                $db = \App\Core\Database::getInstance();
                $alertes = $db->fetchOne(
                    "SELECT COUNT(*) AS nb FROM produit WHERE stock_actuel <= stock_alerte AND deleted_at IS NULL"
                );
                $nbAlertes = (int)($alertes['nb'] ?? 0);
            } catch (\Throwable $e) { $nbAlertes = 0; }
            ?>
            <?php if ($nbAlertes > 0): ?>
            <a href="<?= url('structure/produits?alerte=1') ?>"
                class="relative flex items-center justify-center w-9 h-9 rounded-lg text-amber-500 hover:bg-amber-50 transition"
                title="<?= $nbAlertes ?> produit(s) en alerte stock">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                    <?= $nbAlertes > 9 ? '9+' : $nbAlertes ?>
                </span>
            </a>
            <?php endif; ?>

            <!-- Date courante -->
            <span class="hidden sm:block text-xs text-slate-400 bg-slate-100 px-3 py-1.5 rounded-lg">
                <i class="fa-regular fa-calendar mr-1"></i>
                <?= (new DateTime())->format('d/m/Y') ?>
            </span>
        </div>
    </header>

    <!-- ─── Messages flash ──────────────────────────────── -->
    <?php $flashMessages = flash(); ?>
    <?php if (!empty($flashMessages)): ?>
    <div id="flash-container" class="px-4 sm:px-6 pt-4 space-y-2">
        <?php foreach ($flashMessages as $type => $msgs): ?>
            <?php foreach ($msgs as $msg): ?>
            <div class="flex items-start gap-3 px-4 py-3 rounded-xl text-sm
                <?= $type === 'error'   ? 'bg-rose-50 border border-rose-200 text-rose-700'
                  : ($type === 'success' ? 'bg-emerald-50 border border-emerald-200 text-emerald-700'
                  : 'bg-blue-50 border border-blue-200 text-blue-700') ?>">
                <i class="fa-solid <?= $type === 'error' ? 'fa-circle-exclamation' : ($type === 'success' ? 'fa-circle-check' : 'fa-circle-info') ?> mt-0.5 flex-shrink-0"></i>
                <span class="flex-1"><?= e($msg) ?></span>
                <button onclick="this.parentElement.remove()" class="flex-shrink-0 opacity-60 hover:opacity-100 transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ─── Contenu de la page ──────────────────────────── -->
    <main class="flex-1 px-4 sm:px-6 py-6">
        <?php
        // Résolution de la vue enfant
        $viewPath = $_view ?? '';
        if ($viewPath) {
            if (str_starts_with($viewPath, 'shared/')) {
                $childPath = BASE_PATH . '/app/Shared/Views/' . substr($viewPath, 7) . '.php';
            } else {
                [$mod, $tpl] = explode('/', $viewPath, 2);
                $childPath = BASE_PATH . '/app/Modules/' . $mod . '/Views/' . $tpl . '.php';
            }
            if (file_exists($childPath)) include $childPath;
        }
        ?>
    </main>

    <!-- ─── Footer ──────────────────────────────────────── -->
    <footer class="border-t border-slate-200 px-6 py-3 text-center text-xs text-slate-400">
        StockManager &copy; <?= date('Y') ?> - Tous droits réservés
    </footer>
</div>

<!-- ═══════════════════════════════ Scripts -->
<script>
function openSidebar() {
    document.getElementById('sidebar').classList.remove('-translate-x-full');
    document.getElementById('overlay').classList.remove('hidden');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('overlay').classList.add('hidden');
}

// Auto-dismiss flash après 5s
setTimeout(() => {
    document.querySelectorAll('#flash-container > div').forEach(el => {
        el.style.transition = 'opacity 0.4s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 400);
    });
}, 5000);
</script>

</body>
</html>
