<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - StockManager</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('fontawesome/css/all.min.css') ?>">
    <script src="<?= asset('fontawesome/js/all.min.js') ?>"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4">

    <!-- Fond décoratif grille -->
    <div class="fixed inset-0 bg-[linear-gradient(rgba(124,58,237,0.04)_1px,transparent_1px),linear-gradient(90deg,rgba(124,58,237,0.04)_1px,transparent_1px)] bg-[size:32px_32px] pointer-events-none"></div>

    <div class="relative w-full max-w-md">

        <!-- Logo / En-tête -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-violet-600 shadow-lg shadow-violet-900/50 mb-4">
                <i class="fa-solid fa-boxes-stacking text-2xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">StockManager</h1>
            <p class="text-slate-400 text-sm mt-1">Gestion de stock - Accès sécurisé</p>
        </div>

        <!-- Messages flash -->
        <?php foreach (flash() as $type => $messages): ?>
            <?php foreach ($messages as $msg): ?>
                <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-lg
                    <?= $type === 'error' ? 'bg-rose-500/10 border border-rose-500/30 text-rose-300' : 'bg-emerald-500/10 border border-emerald-500/30 text-emerald-300' ?>">
                    <i class="fa-solid <?= $type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> mt-0.5 flex-shrink-0"></i>
                    <span class="text-sm"><?= e($msg) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <!-- Carte de connexion -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl p-8">
            <h2 class="text-lg font-semibold text-white mb-6">Connexion à votre espace</h2>

            <form method="POST" action="<?= url('login') ?>" novalidate>
                <?= csrfField() ?>

                <!-- Login -->
                <div class="mb-5">
                    <label for="login" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Identifiant
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="fa-solid fa-user text-slate-500 text-sm"></i>
                        </span>
                        <input
                            type="text"
                            id="login"
                            name="login"
                            value="<?= e($_POST['login'] ?? '') ?>"
                            autocomplete="username"
                            autofocus
                            required
                            placeholder="Votre identifiant"
                            class="w-full bg-slate-700 border border-slate-600 text-white placeholder-slate-500
                                    rounded-lg pl-10 pr-4 py-2.5 text-sm
                                    focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                    transition"
                        >
                    </div>
                </div>

                <!-- Mot de passe -->
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">
                        Mot de passe
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <i class="fa-solid fa-lock text-slate-500 text-sm"></i>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
                            class="w-full bg-slate-700 border border-slate-600 text-white placeholder-slate-500
                                   rounded-lg pl-10 pr-10 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent
                                   transition"
                        >
                        <!-- Toggle visibilité -->
                        <button type="button" id="togglePwd"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-500 hover:text-slate-300 transition">
                            <i class="fa-solid fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Bouton -->
                <button type="submit"
                    class="w-full bg-violet-600 hover:bg-violet-500 active:bg-violet-700
                           text-white font-semibold py-2.5 px-4 rounded-lg text-sm
                           transition focus:outline-none focus:ring-2 focus:ring-violet-400 focus:ring-offset-2 focus:ring-offset-slate-800
                           flex items-center justify-center gap-2">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    Se connecter
                </button>
            </form>
        </div>

        <!-- Pied de page -->
        <p class="text-center text-slate-600 text-xs mt-6">
            StockManager &copy; <?= date('Y') ?> - Accès restreint aux utilisateurs autorisés
        </p>
    </div>

    <script>
        const toggle = document.getElementById('togglePwd');
        const pwd    = document.getElementById('password');
        const icon   = document.getElementById('eyeIcon');
        toggle.addEventListener('click', () => {
            const show = pwd.type === 'password';
            pwd.type   = show ? 'text' : 'password';
            icon.className = show ? 'fa-solid fa-eye-slash text-sm' : 'fa-solid fa-eye text-sm';
        });
    </script>
</body>
</html>
