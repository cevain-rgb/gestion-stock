<?php $mdpErrors = $mdpErrors ?? []; ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('utilisateurs/comptes') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= e($u['prenom'].' '.$u['nom']) ?></h1>
        <?php if ($u['actif']): ?>
            <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-medium">Actif</span>
        <?php else: ?>
            <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-medium">Inactif</span>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Infos -->
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-4">Informations</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Nom complet</dt><dd class="font-medium"><?= e($u['prenom'].' '.$u['nom']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Login</dt><dd class="font-mono"><?= e($u['login']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Groupe</dt><dd><span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-xs"><?= e($u['groupe_libelle']) ?></span></dd></div>
                <br>
                <!-- <div class="flex justify-between"><dt class="text-slate-500">Créé le</dt><dd><.?= dateFr($u['created_at']) ?></dd></div> -->
            </dl>
            <?php if (!empty($_SESSION['droits']['securite.modifier'])): ?>
            <div class="mt-4 pt-4 border-t border-slate-100 flex gap-2">
                <a href="<?= url('utilisateurs/comptes/'.$u['id_utilisateur'].'/edit') ?>" class="btn-secondary btn-sm">
                    <i class="fa-solid fa-pen"></i> Modifier
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Changer mot de passe -->
        <?php if (!empty($_SESSION['droits']['securite.modifier'])): ?>
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-4">Réinitialiser le mot de passe</h2>
            <form method="POST" action="<?= url('utilisateurs/comptes/'.$u['id_utilisateur'].'/mdp') ?>">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label text-xs">Nouveau mot de passe</label>
                    <input type="password" name="password" minlength="6" required
                        class="form-input <?= !empty($mdpErrors['password']) ? 'border-rose-400' : '' ?>">
                    <?php if (!empty($mdpErrors['password'])): ?><p class="form-error"><?= e($mdpErrors['password']) ?></p><?php endif; ?>
                </div>
                <div class="mb-4">
                    <label class="form-label text-xs">Confirmation</label>
                    <input type="password" name="password_confirm" required
                        class="form-input <?= !empty($mdpErrors['password_confirm']) ? 'border-rose-400' : '' ?>">
                    <?php if (!empty($mdpErrors['password_confirm'])): ?><p class="form-error"><?= e($mdpErrors['password_confirm']) ?></p><?php endif; ?>
                </div>
                <button type="submit" class="btn-primary btn-sm w-full">
                    <i class="fa-solid fa-key"></i> Changer le mot de passe
                </button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <!-- Historique connexions -->
    <div class="card overflow-hidden">
        <div class="card-header">
            <h2 class="font-semibold text-slate-700">Historique des connexions</h2>
        </div>
        <?php if (empty($historique)): ?>
            <div class="py-8 text-center text-slate-400 text-sm">Aucune connexion enregistrée.</div>
        <?php else: ?>
        <table class="data-table">
            <thead><tr>
                <th>Action</th><th>Adresse IP</th><th>Date & heure</th>
            </tr></thead>
            <tbody>
            <?php foreach ($historique as $h): ?>
            <tr>
                <td>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium <?= $h['action']==='CONNEXION' ? 'text-emerald-700' : 'text-slate-500' ?>">
                        <i class="fa-solid fa-<?= $h['action']==='CONNEXION' ? 'right-to-bracket' : 'right-from-bracket' ?>"></i>
                        <?= e($h['action']) ?>
                    </span>
                </td>
                <td class="font-mono text-xs text-slate-500"><?= e($h['ip_adresse']) ?></td>
                <td class="text-xs text-slate-500"><?= dateFr($h['created_at'], 'd/m/Y H:i:s') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
