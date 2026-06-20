<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Rapports & Éditions</h1>
    <p class="text-sm text-slate-500">Approvisionnement — Documents imprimables</p></div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <a href="<?= url('approvisionnement/rapports/achats-jour') ?>" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center mb-3 group-hover:bg-violet-100 transition">
            <i class="fa-solid fa-calendar-day text-violet-600"></i>
        </div>
        <h3 class="font-semibold text-slate-800 mb-1">État des achats par jour</h3>
        <p class="text-xs text-slate-500">Toutes les commandes d'une journée donnée avec totaux.</p>
    </a>
    <a href="<?= url('approvisionnement/rapports/achats-annuels') ?>" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3 group-hover:bg-blue-100 transition">
            <i class="fa-solid fa-chart-bar text-blue-600"></i>
        </div>
        <h3 class="font-semibold text-slate-800 mb-1">Achats annuels</h3>
        <p class="text-xs text-slate-500">Rapport annuel par mois et par fournisseur avec pourcentages.</p>
    </a>
    <div class="card p-5 bg-slate-50">
        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
            <i class="fa-solid fa-file-lines text-slate-400"></i>
        </div>
        <h3 class="font-semibold text-slate-600 mb-1">Impressions par document</h3>
        <p class="text-xs text-slate-400">Accessible depuis le détail de chaque commande, réception, facture ou don via le bouton <strong>Imprimer</strong>.</p>
    </div>
</div>
