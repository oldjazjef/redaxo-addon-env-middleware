# Environment Middleware Addon für REDAXO

Dieses Addon bietet eine integrierte Lösung für die Verwaltung von Umgebungen, OAuth-Authentifizierung und API-Proxying in REDAXO. Es ermöglicht die sichere Bereitstellung von Umgebungsvariablen im Frontend, während sensible Authentifizierungsdetails auf dem Server verwaltet werden.

## Funktionen

- Konfiguration und Verwaltung mehrerer Umgebungen mit unterschiedlichen Einstellungen
- Zugänglichmachung von Umgebungsvariablen im Frontend über JavaScript
- Konfiguration von OAuth-Endpunkten mit Authentifizierungsinformationen
- Sichere API-Kommunikation mit Proxy-Endpunkten, die automatisch Authentifizierung einfügen
- SSL-Zertifikatsprüfungssteuerung für Entwicklungsumgebungen

## Installation

1. Installieren Sie das Addon über den REDAXO-Installer oder laden Sie es manuell hoch
2. Aktivieren Sie das Addon im REDAXO-Backend

## Konfiguration

### Umgebungen

Der Umgebungsbereich ermöglicht es Ihnen:

1. **Mehrere Umgebungen definieren**: Erstellen Sie Sätze von Umgebungsvariablen, die in verschiedenen Kontexten verwendet werden können (Entwicklung, Test, Produktion).
2. **Aktive Umgebung auswählen**: Wählen Sie aus, welche Umgebungskonfiguration aktiv und im Frontend verfügbar sein soll.
3. **JavaScript-Variablennamen konfigurieren**: Definieren Sie den Namen der globalen JavaScript-Variable, die für den Zugriff auf Umgebungsvariablen im Browser verwendet wird (Standard: ENV).

Jede Umgebung besteht aus Schlüssel-Wert-Paaren, die Ihre Konfiguration darstellen. Diese Werte werden in Ihrem Frontend-JavaScript über die konfigurierte globale Variable zugänglich.

Beispiel für eine Umgebungskonfiguration:
\\\
API_URL: https://api.example.com
FEATURE_FLAGS_ENABLED: true
OAUTH_my_api: true  # Dies verweist auf einen OAuth-Eintrag (siehe OAuth-Konfiguration)
\\\

### OAuth-Konfiguration

Der OAuth-Bereich ermöglicht es Ihnen:

1. **Authentifizierungs-Endpunkte konfigurieren**: Richten Sie OAuth-Endpunkt-URLs und erforderliche Anmeldeinformationen ein.
2. **OAuth-Eintrags-IDs definieren**: Erstellen Sie eindeutige Bezeichner für jeden OAuth-Endpunkt.
3. **Grant-Typen konfigurieren**: Unterstützung für verschiedene OAuth-Grant-Typen, einschließlich Client Credentials.
4. **Client-IDs und -Secrets verwalten**: Sichere Speicherung von Authentifizierungsdaten.

Auf OAuth-Einträge kann in Umgebungsvariablen verwiesen werden, indem das Präfix \OAUTH_\ gefolgt von der Eintrags-ID verwendet wird.

### Proxy-Konfiguration

Der Proxy-Bereich ermöglicht es Ihnen:

1. **API-Proxies erstellen**: Richten Sie Endpunkte ein, die als Vermittler zwischen Ihrem Frontend und Backend-APIs fungieren.
2. **Mit OAuth-Einträgen verknüpfen**: Verbinden Sie Proxies mit den OAuth-Konfigurationen für automatische Authentifizierung.
3. **SSL-Verifizierung steuern**: Aktivieren oder deaktivieren Sie die SSL-Zertifikatsprüfung pro Proxy für Entwicklungsumgebungen.
4. **Sicheren API-Zugriff gewährleisten**: Halten Sie sensible Authentifizierungsinformationen auf dem Server und außerhalb des Frontend-Codes.

Proxies helfen Ihnen, die Sicherheit aufrechtzuerhalten, indem sie verhindern, dass Anmeldeinformationen im Client-seitigen Code offengelegt werden. Sie fügen automatisch die richtigen Authentifizierungs-Header in API-Anfragen ein.

