<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('approvisionnement/dons') ?>" class="btn-secondary btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1 class="text-xl font-bold text-slate-800">Nouveau don</h1>
    </div>

    <?php if(!empty($errors['global'])): ?>
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['global']) ?></div>
    <?php endif; ?>
    <?php if(!empty($errors['lignes'])): ?>
    <div class="mb-4 p-3 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 text-sm"><?= e($errors['lignes']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('approvisionnement/dons/creer') ?>">
        <?= csrfField() ?>
        <div class="card card-body mb-4">
            <div class="mb-4">
                <label class="form-label">Donateur (optionnel)</label>
                <select name="id_fournisseur" class="form-select">
                    <option value="">— Anonyme / Non renseigné —</option>
                    <?php foreach($fournisseurs as $f): ?>
                    <option value="<?= $f['id_fournisseur'] ?>"><?= e($f['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Observations</label>
                <textarea name="observations" rows="2" class="form-input" placeholder="Contexte du don..."></textarea>
            </div>
        </div>

        <div class="card overflow-hidden mb-4">
            <div class="card-header">
                <h2 class="font-semibold text-slate-700">Articles reçus</h2>
                <button type="button" onclick="ajouterLigne()" class="btn-secondary btn-sm"><i class="fa-solid fa-plus"></i> Ajouter une ligne</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tableLignes">
                    <thead><tr class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                        <th class="px-4 py-2.5 text-left">Produit</th>
                        <th class="px-4 py-2.5 text-right w-32">Quantité</th>
                        <th class="px-4 py-2.5 text-right w-36">Valeur unit.</th>
                        <th class="px-4 py-2.5 text-right w-36">Montant</th>
                        <th class="px-4 py-2.5 w-10"></th>
                    </tr></thead>
                    <tbody id="lignesBody"></tbody>
                </table>
            </div>
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex justify-end">
                <span class="text-sm font-semibold text-slate-600 mr-3">Valeur totale :</span>
                <span class="text-lg font-bold text-emerald-700" id="totalGeneral">0 FCFA</span>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="<?= url('approvisionnement/dons') ?>" class="btn-secondary">Annuler</a>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-gift"></i> Enregistrer le don</button>
        </div>
    </form>
</div>

<script>
const PRODUITS = <?= json_encode(array_map(fn($p)=>['id'=>$p['id_produit'],'code'=>$p['code'],'designation'=>$p['designation'],'unite'=>$p['unite'],'prix_achat'=>$p['prix_achat']], $produits)) ?>;
let ligneIndex = 0;

function optionsProduits() {
    return '<option value="">— Produit —</option>' + PRODUITS.map(p =>
        `<option value="${p.id}" data-prix="${p.prix_achat}">${p.code} — ${p.designation}</option>`
    ).join('');
}

function ajouterLigne() {
    const idx = ligneIndex++;
    const tr = document.createElement('tr');
    tr.className = 'border-t border-slate-100';
    tr.innerHTML = `
        <td class="px-4 py-2">
            <select name="produit_id[]" class="form-select py-1.5 text-sm" onchange="majPrix(this, ${idx})">${optionsProduits()}</select>
        </td>
        <td class="px-4 py-2"><input type="number" step="0.01" min="0.01" name="quantite[]" class="form-input py-1.5 text-sm text-right" oninput="calculerLigne(${idx})" id="qte_${idx}"></td>
        <td class="px-4 py-2"><input type="number" step="0.01" min="0" name="valeur_unitaire[]" class="form-input py-1.5 text-sm text-right" oninput="calculerLigne(${idx})" id="val_${idx}"></td>
        <td class="px-4 py-2 text-right font-semibold" id="montant_${idx}">0 FCFA</td>
        <td class="px-4 py-2 text-center"><button type="button" onclick="this.closest('tr').remove(); calculerTotal();" class="text-rose-500 hover:text-rose-700"><i class="fa-solid fa-trash"></i></button></td>
    `;
    document.getElementById('lignesBody').appendChild(tr);
}

function majPrix(select, idx) {
    const opt = select.selectedOptions[0];
    if (opt && opt.dataset.prix) {
        document.getElementById('val_' + idx).value = opt.dataset.prix;
        calculerLigne(idx);
    }
}

function calculerLigne(idx) {
    const qte = parseFloat(document.getElementById('qte_' + idx)?.value || 0);
    const val = parseFloat(document.getElementById('val_' + idx)?.value || 0);
    document.getElementById('montant_' + idx).textContent = (qte * val).toLocaleString('fr-FR') + ' FCFA';
    calculerTotal();
}

function calculerTotal() {
    let total = 0;
    document.querySelectorAll('[id^="montant_"]').forEach(el => total += parseFloat(el.textContent.replace(/[^\d.-]/g,'')) || 0);
    document.getElementById('totalGeneral').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

ajouterLigne();
</script>
