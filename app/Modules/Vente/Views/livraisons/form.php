<?php $qteLivrees = $cmd['qte_livrees'] ?? []; ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('vente/commandes/'.$cmd['oid_doc']) ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div><h1 class="text-xl font-bold text-slate-800">Livrer — <?= e($cmd['numero']) ?></h1><p class="text-sm text-slate-500"><?= e($cmd['client']) ?></p></div>
    </div>

    <?php if(!empty($errors['global'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['global']) ?></div><?php endif; ?>
    <?php if(!empty($errors['lignes'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['lignes']) ?></div><?php endif; ?>

    <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-200 text-blue-700 text-sm flex items-start gap-2">
        <i class="fa-solid fa-circle-info mt-0.5"></i>
        <span>Le stock sera automatiquement décrémenté. Si le stock est insuffisant, l'opération sera rejetée.</span>
    </div>

    <form method="POST" action="<?= url('vente/commandes/'.$cmd['oid_doc'].'/livrer') ?>">
        <?= csrfField() ?>
        <div class="card card-body mb-4">
            <label class="form-label">Observations</label>
            <textarea name="observations" rows="2" class="form-input"></textarea>
        </div>
        <div class="card overflow-hidden">
            <div class="card-header"><h2 class="font-semibold text-slate-700">Lignes commandées</h2></div>
            <table class="data-table">
                <thead><tr><th>Produit</th><th class="text-right">Commandé</th><th class="text-right">Déjà livré</th><th class="text-right">Stock dispo.</th><th class="text-right w-32">Qté à livrer</th><th class="text-right w-36">Prix unit.</th></tr></thead>
                <tbody>
                <?php foreach($cmd['lignes'] as $l): ?>
                <?php $livre = (float)($qteLivrees[$l['id_produit']] ?? 0); $restant = max(0,(float)$l['quantite']-$livre); ?>
                <tr>
                    <td><div class="font-medium text-slate-700"><?= e($l['designation']) ?></div><div class="text-xs font-mono text-slate-400"><?= e($l['code']) ?></div>
                        <input type="hidden" name="produit_id[]" value="<?= $l['id_produit'] ?>"></td>
                    <td class="text-right text-sm"><?= number_format((float)$l['quantite'],2,',',' ') ?> <?= e($l['unite']) ?></td>
                    <td class="text-right text-sm <?= $livre>0?'text-emerald-600 font-medium':'text-slate-400' ?>"><?= number_format($livre,2,',',' ') ?></td>
                    <td class="text-right text-sm <?= (float)$l['stock_actuel']<$restant?'text-rose-600 font-semibold':'text-slate-500' ?>"><?= number_format((float)$l['stock_actuel'],2,',',' ') ?></td>
                    <td><input type="number" step="0.01" min="0" max="<?= $restant ?>" name="quantite_livree[]" value="<?= $restant>0?$restant:0 ?>" class="form-input py-1.5 text-sm text-right" <?= $restant<=0?'disabled':'' ?>></td>
                    <td><input type="number" step="0.01" min="0" name="prix_unitaire[]" value="<?= $l['prix_unitaire'] ?>" class="form-input py-1.5 text-sm text-right"></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="flex justify-end gap-3 mt-4">
            <a href="<?= url('vente/commandes/'.$cmd['oid_doc']) ?>" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-truck"></i> Confirmer la livraison</button>
        </div>
    </form>
</div>
