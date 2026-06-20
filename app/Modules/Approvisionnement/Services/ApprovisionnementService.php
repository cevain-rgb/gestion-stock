<?php
declare(strict_types=1);
namespace App\Modules\Approvisionnement\Services;

use App\Modules\Approvisionnement\Models\{CommandeFournisseurModel, ReceptionModel,
    FactureFournisseurModel, ReglementFournisseurModel, DonModel};
use App\Modules\Structure\Models\{ProduitModel, FournisseurModel};
use App\Shared\Traits\AuditableTrait;
use App\Core\Database;

class ApprovisionnementService
{
    use AuditableTrait;

    private CommandeFournisseurModel  $commandes;
    private ReceptionModel            $receptions;
    private FactureFournisseurModel   $factures;
    private ReglementFournisseurModel $reglements;
    private DonModel                  $dons;
    private ProduitModel              $produits;
    private FournisseurModel          $fournisseurs;

    public function __construct()
    {
        $this->commandes    = new CommandeFournisseurModel();
        $this->receptions   = new ReceptionModel();
        $this->factures     = new FactureFournisseurModel();
        $this->reglements   = new ReglementFournisseurModel();
        $this->dons         = new DonModel();
        $this->produits     = new ProduitModel();
        $this->fournisseurs = new FournisseurModel();
    }

    // ═══ COMMANDES FOURNISSEURS ═════════════════════════════════════════════

    public function listerCommandes(int $page, array $filtres): array
    {
        $total = $this->commandes->compter($filtres);
        return ['lignes' => $this->commandes->tous($page, 20, $filtres), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'filtres' => $filtres, 'fournisseurs' => $this->fournisseurs->tousOptions(),
                'stats' => $this->commandes->statsResumees()];
    }

