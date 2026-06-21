<?php
$layoutFile = BASE_PATH . '/app/Modules/Approvisionnement/Views/editions/layout_print.php';
ob_start();
?>
<?php
$titreDocument = 'Bon de Sortie — ' . $sortie['numero'];
$labelsMotif = ['perime'=>'Périmé','casse'=>'Cassé','perte'=>'Perte','offert'=>'Offert','autre'=>'Autre'];
?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()"><span>🖨</span> Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        <div class="entete-info">
            <strong>Date :</strong> <?= (new DateTime())->format('d/m/Y') ?><br>
            <strong>Imprimé par :</strong> <?= htmlspecialchars($_SESSION['user_login']??'') ?>
        </div>
    </div>
    <div class="titre-doc">Bon de Sortie</div>
    <div class="numero-doc"><?= htmlspecialchars($sortie['numero']) ?></div>
    <div class="bloc-infos">
        <div>
            <h4>Motif de sortie</h4>
            <p><strong style="font-size:14px"><?= htmlspecialchars($labelsMotif[$sortie['motif']] ?? $sortie['motif']) ?></strong></p>
        </div>
        <div>
            <h4>Informations</h4>
            <p><strong>Date :</strong> <?= (new DateTime($sortie['date_document']))->format('d/m/Y') ?></p>
            <p><strong>Établi par :</strong> <?= htmlspecialchars($sortie['cree_par']) ?></p>
        </div>
    </div>
    <?php if (!empty($sortie['observations'])): ?>
    <p style="font-size:11px;padding:3mm;background:#fef2f2;border-left:3px solid #ef4444;margin-bottom:6mm;">
        <strong>Observations :</strong> <?= htmlspecialchars($sortie['observations']) ?>
    </p>
    <?php endif; ?>
    <table class="tableau">
        <thead><tr>
            <th style="width:13%">Code</th><th>Désignation</th>
            <th class="r" style="width:12%">Quantité</th>
            <th class="r" style="width:10%">Unité</th>
            <th class="r" style="width:16%">Valeur unit.</th>
            <th class="r" style="width:16%">Montant</th>
            <th style="width:20%">Détail</th>
        </tr></thead>
        <tbody>
        <?php foreach ($sortie['lignes'] as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['code']) ?></td>
            <td><?= htmlspecialchars($l['designation']) ?></td>
            <td class="r" style="font-weight:700;color:#991b1b"><?= number_format((float)$l['quantite'],2,',',' ') ?></td>
            <td class="r"><?= htmlspecialchars($l['unite']) ?></td>
            <td class="r"><?= number_format((float)$l['valeur_unitaire'],0,',',' ') ?> FCFA</td>
            <td class="r"><?= number_format((float)$l['montant_ligne'],0,',',' ') ?> FCFA</td>
            <td style="font-size:10px;color:#64748b"><?= htmlspecialchars($l['motif_detail']?:'') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot><tr>
            <td colspan="5">Valeur totale sortie</td>
            <td class="r"><?= number_format(array_sum(array_column($sortie['lignes'],'montant_ligne')),0,',',' ') ?> FCFA</td>
            <td></td>
        </tr></tfoot>
    </table>
    <div class="signatures">
        <div class="signature-box"><div class="ligne-sign"></div><p>Magasinier</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Responsable stocks</p></div>
        <div class="signature-box"><div class="ligne-sign"></div><p>Direction</p></div>
    </div>
    <div class="pied">
        <span>StockManager — <?= (new DateTime())->format('d/m/Y à H:i') ?></span>
        <span><?= htmlspecialchars($sortie['numero']) ?></span>
    </div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
