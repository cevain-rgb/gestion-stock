<div class="flex items-center justify-between mb-6">
    <div><h1 class="text-xl font-bold text-slate-800">Rapports & Éditions — Vente</h1></div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    <a href="<?= url('vente/rapports/ventes-jour') ?>" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center mb-3 group-hover:bg-violet-100 transition">
            <i class="fa-solid fa-calendar-day text-violet-600"></i>
        </div>
        <h3 class="font-semibold text-slate-800 mb-1">État des ventes par jour</h3>
        <p class="text-xs text-slate-500">CA du jour, répartition comptant/crédit, liste des commandes.</p>
    </a>
    <a href="<?= url('vente/rapports/ventes-annuelles') ?>" class="card p-5 hover:border-violet-300 hover:shadow-md transition group">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-3 group-hover:bg-emerald-100 transition">
            <i class="fa-solid fa-chart-bar text-emerald-600"></i>
        </div>
        <h3 class="font-semibold text-slate-800 mb-1">Ventes annuelles</h3>
        <p class="text-xs text-slate-500">CA annuel par mois, par mode et top 15 clients.</p>
    </a>
    <div class="card p-5 bg-slate-50">
        <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
            <i class="fa-solid fa-print text-slate-400"></i>
        </div>
        <h3 class="font-semibold text-slate-600 mb-1">Impressions par document</h3>
        <p class="text-xs text-slate-400">Bouton <strong>Imprimer</strong> disponible sur chaque commande, livraison, facture et bon de sortie.</p>
    </div>
</div>
