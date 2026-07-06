# SolarAssistant Decoder 2.1

Kompakter EDOMI-LBS fuer SolarAssistant.

## Datei

- Import: `LBS/19100806/19100806_lbs.php`
- Vorschau: `docs/images/19100806.png`

## Funktion

- Liest SolarAssistant zyklisch per REST API.
- Gibt zentrale PV-, Batterie-, Netz-, Last- und Systemwerte auf festen Ausgaengen aus.
- Schreibt ausgewaehlte Inverter-Einstellungen ueber `POST /api/v1/metrics`.
- Zeigt Schreib-Erfolg oder SolarAssistant-Fehlertext auf A2.

## Schreibwerte

- E28/A28: `inverter_1/max_grid_charge_current`
- E29/A29: `inverter_1/max_charge_current`
- E30/A30: `inverter_1/capacity_point_6`
- E31/A31: `inverter_1/force_off_grid`

Ein Refresh auf E28..E31 schreibt sofort. Wenn EDOMI beim manuellen Eintragen kein Refresh-Flag setzt, erkennt der Baustein geaenderte nicht-leere Werte nach der ersten Baseline beim naechsten Lauf.

## Hinweise

- E1 ist die SolarAssistant Basis-URL, z. B. `http://192.168.1.50`.
- E2 bleibt normalerweise `/api/v1/metrics`.
- E3/E4 sind User und Passwort der SolarAssistant Weboberflaeche.
- E5 aktiviert die zyklische Abfrage.
- E6 ist das Intervall in Sekunden.
