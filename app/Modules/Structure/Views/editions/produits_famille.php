<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Produits — ' . $famille['libelle']; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()">🖨 Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info"><strong>Date :</strong> <?= (new DateTime())->format('d/m/Y H:i') ?></div>
    </div>
    <div class="titre-doc">Produits — Famille</div>
    <div class="numero-doc"><?= htmlspecialchars($famille['libelle']) ?></div>
    <div style="display:flex;gap:8mm;margin-bottom:8mm">
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:22px;font-weight:900;color:#7c3aed"><?= (int)$stats['nb'] ?></div>
            <div style="font-size:10px;color:#64748b">Produits</div>
        </div>
        <div style="flex:1;background:#f8fafc;border:1px solid #e2e8f0;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:22px;font-weight:900;color:#065f46"><?= number_format((float)$stats['valeur_totale'],0,',',' ') ?> FCFA</div>
            <div style="font-size:10px;color:#64748b">Valeur stock</div>
        </div>
        <div style="flex:1;background:<?= (int)$stats['en_alerte']>0?'#fef2f2':'#f8fafc' ?>;border:1px solid <?= (int)$stats['en_alerte']>0?'#fca5a5':'#e2e8f0' ?>;border-radius:4px;padding:4mm;text-align:center">
            <div style="font-size:22px;font-weight:900;color:<?= (int)$stats['en_alerte']>0?'#991b1b':'#94a3b8' ?>"><?= (int)$stats['en_alerte'] ?></div>
            <div style="font-size:10px;color:#64748b">En alerte</div>
        </div>
    </div>
    <table class="tableau">
        <thead><tr>
            <th style="width:13%">Code</th><th>Désignation</th><th>Père</th>
            <th class="r" style="width:10%">Stock</th><th class="r" style="width:8%">Unité</th>
            <th class="r" style="width:10%">Seuil</th>
            <th class="r" style="width:14%">Prix achat</th>
            <th class="r" style="width:14%">Prix vente</th>
            <th class="r" style="width:13%">Valeur</th>
        </tr></thead>
        <tbody>
        <?php foreach ($produits as $p): ?>
        <tr <?= $p['en_alerte'] ? 'style="background:#fef2f2"' : '' ?>>
            <td style="font-family:monospace"><?= htmlspecialchars($p['code']) ?></td>
            <td><?= htmlspecialchars($p['designation']) ?> <?= $p['en_alerte'] ? '<span style="color:#ef4444">⚠</span>' : '' ?></td>
            <td style="font-size:10px;color:#64748b"><?= htmlspecialchars($p['produit_pere']??'—') ?></td>
            <td class="r" style="font-weight:<?= $p['en_alerte']?'700':'400' ?>;color:<?= $p['en_alerte']?'#991b1b':'inherit' ?>"><?= number_format((float)$p['stock_actuel'],2,',',' ') ?></td>
            <td class="r"><?= htmlspecialchars($p['unite']) ?></td>
            <td class="r"><?= number_format((float)$p['stock_alerte'],2,',',' ') ?></td>
            <td class="r"><?= number_format((float)$p['prix_achat'],0,',',' ') ?></td>
            <td class="r"><?= number_format((float)$p['prix_vente'],0,',',' ') ?></td>
            <td class="r"><?= number_format((float)$p['valeur_stock'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr><td colspan="8">Total valeur</td><td class="r"><?= number_format((float)$stats['valeur_totale'],0,',',' ') ?> FCFA</td></tr></tfoot>
    </table>
    <div class="pied"><span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span><span><?= htmlspecialchars($famille['libelle']) ?></span></div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
