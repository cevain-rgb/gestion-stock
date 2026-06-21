<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('vente/comptant') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><i class="fa-solid fa-cash-register text-emerald-600 mr-2"></i>Nouvelle vente comptant</h1>
    </div>

    <?php if(!empty($errors['global'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['global']) ?></div><?php endif; ?>
    <?php if(!empty($errors['lignes'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['lignes']) ?></div><?php endif; ?>

    <div class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-start gap-2">
        <i class="fa-solid fa-circle-info mt-0.5"></i>
        <span>La commande, la livraison, la facture et le règlement seront créés automatiquement en une seule opération. Le stock sera décrémenté immédiatement.</span>
    </div>

    <form method="POST" action="<?= url('vente/comptant/creer') ?>">
        <?= csrfField() ?>
        <div class="card card-body mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Client <span class="text-rose-500">*</span></label>
                    <select name="id_client" id="selectClient" class="form-select <?= !empty($errors['id_client'])?'border-rose-400':'' ?>" required onchange="majRemiseClient()">
                        <option value="">— Choisir —</option>
                        <?php foreach($clients as $c): ?>
                        <option value="<?= $c['id_client'] ?>" data-remise="<?= $c['remise_pct'] ?>"><?= e($c['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if(!empty($errors['id_client'])): ?><p class="form-error"><?= e($errors['id_client']) ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="form-label">Taux TVA (%)</label>
                    <input type="number" step="0.01" min="0" name="taux_tva" id="tauxTva" value="0" class="form-input" oninput="calculerTotal()">
                </div>
            </div>
        </div>

        <div class="card overflow-hidden mb-4">
            <div class="card-header">
                <h2 class="font-semibold text-slate-700">Articles</h2>
                <button type="button" onclick="ajouterLigne()" class="btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Ajouter une ligne</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tableLignes">
                    <thead><tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-2.5 text-left">Produit</th>
                        <th class="px-4 py-2.5 text-right w-28">Quantité</th>
                        <th class="px-4 py-2.5 text-right w-32">Prix unit.</th>
                        <th class="px-4 py-2.5 text-right w-32">Montant</th>
                        <th class="px-4 py-2.5 w-10"></th>
                    </tr></thead>
                    <tbody id="lignesBody"></tbody>
                </table>
            </div>
        </div>

        <div class="card card-body mb-4 bg-slate-50">
            <div class="flex justify-between items-center mb-2 text-sm">
                <span class="text-slate-500">Sous-total HT</span><span class="font-medium" id="sousTotal">0 FCFA</span>
            </div>
            <div class="flex justify-between items-center mb-3 text-sm">
                <span class="text-slate-500">TVA</span><span class="font-medium" id="montantTva">0 FCFA</span>
            </div>
            <div class="flex justify-between items-center border-t border-slate-200 pt-3 mb-4">
                <span class="font-semibold text-slate-700">Total TTC à payer</span>
                <span class="text-2xl font-bold text-violet-700" id="totalTtc">0 FCFA</span>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label text-xs">Mode de paiement</label>
                    <select name="mode_paiement" class="form-select py-2 text-sm">
                        <option value="especes">Espèces</option><option value="cheque">Chèque</option>
                        <option value="virement">Virement</option><option value="mobile_money">Mobile Money</option>
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Montant reçu (FCFA)</label>
                    <input type="number" step="0.01" min="0" name="montant_paye" id="montantPaye" class="form-input py-2 text-sm" oninput="calculerMonnaie()">
                </div>
            </div>
            <div class="mt-3 flex justify-between items-center text-sm">
                <span class="text-slate-500">Monnaie à rendre</span>
                <span class="font-bold text-lg" id="monnaieRendre">0 FCFA</span>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= url('vente/comptant') ?>" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-cash-register"></i> Enregistrer la vente</button>
        </div>
    </form>
</div>

<script>
const PRODUITS = <?= json_encode(array_map(fn($p)=>['id'=>$p['id_produit'],'code'=>$p['code'],'designation'=>$p['designation'],'unite'=>$p['unite'],'prix_vente'=>$p['prix_vente'],'stock'=>$p['stock_actuel']], $produits)) ?>;
let ligneIndex = 0;

function optionsProduits() {
    return '<option value="">— Produit —</option>' + PRODUITS.map(p =>
        `<option value="${p.id}" data-prix="${p.prix_vente}">${p.code} — ${p.designation} (stock: ${parseFloat(p.stock).toLocaleString('fr-FR')})</option>`
    ).join('');
}

function majRemiseClient() {} // remise gérée côté serveur via défaut catégorie

function ajouterLigne() {
    const idx = ligneIndex++;
    const tr = document.createElement('tr');
    tr.className = 'border-t border-slate-100';
    tr.innerHTML = `
        <td class="px-4 py-2"><select name="produit_id[]" class="form-select py-1.5 text-sm" onchange="majPrix(this, ${idx})">${optionsProduits()}</select></td>
        <td class="px-4 py-2"><input type="number" step="0.01" min="0.01" name="quantite[]" class="form-input py-1.5 text-sm text-right" oninput="calculerLigne(${idx})" id="qte_${idx}"></td>
        <td class="px-4 py-2"><input type="number" step="0.01" min="0" name="prix_unitaire[]" class="form-input py-1.5 text-sm text-right" oninput="calculerLigne(${idx})" id="prix_${idx}"></td>
        <td class="px-4 py-2 text-right font-semibold" id="montant_${idx}">0 FCFA</td>
        <td class="px-4 py-2 text-center"><button type="button" onclick="this.closest('tr').remove(); calculerTotal();" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash"></i></button></td>
    `;
    document.getElementById('lignesBody').appendChild(tr);
}

function majPrix(select, idx) {
    const opt = select.selectedOptions[0];
    if (opt && opt.dataset.prix) { document.getElementById('prix_' + idx).value = opt.dataset.prix; calculerLigne(idx); }
}

function calculerLigne(idx) {
    const qte  = parseFloat(document.getElementById('qte_' + idx)?.value || 0);
    const prix = parseFloat(document.getElementById('prix_' + idx)?.value || 0);
    document.getElementById('montant_' + idx).textContent = (qte * prix).toLocaleString('fr-FR') + ' FCFA';
    calculerTotal();
}

function calculerTotal() {
    let sousTotal = 0;
    document.querySelectorAll('[id^="montant_"]').forEach(el => sousTotal += parseFloat(el.textContent.replace(/[^\d.-]/g,'')) || 0);
    const tva = parseFloat(document.getElementById('tauxTva').value || 0);
    const montantTva = sousTotal * tva / 100;
    const ttc = sousTotal + montantTva;
    document.getElementById('sousTotal').textContent  = sousTotal.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('montantTva').textContent = montantTva.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('totalTtc').textContent    = ttc.toLocaleString('fr-FR') + ' FCFA';
    calculerMonnaie();
}

function calculerMonnaie() {
    const ttc  = parseFloat(document.getElementById('totalTtc').textContent.replace(/[^\d.-]/g,'')) || 0;
    const paye = parseFloat(document.getElementById('montantPaye').value || 0);
    const monnaie = paye - ttc;
    const el = document.getElementById('monnaieRendre');
    el.textContent = Math.abs(monnaie).toLocaleString('fr-FR') + ' FCFA' + (monnaie < 0 ? ' (manquant)' : '');
    el.className = 'font-bold text-lg ' + (monnaie < 0 ? 'text-rose-600' : 'text-emerald-600');
}

ajouterLigne();
</script>