## Funktionsweise

### 1. Umgebungsverwaltung

- Definieren Sie mehrere Umgebungen mit unterschiedlichen Konfigurationswerten
- Setzen Sie eine Umgebung als "aktiv", um sie im Frontend verfügbar zu machen
- Greifen Sie über ein globales JavaScript-Objekt auf Umgebungsvariablen zu
- Steuern Sie, welcher Variablenname verwendet wird (z.B. \window.ENV\, \window.CONFIG\ usw.)

### 2. OAuth-Integration

- Konfigurieren Sie OAuth-Endpunkte mit Authentifizierungsdetails
- Unterstützung für verschiedene OAuth-Grant-Typen
- Automatische Token-Abfrage und -Erneuerung
- Sichere Speicherung von Client-Secrets auf dem Server

### 3. API-Proxying

- Erstellen Sie Proxy-Endpunkte, die Anfragen an externe APIs weiterleiten
- Fügen Sie automatisch Authentifizierungs-Header in Anfragen ein
- Halten Sie sensible Anmeldeinformationen sicher, indem Sie die Authentifizierung auf dem Server durchführen
- Steuern Sie SSL-Verifizierungseinstellungen für jeden Proxy individuell

## Anwendungsbeispiele

### Zugriff auf Umgebungsvariablen in JavaScript

\\\javascript
// Bei Verwendung des Standard-Variablennamens "ENV"
console.log(window.ENV);

// Zugriff auf eine bestimmte Variable
const apiUrl = window.ENV.API_URL;

// Zugriff auf ein OAuth-Token
const token = window.ENV.OAUTH_my_api.access_token;
\\\

### Authentifizierte API-Anfragen stellen

Verwendung von OAuth-Tokens direkt (wenn sie im Frontend verfügbar sind):
\\\javascript
fetch('https://api.example.com/data', {
  headers: {
    'Authorization': \Bearer \\
  }
});
\\\

Verwendung des Proxys (sicherer):
\\\javascript
// Der Proxy fügt automatisch Authentifizierungs-Header hinzu
fetch('/index.php?rex-api-call=proxy_request&proxy=my_api_proxy&endpoint=/data');
\\\

Der Proxy-Ansatz ist sicherer, weil:
- Authentifizierungsdaten auf dem Server bleiben
- Tokens nicht im Frontend-Code offengelegt werden
- Der Server abgelaufene Tokens automatisch erneuern kann

## Debugging und Fehlerbehebung

Das Addon enthält mehrere Funktionen zur Unterstützung beim Debugging:

- **Debug-Modus**: Aktivieren Sie detaillierte Protokollierung zur Fehlerbehebung
- **Testkonfigurations-Panel**: Anzeige der aktiven Umgebungskonfiguration
- **Proxy-Tests**: Testen Sie Proxy-Endpunkte direkt im REDAXO-Backend
- **SSL-Verifizierungssteuerung**: Deaktivieren Sie die SSL-Verifizierung für Entwicklungsumgebungen mit selbstsignierten Zertifikaten

Wenn Sie auf Probleme stoßen:

1. Aktivieren Sie den Debug-Modus in den Einstellungen
2. Überprüfen Sie das REDAXO-Systemprotokoll für detaillierte Informationen
3. Überprüfen Sie, ob Ihre OAuth-Anmeldeinformationen korrekt sind
4. Überprüfen Sie die Browser-Konsole auf JavaScript-Fehler

## Sicherheitsüberlegungen

- Halten Sie den Debug-Modus in Produktionsumgebungen deaktiviert
- Die SSL-Verifizierung sollte nur in Entwicklungsumgebungen deaktiviert werden
- Verwenden Sie nach Möglichkeit Proxy-Endpunkte anstatt Tokens direkt offenzulegen
- Rotieren Sie regelmäßig OAuth-Client-Secrets

## Support

Für Support erstellen Sie bitte ein Issue im [GitHub-Repository](https://github.com/oldjazjef/redaxo-addon-env-middleware).

## Lizenz

MIT-Lizenz
