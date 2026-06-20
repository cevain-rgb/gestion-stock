<?php
$layoutFile = __DIR__ . '/../editions/layout_print.php';
ob_start();
?>
<?php
$moisFR = ['','Jan.','Fév.','Mars','Avr.','Mai','Juin','Juil.','Août','Sept.','Oct.','Nov.','Déc.'];
$titreDocument = 'Achats annuels ' . $annee;
$parMoisIdx = array_column($parMois, 'total', 'mois');
?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()"><span>🖨</span> Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
    <form method="GET" action="" style="display:flex;align-items:center;gap:6px;margin-left:12px">
        <select name="annee" style="border:1px solid #e2e8f0;border-radius:4px;padding:4px 8px;font-size:12px">
        <?php for($y=date('Y');$y>=(date('Y')-5);$y--): ?>
        <option value="<?= $y ?>" <?= $y==$annee?'selected':'' ?>><?= $y ?></option>
        <?php endfor; ?>
        </select>
        <button type="submit" style="background:#7c3aed;color:white;border:none;border-radius:4px;padding:4px 12px;cursor:pointer;font-size:12px">Voir</button>
    </form>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info"><strong>Date impression :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">Rapport des Achats Annuels</div>
    <div class="numero-doc">Exercice <?= $annee ?></div>

    <!-- Résumé KPI -->
    <div style="display:flex;gap:8mm;margin-bottom:8mm">
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:22px;font-weight:900;color:#7c3aed"><?= number_format((float)$totaux['total'],0,',',' ') ?> FCFA</div>
            <div style="font-size:10px;color:#64748b">Total achats</div>
        </div>
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:22px;font-weight:900;color:#1e40af"><?= (int)$totaux['nb_commandes'] ?></div>
            <div style="font-size:10px;color:#64748b">Commandes</div>
        </div>
    </div>

    <!-- Par mois -->
    <p style="font-size:11px;font-weight:700;margin-bottom:3mm;text-transform:uppercase;letter-spacing:0.5px;color:#64748b">Répartition mensuelle</p>
    <table class="tableau" style="margin-bottom:8mm">
        <thead><tr><th>Mois</th><th class="r">Nb commandes</th><th class="r">Montant</th><th class="r">% du total</th></tr></thead>
        <tbody>
        <?php for($m=1;$m<=12;$m++): ?>
        <?php $row = $parMois[array_search($m, array_column($parMois,'mois'))] ?? null; ?>
        <tr <?= !$row?'style="color:#94a3b8"':'' ?>>
            <td><?= $moisFR[$m] ?></td>
            <td class="r"><?= $row?(int)$row['nb_commandes']:'—' ?></td>
            <td class="r"><?= $row?number_format((float)$row['total'],0,',',' ').' FCFA':'—' ?></td>
            <td class="r"><?= ($row&&(float)$totaux['total']>0)?number_format((float)$row['total']/(float)$totaux['total']*100,1).'%':'—' ?></td>
        </tr>
        <?php endfor; ?>
        </tbody>
        <tfoot><tr><td>Total</td><td class="r"><?= (int)$totaux['nb_commandes'] ?></td>
            <td class="r"><?= number_format((float)$totaux['total'],0,',',' ') ?> FCFA</td><td class="r">100%</td>
        </tr></tfoot>
    </table>

    <!-- Par fournisseur -->
    <p style="font-size:11px;font-weight:700;margin-bottom:3mm;text-transform:uppercase;letter-spacing:0.5px;color:#64748b">Répartition par fournisseur</p>
    <table class="tableau">
        <thead><tr><th>Fournisseur</th><th class="r">Nb commandes</th><th class="r">Montant</th><th class="r">% du total</th></tr></thead>
        <tbody>
        <?php foreach($parFournisseur as $f): ?>
        <tr>
            <td><?= htmlspecialchars($f['fournisseur']) ?></td>
            <td class="r"><?= (int)$f['nb_commandes'] ?></td>
            <td class="r"><?= number_format((float)$f['total'],0,',',' ') ?> FCFA</td>
            <td class="r"><?= (float)$totaux['total']>0?number_format((float)$f['total']/(float)$totaux['total']*100,1).'%':'—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span><span>Exercice <?= $annee ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
