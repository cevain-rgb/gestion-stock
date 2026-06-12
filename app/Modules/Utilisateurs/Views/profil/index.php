<div class="max-w-3xl mx-auto">
    <h1 class="text-xl font-bold text-slate-800 mb-6">Mon profil</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <!-- Infos profil -->
        <div class="card card-body">
            <div class="flex items-center gap-4 mb-5">
                <div class="w-14 h-14 rounded-2xl bg-violet-700 flex items-center justify-center text-white text-xl font-bold">
                    <?= strtoupper(substr($profil['prenom'] ?? 'U', 0, 1) . substr($profil['nom'], 0, 1)) ?>
                </div>
                <div>
                    <div class="font-bold text-slate-800 text-lg"><?= e($profil['prenom'].' '.$profil['nom']) ?></div>
                    <div class="text-sm text-slate-500 font-mono"><?= e($profil['login']) ?></div>
                    <span class="text-xs bg-violet-100 text-violet-700 px-2 py-0.5 rounded-full font-medium mt-1 inline-block">
                        <?= e($profil['groupe_libelle']) ?>
                    </span>
                </div>
            </div>
            <dl class="space-y-2 text-sm border-t border-slate-100 pt-4">
                <div class="flex justify-between"><dt class="text-slate-500">Statut</dt>
                    <dd><?= $profil['actif'] ? '<span class="text-emerald-600 font-medium">Actif</span>' : '<span class="text-slate-400">Inactif</span>' ?></dd>
                </div>
                <div class="flex justify-between"><dt class="text-slate-500">Compte créé le</dt><dd><?= dateFr($profil['created_at']) ?></dd></div>
            </dl>
        </div>

        <!-- Changer mot de passe -->
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-4">Changer mon mot de passe</h2>
            <form method="POST" action="<?= url('profil/mdp') ?>">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label text-xs">Nouveau mot de passe</label>
                    <input type="password" name="password" minlength="6" required class="form-input">
                </div>
                <div class="mb-4">
                    <label class="form-label text-xs">Confirmation</label>
                    <input type="password" name="password_confirm" required class="form-input">
                </div>
                <button type="submit" class="btn-primary w-full">
                    <i class="fa-solid fa-key"></i> Mettre à jour
                </button>
            </form>
        </div>
    </div>

    <!-- Droits du groupe -->
    <div class="card overflow-hidden mb-4">
        <div class="card-header">
            <h2 class="font-semibold text-slate-700">Mes droits d'accès</h2>
        </div>
        <div class="p-5">
            <?php
            use App\Modules\Utilisateurs\Models\DroitModel;
            $droitsSession = $_SESSION['droits'] ?? [];
            $labelsModule  = ['approvisionnement'=>'Approvisionnement','vente'=>'Vente','structure'=>'Structure','securite'=>'Sécurité'];
            $labelsAction  = ['consulter'=>'Consulter','creer'=>'Créer','modifier'=>'Modifier','supprimer'=>'Supprimer','imprimer'=>'Imprimer','regler'=>'Régler'];
            ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <?php foreach (DroitModel::MODULES as $mod): ?>
            <div>
                <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2"><?= e($labelsModule[$mod]) ?></div>
                <div class="flex flex-wrap gap-1">
                <?php foreach (DroitModel::ACTIONS as $act): ?>
                <?php $ok = !empty($droitsSession[$mod.'.'.$act]); ?>
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-medium
                    <?= $ok ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' ?>">
                    <i class="fa-solid fa-<?= $ok ? 'check' : 'xmark' ?> text-[10px]"></i>
                    <?= e($labelsAction[$act]) ?>
                </span>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Historique connexions -->
    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Mes dernières connexions</h2></div>
        <?php if (empty($historique)): ?>
            <div class="py-8 text-center text-slate-400 text-sm">Aucun historique.</div>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Action</th><th>IP</th><th>Date & heure</th></tr></thead>
            <tbody>
            <?php foreach ($historique as $h): ?>
            <tr>
                <td><span class="text-xs font-medium <?= $h['action']==='CONNEXION' ? 'text-emerald-700' : 'text-slate-400' ?>">
                    <i class="fa-solid fa-<?= $h['action']==='CONNEXION' ? 'right-to-bracket' : 'right-from-bracket' ?> mr-1"></i><?= e($h['action']) ?>
                </span></td>
                <td class="font-mono text-xs text-slate-500"><?= e($h['ip_adresse']) ?></td>
                <td class="text-xs text-slate-500"><?= dateFr($h['created_at'], 'd/m/Y H:i:s') ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
