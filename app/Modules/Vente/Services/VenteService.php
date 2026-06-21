<?php
declare(strict_types=1);
namespace App\Modules\Vente\Services;

use App\Modules\Vente\Models\{CommandeClientModel, LivraisonModel, FactureClientModel,
    ReglementClientModel, SortieModel};
use App\Modules\Structure\Models\{ProduitModel, ClientModel};
use App\Shared\Traits\AuditableTrait;
use App\Core\Database;

class VenteService
{
    use AuditableTrait;

    private CommandeClientModel  $commandes;
    private LivraisonModel       $livraisons;
    private FactureClientModel   $factures;
    private ReglementClientModel $reglements;
    private SortieModel          $sorties;
    private ProduitModel         $produits;
    private ClientModel          $clients;

    public function __construct()
    {
        $this->commandes  = new CommandeClientModel();
        $this->livraisons = new LivraisonModel();
        $this->factures   = new FactureClientModel();
        $this->reglements = new ReglementClientModel();
        $this->sorties     = new SortieModel();
        $this->produits   = new ProduitModel();
        $this->clients    = new ClientModel();
    }

    // ═══ COMMANDES CLIENTS ═══════════════════════════════════════════════════

    public function listerCommandes(int $page, array $filtres): array
    {
        $total = $this->commandes->compter($filtres);
        return ['lignes' => $this->commandes->tous($page, 20, $filtres), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'filtres' => $filtres, 'clients' => $this->clients->tousOptions(),
                'stats' => $this->commandes->statsResumees()];
    }

