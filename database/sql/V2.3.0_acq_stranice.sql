-- V 2.3.0 (HALK / ACQ evidencija): stranice i prava (stranicas, pozicija_prikaz_stranicas).
--
-- Rucno, posle deploy-a koda i php artisan migrate --force, na svakoj bazi (prod, develop/test, lokalno).
-- Idempotentno: moze da se pokrene vise puta, postojeci redovi se ne dupliraju.
-- Develop: /home/eposrs/db-sync/posle-uvoza.sql ga ukljucuje posle nocne sinhronizacije:
--   SOURCE /home/eposrs/develop-servis-epos-app/database/sql/V2.3.0_acq_stranice.sql;
--
-- acq-terminali: stranica, u meniju posle Terminala; Admin (1), Call centar (2), Sef servisa (3), Serviser (4)
--   (svi: lista, detalj, opcije, komentari, dodela uredjaja, izvrsenje prekonfiguracije, premestanje, deinstalacija)
-- acq-terminali-upravljanje: pravo bez rute i menija za Dodaj terminal, rucnu prekonfiguraciju i opoziv; 1 i 2
-- acq-uvoz-zahteva: stranica preko dugmeta na ACQ terminalima; 1 i 2
-- acq-opcije: sifarnik opcija TID-a, preko dugmeta na ACQ terminalima; samo 1
-- Tiketi se pomeraju sa menu_order 5 na 6, da ACQ terminali stoje posle Terminala.

INSERT INTO stranicas (naziv, route_name, menu_order, sub_menu_order, show_in_meni, created_at, updated_at)
SELECT 'ACQ terminali', 'acq-terminali', 5, NULL, 1, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stranicas WHERE route_name = 'acq-terminali');

INSERT INTO stranicas (naziv, route_name, menu_order, sub_menu_order, show_in_meni, created_at, updated_at)
SELECT 'ACQ terminali - upravljanje', 'acq-terminali-upravljanje', 39, NULL, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stranicas WHERE route_name = 'acq-terminali-upravljanje');

INSERT INTO stranicas (naziv, route_name, menu_order, sub_menu_order, show_in_meni, created_at, updated_at)
SELECT 'Uvoz zahteva banke', 'acq-uvoz-zahteva', 39, NULL, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stranicas WHERE route_name = 'acq-uvoz-zahteva');

INSERT INTO stranicas (naziv, route_name, menu_order, sub_menu_order, show_in_meni, created_at, updated_at)
SELECT 'Opcije ACQ terminala', 'acq-opcije', 39, NULL, 0, NOW(), NOW()
WHERE NOT EXISTS (SELECT 1 FROM stranicas WHERE route_name = 'acq-opcije');

UPDATE stranicas SET menu_order = 6, updated_at = NOW() WHERE route_name = 'tiket' AND menu_order <> 6;
UPDATE stranicas SET menu_order = 5, updated_at = NOW() WHERE route_name = 'acq-terminali' AND menu_order <> 5;

INSERT INTO pozicija_prikaz_stranicas (pozicija_tipId, stranicaId, created_at, updated_at)
SELECT p.id, s.id, NOW(), NOW()
FROM pozicija_tips p
JOIN stranicas s ON (s.route_name = 'acq-terminali' AND p.id IN (1, 2, 3, 4))
                 OR (s.route_name IN ('acq-terminali-upravljanje', 'acq-uvoz-zahteva') AND p.id IN (1, 2))
                 OR (s.route_name = 'acq-opcije' AND p.id = 1)
WHERE NOT EXISTS (SELECT 1 FROM pozicija_prikaz_stranicas x WHERE x.pozicija_tipId = p.id AND x.stranicaId = s.id);
