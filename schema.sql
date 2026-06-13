BEGIN;

CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TYPE t_adresse AS (
    rue TEXT,
    ville VARCHAR(100),
    code_postal VARCHAR(20),
    pays VARCHAR(80)
);

CREATE TYPE t_contact AS (
    telephone VARCHAR(30),
    email VARCHAR(100),
    adresse t_adresse
);

CREATE TYPE t_ligne_article AS (
    id_produit INTEGER,
    designation VARCHAR(200),
    quantite NUMERIC(12,4),
    prix_unitaire NUMERIC(15,2),
    remise_pct NUMERIC(5,2),
    montant_ligne NUMERIC(15,2)
);

CREATE TYPE t_module AS ENUM ('approvisionnement','vente','structure','securite');
CREATE TYPE t_action_droit AS ENUM ('creer','modifier','supprimer','imprimer','consulter','regler');
CREATE TYPE t_statut_commande_f AS ENUM ('en_attente','validee','recue','annulee');
CREATE TYPE t_statut_commande_c AS ENUM ('en_attente','validee','livree','annulee');
CREATE TYPE t_statut_paiement AS ENUM ('impayee','partielle','soldee');
CREATE TYPE t_mode_paiement AS ENUM ('especes','cheque','virement','mobile_money');
CREATE TYPE t_motif_sortie AS ENUM ('perime','casse','perte','offert','autre');
CREATE TYPE t_action_audit AS ENUM ('INSERT','UPDATE','DELETE','CONNEXION','DECONNEXION','IMPRESSION');

CREATE TABLE entite (
    oid_entite SERIAL PRIMARY KEY,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP
);

CREATE TABLE famille_produit (
    id_famille INTEGER NOT NULL UNIQUE DEFAULT nextval('entite_oid_entite_seq'::regclass),
    libelle VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) INHERITS (entite);
ALTER TABLE famille_produit ADD PRIMARY KEY (id_famille);

CREATE TABLE produit (
    id_produit INTEGER NOT NULL UNIQUE DEFAULT nextval('entite_oid_entite_seq'::regclass),
    id_famille INTEGER NOT NULL REFERENCES famille_produit(id_famille) ON DELETE CASCADE,
    id_produit_pere INTEGER REFERENCES produit(id_produit) ON DELETE CASCADE,
    code VARCHAR(50) NOT NULL UNIQUE,
    designation VARCHAR(200) NOT NULL,
    unite VARCHAR(30) NOT NULL DEFAULT 'unité',
    prix_achat NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (prix_achat >= 0),
    prix_vente NUMERIC(15,2) NOT NULL DEFAULT 0 CHECK (prix_vente >= 0),
    stock_actuel NUMERIC(12,4) NOT NULL DEFAULT 0,
    stock_alerte NUMERIC(12,4) NOT NULL DEFAULT 0 CHECK (stock_alerte >= 0),
    is_fractionnaire BOOLEAN NOT NULL DEFAULT FALSE,
    facteur_fraction NUMERIC(10,4) DEFAULT 1
) INHERITS (entite);
ALTER TABLE produit ADD PRIMARY KEY (id_produit);

CREATE TABLE produit_fractionnable (
    CHECK (is_fractionnaire = TRUE)
) INHERITS (produit);

CREATE TABLE fournisseur (
    id_fournisseur INTEGER NOT NULL UNIQUE DEFAULT nextval('entite_oid_entite_seq'::regclass),
    nom VARCHAR(150) NOT NULL,
    contact t_contact
) INHERITS (entite);
ALTER TABLE fournisseur ADD PRIMARY KEY (id_fournisseur);

CREATE TABLE categorie_client (
    id_categorie SERIAL PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL UNIQUE,
    remise_pct NUMERIC(5,2) NOT NULL DEFAULT 0 CHECK (remise_pct BETWEEN 0 AND 100)
);

CREATE TABLE client (
    id_client INTEGER NOT NULL UNIQUE DEFAULT nextval('entite_oid_entite_seq'::regclass),
    id_categorie INTEGER NOT NULL REFERENCES categorie_client(id_categorie) ON DELETE RESTRICT,
    nom VARCHAR(150) NOT NULL,
    contact t_contact
) INHERITS (entite);
ALTER TABLE client ADD PRIMARY KEY (id_client);

