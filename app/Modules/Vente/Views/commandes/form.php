<?php $lignesExistantes = $cmd['lignes'] ?? []; ?>
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('vente/commandes') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800"><?= $cmd ? 'Modifier la commande' : 'Nouvelle commande client' ?></h1>
    </div>

    <?php if(!empty($errors['global'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['global']) ?></div><?php endif; ?>
    <?php if(!empty($errors['lignes'])): ?><div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['lignes']) ?></div><?php endif; ?>

    <form method="POST" action="<?= $cmd ? url('vente/commandes/'.$cmd['oid_doc'].'/edit') : url('vente/commandes/creer') ?>">
        <?= csrfField() ?>
        <div class="card card-body mb-4">
            <?php if(!$cmd): ?>
            <div class="mb-4">
                <label class="form-label">Client <span class="text-rose-500">*</span></label>
                <select name="id_client" id="selectClient" class="form-select <?= !empty($errors['id_client'])?'border-rose-400':'' ?>" required onchange="majRemiseClient()">
                    <option value="">— Choisir —</option>
                    <?php foreach($clients as $c): ?>
                    <option value="<?= $c['id_client'] ?>" data-remise="<?= $c['remise_pct'] ?>"><?= e($c['nom']) ?> <?= (float)$c['remise_pct']>0?'(remise -'.$c['remise_pct'].'%)':'' ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if(!empty($errors['id_client'])): ?><p class="form-error"><?= e($errors['id_client']) ?></p><?php endif; ?>
            </div>
            <?php else: ?>
            <div class="mb-4 text-sm text-slate-500">Client : <span class="font-semibold text-slate-700"><?= e($cmd['client']) ?></span> (non modifiable)</div>
            <?php endif; ?>
            <div>
                <label class="form-label">Observations</label>
                <textarea name="observations" rows="2" class="form-input"><?= e($cmd['observations'] ?? '') ?></textarea>
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
                        <th class="px-4 py-2.5 text-right w-24">Remise %</th>
                        <th class="px-4 py-2.5 text-right w-32">Montant</th>
                        <th class="px-4 py-2.5 w-10"></th>
                    </tr></thead>
                    <tbody id="lignesBody"></tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
                <span class="text-sm font-semibold text-slate-600 mr-3">Total :</span>
                <span class="text-lg font-bold text-violet-700" id="totalGeneral">0 FCFA</span>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= url('vente/commandes') ?>" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $cmd?'Enregistrer':'Créer la commande' ?></button>
        </div>
    </form>
</div>

<script>
const PRODUITS = <?= json_encode(array_map(fn($p)=>['id'=>$p['id_produit'],'code'=>$p['code'],'designation'=>$p['designation'],'unite'=>$p['unite'],'prix_vente'=>$p['prix_vente']], $produits)) ?>;
const LIGNES_EXISTANTES = <?= json_encode(array_map(fn($l)=>['id_produit'=>$l['id_produit'],'quantite'=>$l['quantite'],'prix_unitaire'=>$l['prix_unitaire'],'remise_pct'=>$l['remise_pct']], $lignesExistantes)) ?>;
let ligneIndex = 0;
let remiseClientDefaut = 0;

function optionsProduits(selectedId = '') {
    return '<option value="">— Produit —</option>' + PRODUITS.map(p =>
        `<option value="${p.id}" data-prix="${p.prix_vente}" ${String(p.id)===String(selectedId)?'selected':''}>${p.code} — ${p.designation}</option>`
    ).join('');
}

function majRemiseClient() {
    const opt = document.getElementById('selectClient')?.selectedOptions[0];
    remiseClientDefaut = opt ? parseFloat(opt.dataset.remise || 0) : 0;
    document.querySelectorAll('[id^="remise_"]').forEach(input => { if (!input.value) input.value = remiseClientDefaut; });
}

function ajouterLigne(idProduit = '', quantite = '', prixUnitaire = '', remisePct = '') {
    const idx = ligneIndex++;
    const tr = document.createElement('tr');
    tr.className = 'border-t border-slate-100';
    tr.innerHTML = `
        <td class="px-4 py-2"><select name="produit_id[]" class="form-select py-1.5 text-sm" onchange="majPrix(this, ${idx})">${optionsProduits(idProduit)}</select></td>
        <td class="px-4 py-2"><input type="number" step="0.01" min="0.01" name="quantite[]" value="${quantite}" class="form-input py-1.5 text-sm text-right" oninput="calculerLigne(${idx})" id="qte_${idx}"></td>
        <td class="px-4 py-2"><input type="number" step="0.01" min="0" name="prix_unitaire[]" value="${prixUnitaire}" class="form-input py-1.5 text-sm text-right" oninput="calculerLigne(${idx})" id="prix_${idx}"></td>
        <td class="px-4 py-2"><input type="number" step="0.01" min="0" max="100" name="remise_pct[]" value="${remisePct || remiseClientDefaut}" class="form-input py-1.5 text-sm text-right" oninput="calculerLigne(${idx})" id="remise_${idx}"></td>
        <td class="px-4 py-2 text-right font-semibold" id="montant_${idx}">0 FCFA</td>
        <td class="px-4 py-2 text-center"><button type="button" onclick="this.closest('tr').remove(); calculerTotal();" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash"></i></button></td>
    `;
    document.getElementById('lignesBody').appendChild(tr);
    if (prixUnitaire) calculerLigne(idx);
}

function majPrix(select, idx) {
    const opt = select.selectedOptions[0];
    if (opt && opt.dataset.prix) { document.getElementById('prix_' + idx).value = opt.dataset.prix; calculerLigne(idx); }
}

function calculerLigne(idx) {
    const qte    = parseFloat(document.getElementById('qte_' + idx)?.value || 0);
    const prix   = parseFloat(document.getElementById('prix_' + idx)?.value || 0);
    const remise = parseFloat(document.getElementById('remise_' + idx)?.value || 0);
    const montant = qte * prix * (1 - remise / 100);
    document.getElementById('montant_' + idx).textContent = montant.toLocaleString('fr-FR') + ' FCFA';
    calculerTotal();
}

function calculerTotal() {
    let total = 0;
    document.querySelectorAll('[id^="montant_"]').forEach(el => total += parseFloat(el.textContent.replace(/[^\d.-]/g,'')) || 0);
    document.getElementById('totalGeneral').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

if (LIGNES_EXISTANTES.length > 0) {
    LIGNES_EXISTANTES.forEach(l => ajouterLigne(l.id_produit, l.quantite, l.prix_unitaire, l.remise_pct));
} else {
    ajouterLigne();
}
</script>
