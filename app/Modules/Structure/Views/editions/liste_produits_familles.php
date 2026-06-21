<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Liste des produits par famille'; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()">🖨 Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info"><strong>Date :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">Liste des Produits par Famille</div>

    <!-- Résumé familles -->
    <table class="tableau" style="margin-bottom:8mm">
        <thead><tr><th>Famille</th><th class="r">Produits</th><th class="r">Valeur stock</th></tr></thead>
        <tbody>
        <?php foreach ($familles as $f): ?>
        <tr>
            <td><?= htmlspecialchars($f['libelle']) ?></td>
            <td class="r"><?= (int)$f['nb_produits'] ?></td>
            <td class="r"><?= number_format((float)$f['valeur_stock'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td>Total</td>
            <td class="r"><?= array_sum(array_column($familles,'nb_produits')) ?></td>
            <td class="r"><?= number_format(array_sum(array_column($familles,'valeur_stock')),0,',',' ') ?> FCFA</td>
        </tr></tfoot>
    </table>

    <!-- Détail par famille -->
    <?php foreach ($familles as $f):
        $prods = $produitsByFamille[$f['id_famille']] ?? [];
        if (empty($prods)) continue;
    ?>
    <p style="font-size:12px;font-weight:700;color:#7c3aed;margin:5mm 0 2mm;text-transform:uppercase;border-bottom:1px solid #7c3aed;padding-bottom:1mm">
        <?= htmlspecialchars($f['libelle']) ?>
    </p>
    <table class="tableau" style="margin-bottom:5mm">
        <thead><tr>
            <th style="width:13%">Code</th><th>Désignation</th>
            <th class="r" style="width:11%">Stock</th>
            <th class="r" style="width:10%">Unité</th>
            <th class="r" style="width:11%">Seuil</th>
            <th class="r" style="width:15%">Prix achat</th>
            <th class="r" style="width:15%">Prix vente</th>
            <th class="r" style="width:14%">Valeur stock</th>
        </tr></thead>
        <tbody>
        <?php foreach ($prods as $p): ?>
        <tr <?= $p['en_alerte'] ? 'style="background:#fef2f2"' : '' ?>>
            <td style="font-family:monospace"><?= htmlspecialchars($p['code']) ?></td>
            <td><?= htmlspecialchars($p['designation']) ?> <?= $p['en_alerte'] ? '<span style="color:#ef4444;font-size:10px">⚠</span>' : '' ?></td>
            <td class="r" style="font-weight:<?= $p['en_alerte']?'700':'400' ?>;color:<?= $p['en_alerte']?'#991b1b':'inherit' ?>"><?= number_format((float)$p['stock_actuel'],2,',',' ') ?></td>
            <td class="r"><?= htmlspecialchars($p['unite']) ?></td>
            <td class="r"><?= number_format((float)$p['stock_alerte'],2,',',' ') ?></td>
            <td class="r"><?= number_format((float)$p['prix_achat'],0,',',' ') ?> FCFA</td>
            <td class="r"><?= number_format((float)$p['prix_vente'],0,',',' ') ?> FCFA</td>
            <td class="r"><?= number_format((float)$p['stock_actuel']*(float)$p['prix_achat'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endforeach; ?>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