CREATE TABLE banque (
    id_banque SERIAL PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    numero_compte VARCHAR(50),
    adresse t_adresse,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE versement_banque (
    id_versement SERIAL PRIMARY KEY,
    id_banque INTEGER NOT NULL REFERENCES banque(id_banque) ON DELETE RESTRICT,
    montant NUMERIC(15,2) NOT NULL CHECK (montant > 0),
    date_versement DATE NOT NULL DEFAULT CURRENT_DATE,
    reference TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE groupe_utilisateur (
    id_groupe INTEGER NOT NULL UNIQUE DEFAULT nextval('entite_oid_entite_seq'::regclass),
    libelle VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) INHERITS (entite);
ALTER TABLE groupe_utilisateur ADD PRIMARY KEY (id_groupe);

CREATE TABLE droit (
    id_droit SERIAL PRIMARY KEY,
    id_groupe INTEGER NOT NULL REFERENCES groupe_utilisateur(id_groupe) ON DELETE CASCADE,
    module t_module NOT NULL,
    action t_action_droit NOT NULL,
    autorise BOOLEAN NOT NULL DEFAULT FALSE,
    UNIQUE (id_groupe, module, action)
);

CREATE TABLE utilisateur (
    id_utilisateur INTEGER NOT NULL UNIQUE DEFAULT nextval('entite_oid_entite_seq'::regclass),
    id_groupe INTEGER NOT NULL REFERENCES groupe_utilisateur(id_groupe) ON DELETE RESTRICT,
    nom VARCHAR(80) NOT NULL,
    prenom VARCHAR(80),
    login VARCHAR(50) NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    actif BOOLEAN NOT NULL DEFAULT TRUE
) INHERITS (entite);
ALTER TABLE utilisateur ADD PRIMARY KEY (id_utilisateur);

CREATE OR REPLACE FUNCTION trg_hash_password()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.password_hash NOT LIKE '$2a$%' THEN
        NEW.password_hash := crypt(NEW.password_hash, gen_salt('bf', 12));
    END IF;
    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_pwd_utilisateur
BEFORE INSERT OR UPDATE OF password_hash ON utilisateur
FOR EACH ROW EXECUTE FUNCTION trg_hash_password();

CREATE TABLE document (
    oid_doc SERIAL PRIMARY KEY,
    numero VARCHAR(50) NOT NULL UNIQUE,
    date_document DATE NOT NULL DEFAULT CURRENT_DATE,
    id_utilisateur INTEGER NOT NULL REFERENCES utilisateur(id_utilisateur) ON DELETE RESTRICT,
    observations TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMP NOT NULL DEFAULT NOW(),
    deleted_at TIMESTAMP
);

CREATE TABLE commande_fournisseur (
    id_fournisseur INTEGER NOT NULL REFERENCES fournisseur(id_fournisseur) ON DELETE CASCADE,
    statut t_statut_commande_f NOT NULL DEFAULT 'en_attente',
    montant_total NUMERIC(15,2) NOT NULL DEFAULT 0,
    lignes t_ligne_article[] NOT NULL DEFAULT '{}'
) INHERITS (document);
ALTER TABLE commande_fournisseur ADD PRIMARY KEY (oid_doc);

CREATE TABLE ligne_commande_f (
    id_ligne_f SERIAL PRIMARY KEY,
    id_commande_f INTEGER NOT NULL REFERENCES commande_fournisseur(oid_doc) ON DELETE CASCADE,
    id_produit INTEGER NOT NULL REFERENCES produit(id_produit) ON DELETE CASCADE,
    quantite NUMERIC(12,4) NOT NULL CHECK (quantite > 0),
    prix_unitaire NUMERIC(15,2) NOT NULL CHECK (prix_unitaire >= 0),
    montant_ligne NUMERIC(15,2) GENERATED ALWAYS AS (quantite * prix_unitaire) STORED
);

CREATE TABLE bon_reception (
    id_commande_f INTEGER NOT NULL REFERENCES commande_fournisseur(oid_doc) ON DELETE CASCADE
) INHERITS (document);
ALTER TABLE bon_reception ADD PRIMARY KEY (oid_doc);

CREATE TABLE ligne_reception (
    id_ligne_r SERIAL PRIMARY KEY,
    id_reception INTEGER NOT NULL REFERENCES bon_reception(oid_doc) ON DELETE CASCADE,
    id_produit INTEGER NOT NULL REFERENCES produit(id_produit) ON DELETE CASCADE,
    quantite_recue NUMERIC(12,4) NOT NULL CHECK (quantite_recue > 0),
    prix_unitaire NUMERIC(15,2) NOT NULL CHECK (prix_unitaire >= 0)
);

CREATE TABLE facture_fournisseur (
    id_commande_f INTEGER NOT NULL REFERENCES commande_fournisseur(oid_doc) ON DELETE CASCADE,
    montant_ht NUMERIC(15,2) NOT NULL CHECK (montant_ht >= 0),
    taux_tva NUMERIC(5,2) NOT NULL DEFAULT 0,
    montant_tva NUMERIC(15,2) GENERATED ALWAYS AS (montant_ht * taux_tva / 100) STORED,
    montant_ttc NUMERIC(15,2) NOT NULL,
    statut_paiement t_statut_paiement NOT NULL DEFAULT 'impayee'
) INHERITS (document);
ALTER TABLE facture_fournisseur ADD PRIMARY KEY (oid_doc);

CREATE TABLE reglement_fournisseur (
    id_reglement_f SERIAL PRIMARY KEY,
    id_facture_f INTEGER NOT NULL REFERENCES facture_fournisseur(oid_doc) ON DELETE CASCADE,
    id_banque INTEGER REFERENCES banque(id_banque) ON DELETE RESTRICT,
    date_reglement DATE NOT NULL DEFAULT CURRENT_DATE,
    montant NUMERIC(15,2) NOT NULL CHECK (montant > 0),
    mode_paiement t_mode_paiement NOT NULL DEFAULT 'especes',
    reference TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE don (
    id_fournisseur INTEGER REFERENCES fournisseur(id_fournisseur) ON DELETE SET NULL
) INHERITS (document);
ALTER TABLE don ADD PRIMARY KEY (oid_doc);

CREATE TABLE ligne_don (
    id_ligne_d SERIAL PRIMARY KEY,
    id_don INTEGER NOT NULL REFERENCES don(oid_doc) ON DELETE CASCADE,
    id_produit INTEGER NOT NULL REFERENCES produit(id_produit) ON DELETE CASCADE,
    quantite NUMERIC(12,4) NOT NULL CHECK (quantite > 0),
    valeur_unitaire NUMERIC(15,2) NOT NULL DEFAULT 0
);

CREATE TABLE commande_client (
    id_client INTEGER NOT NULL REFERENCES client(id_client) ON DELETE CASCADE,
    statut t_statut_commande_c NOT NULL DEFAULT 'en_attente',
    montant_total NUMERIC(15,2) NOT NULL DEFAULT 0,
    est_comptant BOOLEAN NOT NULL DEFAULT FALSE,
    lignes t_ligne_article[] NOT NULL DEFAULT '{}'
) INHERITS (document);
ALTER TABLE commande_client ADD PRIMARY KEY (oid_doc);

CREATE TABLE ligne_commande_c (
    id_ligne_c SERIAL PRIMARY KEY,
    id_commande_c INTEGER NOT NULL REFERENCES commande_client(oid_doc) ON DELETE CASCADE,
    id_produit INTEGER NOT NULL REFERENCES produit(id_produit) ON DELETE CASCADE,
    quantite NUMERIC(12,4) NOT NULL CHECK (quantite > 0),
    prix_unitaire NUMERIC(15,2) NOT NULL CHECK (prix_unitaire >= 0),
    remise_pct NUMERIC(5,2) NOT NULL DEFAULT 0 CHECK (remise_pct BETWEEN 0 AND 100),
    montant_ligne NUMERIC(15,2) GENERATED ALWAYS AS (quantite * prix_unitaire * (1 - remise_pct / 100)) STORED
);

CREATE TABLE vente_comptant (
    CHECK (est_comptant = TRUE)
) INHERITS (commande_client);
ALTER TABLE vente_comptant ADD PRIMARY KEY (oid_doc);

CREATE TABLE bon_livraison (
    id_commande_c INTEGER NOT NULL REFERENCES commande_client(oid_doc) ON DELETE CASCADE
) INHERITS (document);
ALTER TABLE bon_livraison ADD PRIMARY KEY (oid_doc);

CREATE TABLE ligne_livraison (
    id_ligne_l SERIAL PRIMARY KEY,
    id_livraison INTEGER NOT NULL REFERENCES bon_livraison(oid_doc) ON DELETE CASCADE,
    id_produit INTEGER NOT NULL REFERENCES produit(id_produit) ON DELETE CASCADE,
    quantite_livree NUMERIC(12,4) NOT NULL CHECK (quantite_livree > 0),
    prix_unitaire NUMERIC(15,2) NOT NULL CHECK (prix_unitaire >= 0)
);

CREATE TABLE facture_client (
    id_commande_c INTEGER NOT NULL REFERENCES commande_client(oid_doc) ON DELETE CASCADE,
    montant_ht NUMERIC(15,2) NOT NULL CHECK (montant_ht >= 0),
    taux_tva NUMERIC(5,2) NOT NULL DEFAULT 0,
    montant_tva NUMERIC(15,2) GENERATED ALWAYS AS (montant_ht * taux_tva / 100) STORED,
    montant_ttc NUMERIC(15,2) NOT NULL,
    statut_paiement t_statut_paiement NOT NULL DEFAULT 'impayee'
) INHERITS (document);
ALTER TABLE facture_client ADD PRIMARY KEY (oid_doc);

CREATE TABLE reglement_client (
    id_reglement_c SERIAL PRIMARY KEY,
    id_facture_c INTEGER NOT NULL REFERENCES facture_client(oid_doc) ON DELETE CASCADE,
    id_banque INTEGER REFERENCES banque(id_banque) ON DELETE RESTRICT,
    date_reglement DATE NOT NULL DEFAULT CURRENT_DATE,
    montant NUMERIC(15,2) NOT NULL CHECK (montant > 0),
    mode_paiement t_mode_paiement NOT NULL DEFAULT 'especes',
    reference TEXT,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE bon_sortie (
    motif t_motif_sortie NOT NULL
) INHERITS (document);
ALTER TABLE bon_sortie ADD PRIMARY KEY (oid_doc);

CREATE TABLE ligne_sortie (
    id_ligne_s SERIAL PRIMARY KEY,
    id_sortie INTEGER NOT NULL REFERENCES bon_sortie(oid_doc) ON DELETE CASCADE,
    id_produit INTEGER NOT NULL REFERENCES produit(id_produit) ON DELETE CASCADE,
    quantite NUMERIC(12,4) NOT NULL CHECK (quantite > 0),
    valeur_unitaire NUMERIC(15,2) NOT NULL DEFAULT 0,
    motif_detail TEXT
);

CREATE TABLE journal_audit (
    id_journal SERIAL PRIMARY KEY,
    id_utilisateur INTEGER REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    table_cible VARCHAR(80) NOT NULL,
    action t_action_audit NOT NULL,
    id_enregistrement INTEGER,
    anciennes_valeurs JSONB,
    nouvelles_valeurs JSONB,
    ip_adresse INET,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE archive_xml (
    id_archive SERIAL PRIMARY KEY,
    entite VARCHAR(80) NOT NULL,
    id_entite INTEGER NOT NULL,
    xml_data TEXT NOT NULL,
    action VARCHAR(20) NOT NULL CHECK (action IN ('suppression','restauration')),
    id_utilisateur INTEGER REFERENCES utilisateur(id_utilisateur) ON DELETE SET NULL,
    created_at TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_archive_entite ON archive_xml(entite, id_entite);
CREATE INDEX idx_produit_famille ON produit(id_famille) WHERE deleted_at IS NULL;
CREATE INDEX idx_produit_pere ON produit(id_produit_pere);
CREATE INDEX idx_client_categorie ON client(id_categorie) WHERE deleted_at IS NULL;
CREATE INDEX idx_doc_date ON document(date_document) WHERE deleted_at IS NULL;
CREATE INDEX idx_doc_utilisateur ON document(id_utilisateur);
CREATE INDEX idx_utilisateur_login ON utilisateur(login) WHERE deleted_at IS NULL;
CREATE INDEX idx_journal_date ON journal_audit(created_at);
CREATE INDEX idx_journal_table ON journal_audit(table_cible, action);

CREATE OR REPLACE FUNCTION produit_valeur_stock(p produit)
RETURNS NUMERIC AS $$ SELECT p.stock_actuel * p.prix_achat; $$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION produit_est_en_alerte(p produit)
RETURNS BOOLEAN AS $$ SELECT p.stock_actuel <= p.stock_alerte; $$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION produit_incrementer_stock(p_id INTEGER, p_qte NUMERIC)
RETURNS VOID AS $$
BEGIN
    UPDATE produit
    SET stock_actuel = stock_actuel + p_qte,
        updated_at = NOW()
    WHERE id_produit = p_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION produit_decrementer_stock(p_id INTEGER, p_qte NUMERIC)
RETURNS VOID AS $$
DECLARE v_stock NUMERIC;
BEGIN
    SELECT stock_actuel INTO v_stock FROM produit WHERE id_produit = p_id;
    IF v_stock < p_qte THEN
        RAISE EXCEPTION 'Stock insuffisant pour le produit % : % disponible, % demandé.', p_id, v_stock, p_qte;
    END IF;
    UPDATE produit
    SET stock_actuel = stock_actuel - p_qte,
        updated_at = NOW()
    WHERE id_produit = p_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION client_remise(c client)
RETURNS NUMERIC AS $$
    SELECT cc.remise_pct
    FROM categorie_client cc
    WHERE cc.id_categorie = c.id_categorie;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION groupe_a_droit(p_groupe INTEGER, p_module t_module, p_action t_action_droit)
RETURNS BOOLEAN AS $$
    SELECT COALESCE(
        (SELECT autorise FROM droit WHERE id_groupe = p_groupe AND module = p_module AND action = p_action),
        FALSE
    );
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION utilisateur_verifier_mdp(p_login TEXT, p_mdp TEXT)
RETURNS BOOLEAN AS $$
    SELECT EXISTS (
        SELECT 1 FROM utilisateur
        WHERE login = p_login
          AND actif = TRUE
          AND deleted_at IS NULL
          AND password_hash = crypt(p_mdp, password_hash)
    );
$$ LANGUAGE sql STABLE SECURITY DEFINER;

CREATE OR REPLACE FUNCTION utilisateur_a_droit(p_user INTEGER, p_module t_module, p_action t_action_droit)
RETURNS BOOLEAN AS $$
    SELECT groupe_a_droit((SELECT id_groupe FROM utilisateur WHERE id_utilisateur = p_user), p_module, p_action);
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION facture_f_reste(p_id INTEGER)
RETURNS NUMERIC AS $$
    SELECT ff.montant_ttc - COALESCE(SUM(r.montant), 0)
    FROM facture_fournisseur ff
    LEFT JOIN reglement_fournisseur r ON r.id_facture_f = ff.oid_doc
    WHERE ff.oid_doc = p_id
    GROUP BY ff.montant_ttc;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION facture_c_reste(p_id INTEGER)
RETURNS NUMERIC AS $$
    SELECT fc.montant_ttc - COALESCE(SUM(r.montant), 0)
    FROM facture_client fc
    LEFT JOIN reglement_client r ON r.id_facture_c = fc.oid_doc
    WHERE fc.oid_doc = p_id
    GROUP BY fc.montant_ttc;
$$ LANGUAGE sql STABLE;

CREATE OR REPLACE FUNCTION trg_stock_reception()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    PERFORM produit_incrementer_stock(NEW.id_produit, NEW.quantite_recue);
    RETURN NEW;
END;
$$;
CREATE TRIGGER trg_reception_stock AFTER INSERT ON ligne_reception FOR EACH ROW EXECUTE FUNCTION trg_stock_reception();

CREATE OR REPLACE FUNCTION trg_stock_don()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    PERFORM produit_incrementer_stock(NEW.id_produit, NEW.quantite);
    RETURN NEW;
END;
$$;
CREATE TRIGGER trg_don_stock AFTER INSERT ON ligne_don FOR EACH ROW EXECUTE FUNCTION trg_stock_don();

CREATE OR REPLACE FUNCTION trg_stock_livraison()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    PERFORM produit_decrementer_stock(NEW.id_produit, NEW.quantite_livree);
    RETURN NEW;
END;
$$;
CREATE TRIGGER trg_livraison_stock BEFORE INSERT ON ligne_livraison FOR EACH ROW EXECUTE FUNCTION trg_stock_livraison();

CREATE OR REPLACE FUNCTION trg_stock_sortie()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    PERFORM produit_decrementer_stock(NEW.id_produit, NEW.quantite);
    RETURN NEW;
END;
$$;
CREATE TRIGGER trg_sortie_stock AFTER INSERT ON ligne_sortie FOR EACH ROW EXECUTE FUNCTION trg_stock_sortie();

CREATE OR REPLACE FUNCTION trg_total_cf()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    UPDATE commande_fournisseur
    SET montant_total = COALESCE((SELECT SUM(montant_ligne) FROM ligne_commande_f WHERE id_commande_f = COALESCE(NEW.id_commande_f, OLD.id_commande_f)), 0),
        updated_at = NOW()
    WHERE oid_doc = COALESCE(NEW.id_commande_f, OLD.id_commande_f);
    RETURN NULL;
END;
$$;
CREATE TRIGGER trg_total_commande_f AFTER INSERT OR UPDATE OR DELETE ON ligne_commande_f FOR EACH ROW EXECUTE FUNCTION trg_total_cf();

CREATE OR REPLACE FUNCTION trg_total_cc()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
BEGIN
    UPDATE commande_client
    SET montant_total = COALESCE((SELECT SUM(montant_ligne) FROM ligne_commande_c WHERE id_commande_c = COALESCE(NEW.id_commande_c, OLD.id_commande_c)), 0),
        updated_at = NOW()
    WHERE oid_doc = COALESCE(NEW.id_commande_c, OLD.id_commande_c);
    RETURN NULL;
END;
$$;
CREATE TRIGGER trg_total_commande_c AFTER INSERT OR UPDATE OR DELETE ON ligne_commande_c FOR EACH ROW EXECUTE FUNCTION trg_total_cc();

CREATE OR REPLACE FUNCTION trg_numero_document()
RETURNS TRIGGER LANGUAGE plpgsql AS $$
DECLARE v_prefixe TEXT; v_seq TEXT;
BEGIN
    IF NEW.numero IS NULL THEN
        CASE TG_TABLE_NAME
            WHEN 'commande_fournisseur' THEN v_prefixe := 'BCF'; v_seq := 'seq_bc_fournisseur';
            WHEN 'commande_client' THEN v_prefixe := 'BCC'; v_seq := 'seq_bc_client';
            WHEN 'vente_comptant' THEN v_prefixe := 'VCC'; v_seq := 'seq_bc_client';
            WHEN 'bon_reception' THEN v_prefixe := 'BR'; v_seq := 'seq_br';
            WHEN 'bon_livraison' THEN v_prefixe := 'BL'; v_seq := 'seq_bl';
            WHEN 'facture_fournisseur' THEN v_prefixe := 'FF'; v_seq := 'seq_facture_f';
            WHEN 'facture_client' THEN v_prefixe := 'FC'; v_seq := 'seq_facture_c';
            WHEN 'bon_sortie' THEN v_prefixe := 'BS'; v_seq := 'seq_bon_sortie';
            WHEN 'don' THEN v_prefixe := 'DON'; v_seq := 'seq_bon_don';
            ELSE v_prefixe := 'DOC'; v_seq := 'seq_bc_client';
        END CASE;
        NEW.numero := v_prefixe || '-' || TO_CHAR(NOW(),'YYYY') || '-' || LPAD(nextval(v_seq::regclass)::TEXT, 5, '0');
    END IF;
    RETURN NEW;
END;
$$;

CREATE SEQUENCE seq_bc_fournisseur START 1;
CREATE SEQUENCE seq_bc_client START 1;
CREATE SEQUENCE seq_br START 1;
CREATE SEQUENCE seq_bl START 1;
CREATE SEQUENCE seq_facture_f START 1;
CREATE SEQUENCE seq_facture_c START 1;
CREATE SEQUENCE seq_bon_sortie START 1;
CREATE SEQUENCE seq_bon_don START 1;

CREATE TRIGGER trg_num_cf BEFORE INSERT ON commande_fournisseur FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_cc BEFORE INSERT ON commande_client FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_vc BEFORE INSERT ON vente_comptant FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_br BEFORE INSERT ON bon_reception FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_bl BEFORE INSERT ON bon_livraison FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_ff BEFORE INSERT ON facture_fournisseur FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_fc BEFORE INSERT ON facture_client FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_bs BEFORE INSERT ON bon_sortie FOR EACH ROW EXECUTE FUNCTION trg_numero_document();
CREATE TRIGGER trg_num_don BEFORE INSERT ON don FOR EACH ROW EXECUTE FUNCTION trg_numero_document();

CREATE VIEW v_stock_produit AS
SELECT p.id_produit, p.code, p.designation, f.libelle AS famille, p.unite, p.stock_actuel, p.stock_alerte, p.prix_achat, p.prix_vente,
       produit_valeur_stock(p) AS valeur_stock, produit_est_en_alerte(p) AS en_alerte,
       CASE WHEN p.id_produit_pere IS NOT NULL THEN 'fils' ELSE 'père' END AS type_produit
FROM produit p
JOIN famille_produit f ON f.id_famille = p.id_famille
WHERE p.deleted_at IS NULL;

CREATE VIEW v_ventes_par_jour AS
SELECT cc.date_document AS jour, COUNT(DISTINCT cc.oid_doc) AS nb_commandes, SUM(l.quantite) AS total_quantite, SUM(l.montant_ligne) AS total_montant_ht
FROM commande_client cc
JOIN ligne_commande_c l ON l.id_commande_c = cc.oid_doc
WHERE cc.deleted_at IS NULL AND cc.statut <> 'annulee'
GROUP BY cc.date_document;

CREATE VIEW v_achats_par_jour AS
SELECT cf.date_document AS jour, COUNT(DISTINCT cf.oid_doc) AS nb_commandes, SUM(l.quantite) AS total_quantite, SUM(l.montant_ligne) AS total_montant_ht
FROM commande_fournisseur cf
JOIN ligne_commande_f l ON l.id_commande_f = cf.oid_doc
WHERE cf.deleted_at IS NULL AND cf.statut <> 'annulee'
GROUP BY cf.date_document;

CREATE VIEW v_factures_f_impayees AS
SELECT ff.numero AS numero_facture, fo.nom AS fournisseur, ff.date_document AS date_facture, ff.montant_ttc,
       COALESCE(SUM(r.montant), 0) AS montant_regle, ff.montant_ttc - COALESCE(SUM(r.montant),0) AS reste_a_payer
FROM facture_fournisseur ff
JOIN commande_fournisseur cf ON cf.oid_doc = ff.id_commande_f
JOIN fournisseur fo ON fo.id_fournisseur = cf.id_fournisseur
LEFT JOIN reglement_fournisseur r ON r.id_facture_f = ff.oid_doc
WHERE ff.statut_paiement <> 'soldee'
GROUP BY ff.oid_doc, ff.numero, fo.nom, ff.date_document, ff.montant_ttc;

CREATE VIEW v_factures_c_impayees AS
SELECT fc.oid_doc, fc.numero AS numero_facture, c.nom AS client, fc.date_document AS date_facture, fc.montant_ttc,
       COALESCE(SUM(r.montant), 0) AS montant_regle, fc.montant_ttc - COALESCE(SUM(r.montant),0) AS reste_a_payer
FROM facture_client fc
JOIN commande_client cc ON cc.oid_doc = fc.id_commande_c
JOIN client c ON c.id_client = cc.id_client
LEFT JOIN reglement_client r ON r.id_facture_c = fc.oid_doc
WHERE fc.statut_paiement <> 'soldee'
GROUP BY fc.oid_doc, fc.numero, c.nom, fc.date_document, fc.montant_ttc;

INSERT INTO categorie_client(libelle, remise_pct) VALUES
('Standard', 0.00),
('Grossiste', 5.00),
('Revendeur', 8.00),
('VIP', 10.00);

INSERT INTO groupe_utilisateur(libelle, description) VALUES
('Administrateur', 'Accès total à tous les modules'),
('Gestionnaire', 'Approvisionnements et ventes'),
('Caissier', 'Ventes au comptant uniquement'),
('Magasinier', 'Réceptions et bons de sortie'),
('Consultant', 'Lecture seule — aucune modification');

INSERT INTO droit(id_groupe, module, action, autorise)
SELECT g.id_groupe, m.module, a.action, TRUE
FROM groupe_utilisateur g
CROSS JOIN (VALUES ('approvisionnement'::t_module),('vente'::t_module),('structure'::t_module),('securite'::t_module)) AS m(module)
CROSS JOIN (VALUES ('creer'::t_action_droit),('modifier'::t_action_droit),('supprimer'::t_action_droit),('imprimer'::t_action_droit),('consulter'::t_action_droit),('regler'::t_action_droit)) AS a(action)
WHERE g.libelle = 'Administrateur';

INSERT INTO droit(id_groupe, module, action, autorise)
SELECT g.id_groupe, m.module, a.action, TRUE
FROM groupe_utilisateur g
CROSS JOIN (VALUES ('approvisionnement'::t_module),('vente'::t_module),('structure'::t_module)) AS m(module)
CROSS JOIN (VALUES ('creer'::t_action_droit),('modifier'::t_action_droit),('imprimer'::t_action_droit),('consulter'::t_action_droit),('regler'::t_action_droit)) AS a(action)
WHERE g.libelle = 'Gestionnaire';

INSERT INTO droit(id_groupe, module, action, autorise)
SELECT g.id_groupe, 'vente'::t_module, a.action, TRUE
FROM groupe_utilisateur g
CROSS JOIN (VALUES ('creer'::t_action_droit),('imprimer'::t_action_droit),('consulter'::t_action_droit),('regler'::t_action_droit)) AS a(action)
WHERE g.libelle = 'Caissier';

INSERT INTO droit(id_groupe, module, action, autorise)
SELECT g.id_groupe, v.module, v.action, TRUE
FROM groupe_utilisateur g
CROSS JOIN (VALUES
    ('approvisionnement'::t_module, 'creer'::t_action_droit),
    ('approvisionnement'::t_module, 'consulter'::t_action_droit),
    ('approvisionnement'::t_module, 'imprimer'::t_action_droit),
    ('structure'::t_module, 'consulter'::t_action_droit)
) AS v(module, action)
WHERE g.libelle = 'Magasinier';

INSERT INTO droit(id_groupe, module, action, autorise)
SELECT g.id_groupe, m.module, 'consulter'::t_action_droit, TRUE
FROM groupe_utilisateur g
CROSS JOIN (VALUES ('approvisionnement'::t_module),('vente'::t_module),('structure'::t_module),('securite'::t_module)) AS m(module)
WHERE g.libelle = 'Consultant';

INSERT INTO utilisateur(id_groupe, nom, prenom, login, password_hash, actif)
SELECT g.id_groupe, 'Administrateur', 'Stock', 'admin', 'admin123', TRUE
FROM groupe_utilisateur g
WHERE g.libelle = 'Administrateur'
  AND NOT EXISTS (SELECT 1 FROM utilisateur WHERE login = 'admin');

COMMIT;
