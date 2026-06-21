<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('vente/factures') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <div class="flex-1">
            <h1 class="text-xl font-bold text-slate-800 font-mono"><?= e($facture['numero']) ?></h1>
            <p class="text-sm text-slate-500"><?= e($facture['client']) ?> — Commande <?= e($facture['numero_commande']) ?></p>
        </div>
        <?= badgePaiement($facture['statut_paiement']) ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-3">Détails</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Date</dt><dd><?= dateFr($facture['date_document']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Montant HT</dt><dd><?= money($facture['montant_ht']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">TVA (<?= $facture['taux_tva'] ?>%)</dt><dd><?= money($facture['montant_tva']) ?></dd></div>
                <div class="flex justify-between border-t border-slate-100 pt-2"><dt class="font-semibold text-slate-700">Montant TTC</dt><dd class="font-bold text-violet-700"><?= money($facture['montant_ttc']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Reste à payer</dt>
                    <dd class="font-bold <?= (float)$facture['reste_a_payer']>0?'text-rose-600':'text-emerald-600' ?>"><?= money($facture['reste_a_payer']) ?></dd></div>
            </dl>
        </div>

                <a href="<?= url('vente/factures/'.$facture['oid_doc'].'/imprimer') ?>" target="_blank" class="btn-secondary btn-sm">
            <i class="fa-solid fa-print"></i> Imprimer facture
        </a>
        <?php if((float)$facture['reste_a_payer']>0 && !empty($_SESSION['droits']['vente.regler'])): ?>
        <div class="card card-body">
            <h2 class="font-semibold text-slate-700 mb-3">Enregistrer un règlement</h2>
            <form method="POST" action="<?= url('vente/factures/'.$facture['oid_doc'].'/reglement') ?>">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label class="form-label text-xs">Montant <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" max="<?= $facture['reste_a_payer'] ?>" name="montant" class="form-input" required>
                </div>
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div><label class="form-label text-xs">Mode</label>
                        <select name="mode_paiement" class="form-select py-2 text-sm">
                            <option value="especes">Espèces</option><option value="cheque">Chèque</option>
                            <option value="virement">Virement</option><option value="mobile_money">Mobile Money</option>
                        </select></div>
                    <div><label class="form-label text-xs">Date</label><input type="date" name="date_reglement" value="<?= date('Y-m-d') ?>" class="form-input py-2 text-sm"></div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-xs">Banque (optionnel)</label>
                    <select name="id_banque" class="form-select py-2 text-sm"><option value="">—</option>
                    <?php foreach($banques as $b): ?><option value="<?= $b['id_banque'] ?>"><?= e($b['nom']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-4"><label class="form-label text-xs">Référence</label><input type="text" name="reference" class="form-input" placeholder="N° chèque, virement..."></div>
                <button type="submit" class="btn-primary w-full"><i class="fa-solid fa-money-bill"></i> Enregistrer le règlement</button>
            </form>
        </div>
        <?php endif; ?>
    </div>

    <div class="card overflow-hidden">
        <div class="card-header"><h2 class="font-semibold text-slate-700">Historique des règlements</h2></div>
        <?php if(empty($facture['reglements'])): ?>
        <div class="py-8 text-center text-slate-400 text-sm">Aucun règlement enregistré.</div>
        <?php else: ?>
        <table class="data-table"><thead><tr><th>Date</th><th>Mode</th><th>Banque</th><th>Référence</th><th class="text-right">Montant</th><th class="text-center">Reçu</th></tr></thead>
        <tbody>
        <?php foreach($facture['reglements'] as $r): ?>
        <tr>
            <td class="text-sm"><?= dateFr($r['date_reglement']) ?></td>
            <td><span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full"><?= e(ucfirst(str_replace('_',' ',$r['mode_paiement']))) ?></span></td>
            <td class="text-sm text-slate-500"><?= e($r['banque_nom']?:'—') ?></td>
            <td class="text-sm text-slate-500"><?= e($r['reference']?:'—') ?></td>
            <td class="text-right font-semibold text-emerald-700"><?= money($r['montant']) ?></td>
            <td class="text-center"><a href="<?= url('vente/reglements/'.$r['id_reglement_c'].'/recu') ?>" target="_blank" class="text-violet-500 hover:text-violet-700 text-sm" title="Imprimer le reçu"><i class="fa-solid fa-receipt"></i></a></td>
        </tr>
        <?php endforeach; ?>
        </tbody></table>
        <?php endif; ?>
    </div>
</div>