    public function detailCommande(int $id): array|false
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return false;
        $cmd['lignes'] = $this->commandes->lignes($id);
        $cmd['a_facture'] = $this->factures->existeePourCommande($id);
        return $cmd;
    }

    public function creerCommande(array $d, int $idUtilisateur): array
    {
        if (empty($d['id_fournisseur'])) return ['ok'=>false,'errors'=>['id_fournisseur'=>'Fournisseur obligatoire.']];
        $lignes = $this->extraireLignes($d);
        if (empty($lignes)) return ['ok'=>false,'errors'=>['lignes'=>'Au moins une ligne de produit est requise.']];

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $id = $this->commandes->creer((int)$d['id_fournisseur'], $idUtilisateur, trim($d['observations'] ?? ''));
            foreach ($lignes as $l) {
                $this->commandes->ajouterLigne($id, $l['id_produit'], $l['quantite'], $l['prix_unitaire']);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            return ['ok'=>false,'errors'=>['global'=>'Erreur lors de la création : '.$e->getMessage()]];
        }

        $this->journaliser('INSERT', 'commande_fournisseur', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierLignesCommande(int $id, array $d): array
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return ['ok'=>false,'message'=>'Commande introuvable.'];
        if ($cmd['statut'] !== 'en_attente') return ['ok'=>false,'message'=>'Seules les commandes en attente sont modifiables.'];

        $lignes = $this->extraireLignes($d);
        if (empty($lignes)) return ['ok'=>false,'message'=>'Au moins une ligne est requise.'];

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $this->commandes->viderLignes($id);
            foreach ($lignes as $l) {
                $this->commandes->ajouterLigne($id, $l['id_produit'], $l['quantite'], $l['prix_unitaire']);
            }
            $this->commandes->modifierObservations($id, trim($d['observations'] ?? ($cmd['observations'] ?? '')));
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            return ['ok'=>false,'message'=>'Erreur : '.$e->getMessage()];
        }
        $this->journaliser('UPDATE', 'commande_fournisseur', $id, $cmd, $d);
        return ['ok'=>true];
    }

    public function validerCommande(int $id): array
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return ['ok'=>false,'message'=>'Commande introuvable.'];
        if ($cmd['statut'] !== 'en_attente') return ['ok'=>false,'message'=>'Cette commande a déjà été traitée.'];
        if ($this->commandes->nbLignes($id) === 0) return ['ok'=>false,'message'=>'La commande ne contient aucune ligne.'];

        $this->commandes->changerStatut($id, 'validee');
        $this->journaliser('UPDATE', 'commande_fournisseur', $id, ['statut'=>'en_attente'], ['statut'=>'validee']);
        return ['ok'=>true];
    }

    public function annulerCommande(int $id): array
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return ['ok'=>false,'message'=>'Commande introuvable.'];
        if ($cmd['statut'] === 'recue') return ['ok'=>false,'message'=>'Une commande déjà reçue ne peut être annulée.'];

        $this->commandes->changerStatut($id, 'annulee');
        $this->journaliser('UPDATE', 'commande_fournisseur', $id, ['statut'=>$cmd['statut']], ['statut'=>'annulee']);
        return ['ok'=>true];
    }

    public function supprimerCommande(int $id): array
    {
        if ($this->commandes->aDesReceptions($id))
            return ['ok'=>false,'message'=>'Impossible : des réceptions sont associées à cette commande.'];
        $ancien = $this->commandes->trouverParId($id);
        $this->archivageXml('commande_fournisseur', $id, $ancien ?: []);
        $this->commandes->supprimer($id);
        $this->journaliser('DELETE', 'commande_fournisseur', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ RÉCEPTIONS ═════════════════════════════════════════════════════════

    public function listerReceptions(int $page, array $filtres): array
    {
        $total = $this->receptions->compter($filtres);
        return ['lignes' => $this->receptions->tous($page, 20, $filtres), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'filtres' => $filtres];
    }

    public function detailReception(int $id): array|false
    {
        $r = $this->receptions->trouverParId($id);
        if (!$r) return false;
        $r['lignes'] = $this->receptions->lignes($id);
        return $r;
    }

    /** Prépare les données nécessaires pour créer une réception depuis une commande validée */
    public function preparerReception(int $idCommande): array|false
    {
        $cmd = $this->commandes->trouverParId($idCommande);
        if (!$cmd || $cmd['statut'] !== 'validee') return false;
        $cmd['lignes']         = $this->commandes->lignes($idCommande);
        $cmd['qte_recues']     = $this->receptions->quantitesRecuesParCommande($idCommande);
        return $cmd;
    }

    public function creerReception(int $idCommande, array $d, int $idUtilisateur): array
    {
        $cmd = $this->commandes->trouverParId($idCommande);
        if (!$cmd) return ['ok'=>false,'errors'=>['global'=>'Commande introuvable.']];
        if ($cmd['statut'] !== 'validee') return ['ok'=>false,'errors'=>['global'=>'La commande doit être validée pour recevoir.']];

        $lignes = $this->extraireLignesReception($d);
        if (empty($lignes)) return ['ok'=>false,'errors'=>['lignes'=>'Au moins une ligne reçue est requise.']];

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $idReception = $this->receptions->creer($idCommande, $idUtilisateur, trim($d['observations'] ?? ''));
            foreach ($lignes as $l) {
                // Le trigger trg_reception_stock incrémente automatiquement produit.stock_actuel
                $this->receptions->ajouterLigne($idReception, $l['id_produit'], $l['quantite'], $l['prix_unitaire']);
            }

            // Vérifier si toutes les quantités commandées ont été reçues → passer la commande à "reçue"
            $lignesCmd  = $this->commandes->lignes($idCommande);
            $qteRecues  = $this->receptions->quantitesRecuesParCommande($idCommande);
            $complet = true;
            foreach ($lignesCmd as $lc) {
                $recu = (float)($qteRecues[$lc['id_produit']] ?? 0);
                if ($recu < (float)$lc['quantite']) { $complet = false; break; }
            }
            if ($complet) {
                $this->commandes->changerStatut($idCommande, 'recue');
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            return ['ok'=>false,'errors'=>['global'=>'Erreur : '.$e->getMessage()]];
        }

        $this->journaliser('INSERT', 'bon_reception', $idReception, null, $d);
        return ['ok'=>true,'id'=>$idReception];
    }

    public function supprimerReception(int $id): array
    {
        $ancien = $this->receptions->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'message'=>'Réception introuvable.'];
        // Remarque : le retrait du stock correspondant n'est pas automatique (cohérence comptable
        // exige un bon de sortie dédié plutôt qu'une suppression silencieuse).
        $this->archivageXml('bon_reception', $id, $ancien);
        $this->receptions->supprimer($id);
        $this->journaliser('DELETE', 'bon_reception', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ FACTURES FOURNISSEURS ══════════════════════════════════════════════

    public function listerFactures(int $page, array $filtres): array
    {
        $total = $this->factures->compter($filtres);
        return ['lignes' => $this->factures->tous($page, 20, $filtres), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'filtres' => $filtres, 'stats' => $this->factures->statsResumees()];
    }

    public function detailFacture(int $id): array|false
    {
        $f = $this->factures->trouverParId($id);
        if (!$f) return false;
        $f['reglements'] = $this->factures->reglements($id);
        return $f;
    }

    public function commandesFacturables(): array
    {
        // Commandes reçues sans facture
        return Database::getInstance()->fetchAll(
            "SELECT cf.oid_doc, cf.numero, cf.montant_total, f.nom AS fournisseur
             FROM commande_fournisseur cf
             JOIN fournisseur f ON f.id_fournisseur = cf.id_fournisseur
             WHERE cf.statut='recue' AND cf.deleted_at IS NULL
               AND NOT EXISTS (SELECT 1 FROM facture_fournisseur ff WHERE ff.id_commande_f=cf.oid_doc AND ff.deleted_at IS NULL)
             ORDER BY cf.date_document DESC");
    }

    public function creerFacture(array $d, int $idUtilisateur): array
    {
        if (empty($d['id_commande_f'])) return ['ok'=>false,'errors'=>['id_commande_f'=>'Commande obligatoire.']];
        $cmd = $this->commandes->trouverParId((int)$d['id_commande_f']);
        if (!$cmd) return ['ok'=>false,'errors'=>['id_commande_f'=>'Commande introuvable.']];
        if ($cmd['statut'] !== 'recue') return ['ok'=>false,'errors'=>['global'=>'La commande doit être entièrement reçue.']];
        if ($this->factures->existeePourCommande((int)$d['id_commande_f']))
            return ['ok'=>false,'errors'=>['global'=>'Une facture existe déjà pour cette commande.']];

        $montantHt = (float)($d['montant_ht'] ?? 0);
        $tauxTva   = (float)($d['taux_tva'] ?? 0);
        if ($montantHt <= 0) return ['ok'=>false,'errors'=>['montant_ht'=>'Montant HT invalide.']];

        $id = $this->factures->creer((int)$d['id_commande_f'], $idUtilisateur, $montantHt, $tauxTva);
        $this->journaliser('INSERT', 'facture_fournisseur', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function supprimerFacture(int $id): array
    {
        $ancien = $this->factures->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'message'=>'Facture introuvable.'];
        if ((float)$ancien['reste_a_payer'] < (float)$ancien['montant_ttc'])
            return ['ok'=>false,'message'=>'Impossible : des règlements existent déjà sur cette facture.'];
        $this->archivageXml('facture_fournisseur', $id, $ancien);
        $this->factures->supprimer($id);
        $this->journaliser('DELETE', 'facture_fournisseur', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ RÈGLEMENTS FOURNISSEURS ════════════════════════════════════════════

    public function ajouterReglement(int $idFacture, array $d): array
    {
        $facture = $this->factures->trouverParId($idFacture);
        if (!$facture) return ['ok'=>false,'errors'=>['global'=>'Facture introuvable.']];

        $montant = (float)($d['montant'] ?? 0);
        if ($montant <= 0) return ['ok'=>false,'errors'=>['montant'=>'Montant invalide.']];
        if ($montant > (float)$facture['reste_a_payer'] + 0.01)
            return ['ok'=>false,'errors'=>['montant'=>'Le montant dépasse le reste à payer ('.number_format((float)$facture['reste_a_payer'],0,',',' ').' FCFA).']];

        $idBanque = !empty($d['id_banque']) ? (int)$d['id_banque'] : null;
        $id = $this->reglements->creer($idFacture, $idBanque, $montant,
            $d['date_reglement'] ?? date('Y-m-d'), $d['mode_paiement'] ?? 'especes', trim($d['reference'] ?? ''));

        $this->factures->mettreAJourStatutPaiement($idFacture);
        $this->journaliser('INSERT', 'reglement_fournisseur', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function supprimerReglement(int $idReglement, int $idFacture): array
    {
        $r = $this->reglements->trouverParId($idReglement);
        if (!$r) return ['ok'=>false,'message'=>'Règlement introuvable.'];
        $this->reglements->supprimer($idReglement);
        $this->factures->mettreAJourStatutPaiement($idFacture);
        $this->journaliser('DELETE', 'reglement_fournisseur', $idReglement, $r, null);
        return ['ok'=>true];
    }

    // ═══ DONS ════════════════════════════════════════════════════════════════

    public function listerDons(int $page, array $filtres): array
    {
        $total = $this->dons->compter($filtres);
        return ['lignes' => $this->dons->tous($page, 20, $filtres), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'filtres' => $filtres, 'fournisseurs' => $this->fournisseurs->tousOptions()];
    }

    public function detailDon(int $id): array|false
    {
        $d = $this->dons->trouverParId($id);
        if (!$d) return false;
        $d['lignes'] = $this->dons->lignes($id);
        return $d;
    }

    public function creerDon(array $d, int $idUtilisateur): array
    {
        $lignes = $this->extraireLignesDon($d);
        if (empty($lignes)) return ['ok'=>false,'errors'=>['lignes'=>'Au moins une ligne de produit est requise.']];

        $idFournisseur = !empty($d['id_fournisseur']) ? (int)$d['id_fournisseur'] : null;

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $id = $this->dons->creer($idFournisseur, $idUtilisateur, trim($d['observations'] ?? ''));
            foreach ($lignes as $l) {
                $this->dons->ajouterLigne($id, $l['id_produit'], $l['quantite'], $l['valeur_unitaire']);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            return ['ok'=>false,'errors'=>['global'=>'Erreur : '.$e->getMessage()]];
        }

        $this->journaliser('INSERT', 'don', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function supprimerDon(int $id): array
    {
        $ancien = $this->dons->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'message'=>'Don introuvable.'];
        $this->archivageXml('don', $id, $ancien);
        $this->dons->supprimer($id);
        $this->journaliser('DELETE', 'don', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ Helpers extraction de lignes depuis $_POST ════════════════════════

    private function extraireLignes(array $d): array
    {
        $lignes = [];
        $ids  = $d['produit_id']      ?? [];
        $qtes = $d['quantite']        ?? [];
        $prix = $d['prix_unitaire']   ?? [];
        foreach ($ids as $i => $idProduit) {
            if (empty($idProduit) || empty($qtes[$i])) continue;
            $lignes[] = ['id_produit' => (int)$idProduit, 'quantite' => (float)$qtes[$i], 'prix_unitaire' => (float)($prix[$i] ?? 0)];
        }
        return $lignes;
    }

    private function extraireLignesReception(array $d): array
    {
        $lignes = [];
        $ids  = $d['produit_id']      ?? [];
        $qtes = $d['quantite_recue']  ?? [];
        $prix = $d['prix_unitaire']   ?? [];
        foreach ($ids as $i => $idProduit) {
            if (empty($idProduit) || empty($qtes[$i]) || (float)$qtes[$i] <= 0) continue;
            $lignes[] = ['id_produit' => (int)$idProduit, 'quantite' => (float)$qtes[$i], 'prix_unitaire' => (float)($prix[$i] ?? 0)];
        }
        return $lignes;
    }

    private function extraireLignesDon(array $d): array
    {
        $lignes = [];
        $ids  = $d['produit_id']       ?? [];
        $qtes = $d['quantite']         ?? [];
        $vals = $d['valeur_unitaire']  ?? [];
        foreach ($ids as $i => $idProduit) {
            if (empty($idProduit) || empty($qtes[$i])) continue;
            $lignes[] = ['id_produit' => (int)$idProduit, 'quantite' => (float)$qtes[$i], 'valeur_unitaire' => (float)($vals[$i] ?? 0)];
        }
        return $lignes;
    }

    // ═══ Accesseurs modèles ═════════════════════════════════════════════════
    public function getCommandeModel():  CommandeFournisseurModel  { return $this->commandes; }
    public function getReceptionModel(): ReceptionModel            { return $this->receptions; }
    public function getFactureModel():   FactureFournisseurModel   { return $this->factures; }
    public function getProduitModel():   ProduitModel              { return $this->produits; }
}
