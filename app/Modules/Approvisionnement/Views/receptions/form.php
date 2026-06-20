<?php $qteRecues = $cmd['qte_recues'] ?? []; ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('approvisionnement/commandes/'.$cmd['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div>
            <h1 class="text-xl font-bold text-slate-800">Réceptionner — <?= e($cmd['numero']) ?></h1>
            <p class="text-sm text-slate-500"><?= e($cmd['fournisseur']) ?></p>
        </div>
    </div>

    <?php if(!empty($errors['global'])): ?>
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['global']) ?></div>
    <?php endif; ?>
    <?php if(!empty($errors['lignes'])): ?>
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['lignes']) ?></div>
    <?php endif; ?>

    <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-sm flex items-start gap-2">
        <i class="fa-solid fa-circle-info mt-0.5"></i>
        <span>Indiquez les quantités effectivement reçues. Le stock sera mis à jour automatiquement à l'enregistrement.</span>
    </div>

    <form method="POST" action="<?= url('approvisionnement/commandes/'.$cmd['oid_doc'].'/recevoir') ?>">
        <?= csrfField() ?>
        <div class="card card-body mb-4">
            <label class="form-label">Observations</label>
            <textarea name="observations" rows="2" class="form-input"></textarea>
        </div>
        <div class="card overflow-hidden">
            <div class="card-header"><h2 class="font-semibold text-slate-700">Lignes commandées</h2></div>
            <table class="data-table">
                <thead><tr><th>Produit</th><th class="text-right">Commandé</th><th class="text-right">Déjà reçu</th><th class="text-right w-32">Qté à recevoir</th><th class="text-right w-36">Prix unit.</th></tr></thead>
                <tbody>
                <?php foreach($cmd['lignes'] as $l): ?>
                <?php $recu = (float)($qteRecues[$l['id_produit']] ?? 0); $restant = max(0,(float)$l['quantite']-$recu); ?>
                <tr>
                    <td>
                        <div class="font-medium text-slate-700"><?= e($l['designation']) ?></div>
                        <div class="text-xs font-mono text-slate-400"><?= e($l['code']) ?></div>
                        <input type="hidden" name="produit_id[]" value="<?= $l['id_produit'] ?>">
                    </td>
                    <td class="text-right text-sm"><?= number_format((float)$l['quantite'],2,',',' ') ?> <?= e($l['unite']) ?></td>
                    <td class="text-right text-sm <?= $recu>0?'text-emerald-600 font-medium':'text-slate-400' ?>"><?= number_format($recu,2,',',' ') ?></td>
                    <td><input type="number" step="0.01" min="0" max="<?= $restant ?>" name="quantite_recue[]"
                        value="<?= $restant>0?$restant:0 ?>" class="form-input py-1.5 text-sm text-right" <?= $restant<=0?'disabled':'' ?>></td>
                    <td><input type="number" step="0.01" min="0" name="prix_unitaire[]" value="<?= $l['prix_unitaire'] ?>" class="form-input py-1.5 text-sm text-right"></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="flex justify-end gap-3 mt-4">
            <a href="<?= url('approvisionnement/commandes/'.$cmd['oid_doc']) ?>" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-truck-ramp-box"></i> Confirmer la réception</button>
        </div>
    </form>
</div>