    public function detailCommande(int $id): array|false
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return false;
        $cmd['lignes']    = $this->commandes->lignes($id);
        $cmd['a_facture'] = $this->factures->existeePourCommande($id);
        return $cmd;
    }

    public function creerCommande(array $d, int $idUtilisateur, bool $estComptant = false): array
    {
        if (empty($d['id_client'])) return ['ok'=>false,'errors'=>['id_client'=>'Client obligatoire.']];
        $lignes = $this->extraireLignes($d);
        if (empty($lignes)) return ['ok'=>false,'errors'=>['lignes'=>'Au moins une ligne de produit est requise.']];

        // Remise automatique de la catégorie client si non précisée par ligne
        $client       = $this->clients->trouverParId((int)$d['id_client']);
        $remiseDefaut = $client ? (float)$client['remise_pct'] : 0;

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $id = $this->commandes->creer((int)$d['id_client'], $idUtilisateur, trim($d['observations'] ?? ''), $estComptant);
            foreach ($lignes as $l) {
                $remise = $l['remise_pct'] > 0 ? $l['remise_pct'] : $remiseDefaut;
                $this->commandes->ajouterLigne($id, $l['id_produit'], $l['quantite'], $l['prix_unitaire'], $remise);
            }

            if ($estComptant) {
                // Vente comptant : validation, livraison, facture et règlement immédiats
                $this->commandes->changerStatut($id, 'validee');
                $idLivraison = $this->livraisons->creer($id, $idUtilisateur, 'Vente comptant — livraison immédiate');
                foreach ($lignes as $l) {
                    $this->livraisons->ajouterLigne($idLivraison, $l['id_produit'], $l['quantite'], $l['prix_unitaire']);
                }
                $this->commandes->changerStatut($id, 'livree');

                $cmdFraiche = $this->commandes->trouverParId($id);
                $idFacture  = $this->factures->creer($id, $idUtilisateur, (float)$cmdFraiche['montant_total'], (float)($d['taux_tva'] ?? 0));
                $facture    = $this->factures->trouverParId($idFacture);

                if (!empty($d['montant_paye']) && (float)$d['montant_paye'] > 0) {
                    $montantPaye = min((float)$d['montant_paye'], (float)$facture['montant_ttc']);
                    $this->reglements->creer($idFacture, null, $montantPaye, date('Y-m-d'), $d['mode_paiement'] ?? 'especes', '');
                    $this->factures->mettreAJourStatutPaiement($idFacture);
                }
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            return ['ok'=>false,'errors'=>['global'=>'Erreur : '.$e->getMessage()]];
        }

        $this->journaliser('INSERT', $estComptant ? 'vente_comptant' : 'commande_client', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function modifierLignesCommande(int $id, array $d): array
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return ['ok'=>false,'message'=>'Commande introuvable.'];
        if ($cmd['statut'] !== 'en_attente') return ['ok'=>false,'message'=>'Seules les commandes en attente sont modifiables.'];

        $lignes = $this->extraireLignes($d);
        if (empty($lignes)) return ['ok'=>false,'message'=>'Au moins une ligne est requise.'];

        $client       = $this->clients->trouverParId((int)($cmd['id_client'] ?? 0));
        $remiseDefaut = $client ? (float)$client['remise_pct'] : 0;

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $this->commandes->viderLignes($id);
            foreach ($lignes as $l) {
                $remise = $l['remise_pct'] > 0 ? $l['remise_pct'] : $remiseDefaut;
                $this->commandes->ajouterLigne($id, $l['id_produit'], $l['quantite'], $l['prix_unitaire'], $remise);
            }
            $this->commandes->modifierObservations($id, trim($d['observations'] ?? ($cmd['observations'] ?? '')));
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            return ['ok'=>false,'message'=>'Erreur : '.$e->getMessage()];
        }
        $this->journaliser('UPDATE', 'commande_client', $id, $cmd, $d);
        return ['ok'=>true];
    }

    public function validerCommande(int $id): array
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return ['ok'=>false,'message'=>'Commande introuvable.'];
        if ($cmd['statut'] !== 'en_attente') return ['ok'=>false,'message'=>'Cette commande a déjà été traitée.'];
        if ($this->commandes->nbLignes($id) === 0) return ['ok'=>false,'message'=>'La commande ne contient aucune ligne.'];

        $this->commandes->changerStatut($id, 'validee');
        $this->journaliser('UPDATE', 'commande_client', $id, ['statut'=>'en_attente'], ['statut'=>'validee']);
        return ['ok'=>true];
    }

    public function annulerCommande(int $id): array
    {
        $cmd = $this->commandes->trouverParId($id);
        if (!$cmd) return ['ok'=>false,'message'=>'Commande introuvable.'];
        if ($cmd['statut'] === 'livree') return ['ok'=>false,'message'=>'Une commande déjà livrée ne peut être annulée.'];

        $this->commandes->changerStatut($id, 'annulee');
        $this->journaliser('UPDATE', 'commande_client', $id, ['statut'=>$cmd['statut']], ['statut'=>'annulee']);
        return ['ok'=>true];
    }

    public function supprimerCommande(int $id): array
    {
        if ($this->commandes->aDesLivraisons($id))
            return ['ok'=>false,'message'=>'Impossible : des livraisons sont associées à cette commande.'];
        $ancien = $this->commandes->trouverParId($id);
        $this->archivageXml('commande_client', $id, $ancien ?: []);
        $this->commandes->supprimer($id);
        $this->journaliser('DELETE', 'commande_client', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ LIVRAISONS ══════════════════════════════════════════════════════════

    public function listerLivraisons(int $page, array $filtres): array
    {
        $total = $this->livraisons->compter($filtres);
        return ['lignes' => $this->livraisons->tous($page, 20, $filtres), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'filtres' => $filtres];
    }

    public function detailLivraison(int $id): array|false
    {
        $l = $this->livraisons->trouverParId($id);
        if (!$l) return false;
        $l['lignes'] = $this->livraisons->lignes($id);
        return $l;
    }

    public function preparerLivraison(int $idCommande): array|false
    {
        $cmd = $this->commandes->trouverParId($idCommande);
        if (!$cmd || $cmd['statut'] !== 'validee') return false;
        $cmd['lignes']       = $this->commandes->lignes($idCommande);
        $cmd['qte_livrees']  = $this->livraisons->quantitesLivreesParCommande($idCommande);
        return $cmd;
    }

    public function creerLivraison(int $idCommande, array $d, int $idUtilisateur): array
    {
        $cmd = $this->commandes->trouverParId($idCommande);
        if (!$cmd) return ['ok'=>false,'errors'=>['global'=>'Commande introuvable.']];
        if ($cmd['statut'] !== 'validee') return ['ok'=>false,'errors'=>['global'=>'La commande doit être validée pour livrer.']];

        $lignes = $this->extraireLignesLivraison($d);
        if (empty($lignes)) return ['ok'=>false,'errors'=>['lignes'=>'Au moins une ligne livrée est requise.']];

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $idLivraison = $this->livraisons->creer($idCommande, $idUtilisateur, trim($d['observations'] ?? ''));
            foreach ($lignes as $l) {
                // Le trigger BEFORE INSERT vérifie le stock et lève une exception si insuffisant
                $this->livraisons->ajouterLigne($idLivraison, $l['id_produit'], $l['quantite'], $l['prix_unitaire']);
            }

            $lignesCmd  = $this->commandes->lignes($idCommande);
            $qteLivrees = $this->livraisons->quantitesLivreesParCommande($idCommande);
            $complet = true;
            foreach ($lignesCmd as $lc) {
                $livre = (float)($qteLivrees[$lc['id_produit']] ?? 0);
                if ($livre < (float)$lc['quantite']) { $complet = false; break; }
            }
            if ($complet) $this->commandes->changerStatut($idCommande, 'livree');

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            $msg = str_contains($e->getMessage(), 'Stock insuffisant') ? $e->getMessage() : 'Erreur : '.$e->getMessage();
            return ['ok'=>false,'errors'=>['global'=>$msg]];
        }

        $this->journaliser('INSERT', 'bon_livraison', $idLivraison, null, $d);
        return ['ok'=>true,'id'=>$idLivraison];
    }

    public function supprimerLivraison(int $id): array
    {
        $ancien = $this->livraisons->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'message'=>'Livraison introuvable.'];
        $this->archivageXml('bon_livraison', $id, $ancien);
        $this->livraisons->supprimer($id);
        $this->journaliser('DELETE', 'bon_livraison', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ FACTURES CLIENTS ════════════════════════════════════════════════════

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
        return Database::getInstance()->fetchAll(
            "SELECT cc.oid_doc, cc.numero, cc.montant_total, c.nom AS client
             FROM commande_client cc
             JOIN client c ON c.id_client = cc.id_client
             WHERE cc.statut='livree' AND cc.deleted_at IS NULL AND cc.est_comptant = FALSE
               AND NOT EXISTS (SELECT 1 FROM facture_client fc WHERE fc.id_commande_c=cc.oid_doc AND fc.deleted_at IS NULL)
             ORDER BY cc.date_document DESC");
    }

    public function creerFacture(array $d, int $idUtilisateur): array
    {
        if (empty($d['id_commande_c'])) return ['ok'=>false,'errors'=>['id_commande_c'=>'Commande obligatoire.']];
        $cmd = $this->commandes->trouverParId((int)$d['id_commande_c']);
        if (!$cmd) return ['ok'=>false,'errors'=>['id_commande_c'=>'Commande introuvable.']];
        if ($cmd['statut'] !== 'livree') return ['ok'=>false,'errors'=>['global'=>'La commande doit être entièrement livrée.']];
        if ($this->factures->existeePourCommande((int)$d['id_commande_c']))
            return ['ok'=>false,'errors'=>['global'=>'Une facture existe déjà pour cette commande.']];

        $montantHt = (float)($d['montant_ht'] ?? 0);
        $tauxTva   = (float)($d['taux_tva'] ?? 0);
        if ($montantHt <= 0) return ['ok'=>false,'errors'=>['montant_ht'=>'Montant HT invalide.']];

        $id = $this->factures->creer((int)$d['id_commande_c'], $idUtilisateur, $montantHt, $tauxTva);
        $this->journaliser('INSERT', 'facture_client', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function supprimerFacture(int $id): array
    {
        $ancien = $this->factures->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'message'=>'Facture introuvable.'];
        if ((float)$ancien['reste_a_payer'] < (float)$ancien['montant_ttc'])
            return ['ok'=>false,'message'=>'Impossible : des règlements existent déjà sur cette facture.'];
        $this->archivageXml('facture_client', $id, $ancien);
        $this->factures->supprimer($id);
        $this->journaliser('DELETE', 'facture_client', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ RÈGLEMENTS CLIENTS ══════════════════════════════════════════════════

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
        $this->journaliser('INSERT', 'reglement_client', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function supprimerReglement(int $idReglement, int $idFacture): array
    {
        $r = $this->reglements->trouverParId($idReglement);
        if (!$r) return ['ok'=>false,'message'=>'Règlement introuvable.'];
        $this->reglements->supprimer($idReglement);
        $this->factures->mettreAJourStatutPaiement($idFacture);
        $this->journaliser('DELETE', 'reglement_client', $idReglement, $r, null);
        return ['ok'=>true];
    }

    // ═══ BONS DE SORTIE ══════════════════════════════════════════════════════

    public function listerSorties(int $page, array $filtres): array
    {
        $total = $this->sorties->compter($filtres);
        return ['lignes' => $this->sorties->tous($page, 20, $filtres), 'total' => $total,
                'page' => $page, 'perPage' => 20, 'totalPages' => max(1,(int)ceil($total/20)),
                'filtres' => $filtres];
    }

    public function detailSortie(int $id): array|false
    {
        $s = $this->sorties->trouverParId($id);
        if (!$s) return false;
        $s['lignes'] = $this->sorties->lignes($id);
        return $s;
    }

    public function creerSortie(array $d, int $idUtilisateur): array
    {
        if (empty($d['motif'])) return ['ok'=>false,'errors'=>['motif'=>'Motif obligatoire.']];
        $lignes = $this->extraireLignesSortie($d);
        if (empty($lignes)) return ['ok'=>false,'errors'=>['lignes'=>'Au moins une ligne est requise.']];

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $id = $this->sorties->creer($d['motif'], $idUtilisateur, trim($d['observations'] ?? ''));
            foreach ($lignes as $l) {
                // Trigger AFTER INSERT décrémente le stock, lève une exception si insuffisant
                $this->sorties->ajouterLigne($id, $l['id_produit'], $l['quantite'], $l['valeur_unitaire'], $l['detail']);
            }
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollback();
            $msg = str_contains($e->getMessage(), 'Stock insuffisant') ? $e->getMessage() : 'Erreur : '.$e->getMessage();
            return ['ok'=>false,'errors'=>['global'=>$msg]];
        }

        $this->journaliser('INSERT', 'bon_sortie', $id, null, $d);
        return ['ok'=>true,'id'=>$id];
    }

    public function supprimerSortie(int $id): array
    {
        $ancien = $this->sorties->trouverParId($id);
        if (!$ancien) return ['ok'=>false,'message'=>'Bon de sortie introuvable.'];
        $this->archivageXml('bon_sortie', $id, $ancien);
        $this->sorties->supprimer($id);
        $this->journaliser('DELETE', 'bon_sortie', $id, $ancien, null);
        return ['ok'=>true];
    }

    // ═══ Helpers extraction lignes ═══════════════════════════════════════════

    private function extraireLignes(array $d): array
    {
        $lignes = [];
        $ids    = $d['produit_id']    ?? [];
        $qtes   = $d['quantite']      ?? [];
        $prix   = $d['prix_unitaire'] ?? [];
        $remise = $d['remise_pct']    ?? [];
        foreach ($ids as $i => $idProduit) {
            if (empty($idProduit) || empty($qtes[$i])) continue;
            $lignes[] = ['id_produit'=>(int)$idProduit,'quantite'=>(float)$qtes[$i],
                         'prix_unitaire'=>(float)($prix[$i] ?? 0), 'remise_pct'=>(float)($remise[$i] ?? 0)];
        }
        return $lignes;
    }

    private function extraireLignesLivraison(array $d): array
    {
        $lignes = [];
        $ids  = $d['produit_id']       ?? [];
        $qtes = $d['quantite_livree']  ?? [];
        $prix = $d['prix_unitaire']    ?? [];
        foreach ($ids as $i => $idProduit) {
            if (empty($idProduit) || empty($qtes[$i]) || (float)$qtes[$i] <= 0) continue;
            $lignes[] = ['id_produit'=>(int)$idProduit,'quantite'=>(float)$qtes[$i],'prix_unitaire'=>(float)($prix[$i] ?? 0)];
        }
        return $lignes;
    }

    private function extraireLignesSortie(array $d): array
    {
        $lignes = [];
        $ids    = $d['produit_id']      ?? [];
        $qtes   = $d['quantite']        ?? [];
        $vals   = $d['valeur_unitaire'] ?? [];
        $detail = $d['motif_detail']    ?? [];
        foreach ($ids as $i => $idProduit) {
            if (empty($idProduit) || empty($qtes[$i])) continue;
            $lignes[] = ['id_produit'=>(int)$idProduit,'quantite'=>(float)$qtes[$i],
                         'valeur_unitaire'=>(float)($vals[$i] ?? 0), 'detail'=>trim($detail[$i] ?? '')];
        }
        return $lignes;
    }

    // ═══ Accesseurs modèles ═════════════════════════════════════════════════
    public function getCommandeModel(): CommandeClientModel { return $this->commandes; }
    public function getProduitModel(): ProduitModel         { return $this->produits; }
    public function getClientModel():  ClientModel          { return $this->clients; }
}
