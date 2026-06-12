<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur <?= $code ?? 500 ?> - StockManager</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
    <link rel="stylesheet" href="<?= asset('fontawesome/css/all.min.css') ?>">
    <script src="<?= asset('fontawesome/js/all.min.js') ?>"></script>
</head>
<body class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
<div class="text-center max-w-md">
    <div class="text-7xl font-black text-slate-700 mb-2"><?= $code ?? 500 ?></div>
    <div class="text-xl font-semibold text-white mb-2"><?= e($title ?? 'Erreur') ?></div>
    <?php if (!empty($message)): ?>
    <p class="text-slate-400 text-sm mb-6"><?= e($message) ?></p>
    <?php endif; ?>
    <a href="<?= url('dashboard') ?>"
        class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
        <i class="fa-solid fa-house"></i> Retour au tableau de bord
    </a>
</div>
</body>
</html>
