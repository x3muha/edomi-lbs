# EDOMI LBS

Auswahl eigener EDOMI-Logikbausteine.

## Bausteine

| ID | Name | Beschreibung |
| --- | --- | --- |
| [19100801](bausteine/19100801/README.md) | [Universal P-Regler 1.0](bausteine/19100801/README.md) | P-Regler für Soll-/Istwert, Hysterese und Stellwertkorrektur. |
| [19100804](bausteine/19100804/README.md) | [cFos HTTP Reader 1.0](bausteine/19100804/README.md) | Zyklischer Reader für cFos /cnf?cmd=get_dev_info. |
| [19100805](bausteine/19100805/README.md) | [cFos Wallbox Decoder 1.0](bausteine/19100805/README.md) | Dekodiert eine einzelne Wallbox aus dem Reader-JSON von 19100804. |
| [19100806](bausteine/19100806/README.md) | [SolarAssistant Decoder 2.1](bausteine/19100806/README.md) | Liest SolarAssistant per REST und schreibt ausgewaehlte Inverter-Einstellungen. |
| [19100818](bausteine/19100818/README.md) | [cFos HTTP Zähler 1.0](bausteine/19100818/README.md) | Sendet model + power_w per POST an cFos set_ajax_meter. |
| [19100821](bausteine/19100821/README.md) | [cFos HTTP Zähler 4fach 1.0](bausteine/19100821/README.md) | Sendet vier cFos-Zähler mit gemeinsamer Host/User/Pass-Konfiguration. |
| [19100827](bausteine/19100827/README.md) | [cFos Variablen Writer 4fach 1.0](bausteine/19100827/README.md) | Schreibt bis zu vier Charging-Manager-Variablen in cFos. |
| [19100828](bausteine/19100828/README.md) | [cFos Variablen Reader 4fach 1.0](bausteine/19100828/README.md) | Liest bis zu vier Charging-Manager-Variablen aus cFos. |
| [19100829](bausteine/19100829/README.md) | [cFos Switch Writer 4fach 1.0](bausteine/19100829/README.md) | Schreibt bis zu vier als Switch/Bool deklarierte cFos Charging-Manager-Variablen. |
| [19100830](bausteine/19100830/README.md) | [cFos HTTP Reader 4fach 1.0](bausteine/19100830/README.md) | Zyklischer 4fach-Reader für cFos /cnf?cmd=get_dev_info. |
| [19100834](bausteine/19100834/README.md) | [FriWa Wh Verteiler 1.0](bausteine/19100834/README.md) | FriWa-Zählerstand auf Zirkulation und vier Kanäle verteilen. |
| [19100836](bausteine/19100836/README.md) | [ESPHome Tesla BLE 2.0](bausteine/19100836/README.md) | ESPHome Tesla BLE Status lesen und zentrale Steuerbefehle senden. |
| [19100839](bausteine/19100839/README.md) | [ESPHome Midea X3 1.0](bausteine/19100839/README.md) | ESPHome Midea X3 Klima-, Fresh- und Clean-Status/Steuerung. |
| [19100840](bausteine/19100840/README.md) | [KNX DPT 20.105 Mapper 1.0](bausteine/19100840/README.md) | KNX DPT 20.105 HVAC Control Mode zwischen DPT-Wert und 1/0-Modusobjekten mappen. |
| [19100841](bausteine/19100841/README.md) | [Hoymiles DTUBI 1.0](bausteine/19100841/README.md) | Hoymiles HMS/HMT Wechselrichter mit integrierter WLAN-DTU lokal per TCP/Protobuf lesen. |

## Externe Projekte

- ESPHome Tesla BLE: https://github.com/yoziru/esphome-tesla-ble
- Tesla BLE C++ Library: https://github.com/yoziru/tesla-ble
- Hoymiles WiFi Python Library: https://github.com/suaveolent/hoymiles-wifi
