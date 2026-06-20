<?php
$layoutFile = __DIR__ . '/layout_print.php';
ob_start();
?>
<?php
$titreDocument = 'Bon de Commande — ' . $cmd['numero'];
$contenu = ob_start() ?: '';
?>
<div class="btn-print">
    <button class="btn-p" onclick="window.print()"><span>🖨</span> Imprimer</button>
    <a class="btn-r" href="javascript:history.back()">← Retour</a>
</div>
<div class="page">
    <div class="entete">
        <div>
            <div class="entete-logo">StockManager<small>Système de Gestion de Stock</small></div>
        </div>
        <div class="entete-info">
            <strong>Date :</strong> <?= (new DateTime())->format('d/m/Y') ?><br>
            <strong>Imprimé par :</strong> <?= htmlspecialchars($_SESSION['user_login']??'') ?>
        </div>
    </div>

    <div class="titre-doc">Bon de Commande Fournisseur</div>
    <div class="numero-doc"><?= htmlspecialchars($cmd['numero']) ?></div>

    <div class="bloc-infos">
        <div>
            <h4>Fournisseur</h4>
            <p><strong><?= htmlspecialchars($cmd['fournisseur']) ?></strong></p>
        </div>
        <div>
            <h4>Informations commande</h4>
            <p><strong>Date :</strong> <?= (new DateTime($cmd['date_document']))->format('d/m/Y') ?></p>
            <p><strong>Statut :</strong> <span class="badge badge-<?= $cmd['statut'] ?>"><?= ucfirst(str_replace('_',' ',$cmd['statut'])) ?></span></p>
            <p><strong>Créée par :</strong> <?= htmlspecialchars($cmd['cree_par']) ?></p>
        </div>
    </div>

    <table class="tableau">
        <thead>
            <tr>
                <th style="width:15%">Code</th>
                <th>Désignation</th>
                <th class="r" style="width:12%">Quantité</th>
                <th class="r" style="width:12%">Unité</th>
                <th class="r" style="width:18%">Prix unitaire</th>
                <th class="r" style="width:18%">Montant</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cmd['lignes'] as $l): ?>
        <tr>
            <td><?= htmlspecialchars($l['code']) ?></td>
            <td><?= htmlspecialchars($l['designation']) ?></td>
            <td class="r"><?= number_format((float)$l['quantite'],2,',',' ') ?></td>
            <td class="r"><?= htmlspecialchars($l['unite']) ?></td>
            <td class="r"><?= number_format((float)$l['prix_unitaire'],0,',',' ') ?> FCFA</td>
            <td class="r"><?= number_format((float)$l['montant_ligne'],0,',',' ') ?> FCFA</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Total Général</td>
                <td class="r"><?= number_format((float)$cmd['montant_total'],0,',',' ') ?> FCFA</td>
            </tr>
        </tfoot>
    </table>

    <?php if (!empty($cmd['observations'])): ?>
    <p style="font-size:11px; margin-bottom:8mm; padding:3mm; background:#f8fafc; border-left:3px solid #7c3aed;">
        <strong>Observations :</strong> <?= htmlspecialchars($cmd['observations']) ?>
    </p>
    <?php endif; ?>

    <div class="signatures">
        <div class="signature-box">
            <div class="ligne-sign"></div>
            <p>Responsable des achats</p>
        </div>
        <div class="signature-box">
            <div class="ligne-sign"></div>
            <p>Fournisseur / Cachet</p>
        </div>
        <div class="signature-box">
            <div class="ligne-sign"></div>
            <p>Direction</p>
        </div>
    </div>

    <div class="pied">
        <span>StockManager — Document généré le <?= (new DateTime())->format('d/m/Y à H:i') ?></span>
        <span><?= htmlspecialchars($cmd['numero']) ?></span>
    </div>
</div>

<?php
$contenu = ob_get_clean();
include $layoutFile;
