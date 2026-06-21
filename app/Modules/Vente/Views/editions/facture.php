<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php $titreDocument = 'Facture — ' . $facture['numero']; ?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()"><span>🖨</span> Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info">
            <strong>Date édition :</strong> <?= (new DateTime())->format('d/m/Y') ?>
        </div>
    </div>
    <div class="titre-doc">Facture Client</div>
    <div class="numero-doc"><?= htmlspecialchars($facture['numero']) ?></div>
    <div class="bloc-infos">
        <div>
            <h4>Client</h4>
            <p><strong><?= htmlspecialchars($facture['client']) ?></strong></p>
            <p>Commande n° : <?= htmlspecialchars($facture['numero_commande']) ?></p>
        </div>
        <div>
            <h4>Facture</h4>
            <p><strong>Date :</strong> <?= (new DateTime($facture['date_document']))->format('d/m/Y') ?></p>
            <p><strong>Statut :</strong> <span class="badge badge-<?= $facture['statut_paiement'] ?>"><?= ucfirst($facture['statut_paiement']) ?></span></p>
        </div>
    </div>

    <!-- Détail des articles -->
    <?php if (!empty($lignes)): ?>
    <table class="tableau" style="margin-bottom:6mm">
        <thead><tr>
            <th style="width:13%">Code</th><th>Désignation</th>
            <th class="r" style="width:11%">Qté</th>
            <th class="r" style="width:10%">Unité</th>
            <th class="r" style="width:15%">Prix unit.</th>
            <th class="r" style="width:10%">Remise</th>
            <th class="r" style="width:16%">Montant</th>
        </tr></thead>
        <tbody>
        <?php foreach ($lignes as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['code']) ?></td>
            <td><?= htmlspecialchars($l['designation']) ?></td>
            <td class="r"><?= number_format((float)$l['quantite'],2,',',' ') ?></td>
            <td class="r"><?= htmlspecialchars($l['unite']) ?></td>
            <td class="r"><?= number_format((float)$l['prix_unitaire'],0,',',' ') ?> FCFA</td>
            <td class="r"><?= (float)$l['remise_pct']>0 ? $l['remise_pct'].'%' : '—' ?></td>
            <td class="r"><?= number_format((float)$l['montant_ligne'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Totaux -->
    <div class="total-box">
        <div class="total-row"><span class="total-label">Montant HT</span><span class="total-value"><?= number_format((float)$facture['montant_ht'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row"><span class="total-label">TVA (<?= $facture['taux_tva'] ?>%)</span><span class="total-value"><?= number_format((float)$facture['montant_tva'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row total-ttc"><span class="total-label">Total TTC</span><span class="total-value"><?= number_format((float)$facture['montant_ttc'],0,',',' ') ?> FCFA</span></div>
        <div class="total-row" style="margin-top:2mm"><span class="total-label">Reste à payer</span>
            <span class="total-value" style="color:<?= (float)$facture['reste_a_payer']>0?'#991b1b':'#065f46' ?>">
                <?= number_format((float)$facture['reste_a_payer'],0,',',' ') ?> FCFA
            </span>
        </div>
    </div>

    <!-- Règlements -->
    <?php if (!empty($facture['reglements'])): ?>
    <p style="font-size:11px;font-weight:700;margin-bottom:3mm">Règlements enregistrés :</p>
    <table class="tableau">
        <thead><tr><th>Date</th><th>Mode</th><th>Référence</th><th class="r">Montant</th></tr></thead>
        <tbody>
        <?php foreach ($facture['reglements'] as $r): ?>
        <tr>
            <td><?= (new DateTime($r['date_reglement']))->format('d/m/Y') ?></td>
            <td><?= htmlspecialchars(ucfirst(str_replace('_',' ',$r['mode_paiement']))) ?></td>
            <td><?= htmlspecialchars($r['reference']?:'—') ?></td>
            <td class="r"><?= number_format((float)$r['montant'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <div class="signatures" style="margin-top:10mm">
        <div class="signature-box"><div class="ligne-sign"></div><p>Comptabilité</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Client (signature + cachet)</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Direction</p></div>
    </div>
    <div class="pied">
        <span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span>
        <span><?= htmlspecialchars($facture['numero']) ?></span>
    </div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
