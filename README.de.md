# Environment Middleware Addon für REDAXO

Dieses Addon bietet eine Möglichkeit, Umgebungsvariablen zu verwalten und sie über JavaScript im Frontend zugänglich zu machen.

## Funktionen

- Konfiguration von Umgebungsvariablen für verschiedene Umgebungen
- Zugriff auf Umgebungsvariablen in JavaScript über `window.ENV` (Name konfigurierbar)
- Integration mit OAuth-Diensten zur automatischen Einbindung von Zugriffstoken

## Installation

1. Installieren Sie das Addon über den REDAXO-Installer oder laden Sie es manuell hoch
2. Aktivieren Sie das Addon im REDAXO-Backend

## Konfiguration

### Einstellungen

- **JavaScript-Variablenname**: Der Name der globalen JavaScript-Variable (Standard: `ENV`)
- **Debug-Modus**: Aktivieren Sie detaillierte Protokollierung zur Fehlerbehebung
- **Aktive Umgebung**: Wählen Sie aus, welche Umgebung aktiv sein und im Frontend verfügbar sein soll

### Umgebungen

Erstellen Sie Sätze von Umgebungsvariablen, zwischen denen gewechselt werden kann. Jede Umgebung ist eine Sammlung von Schlüssel-Wert-Paaren, die in Ihrem Frontend-JavaScript zugänglich sein werden.

Um OAuth-Token in Ihren Umgebungsvariablen zu verwenden, erstellen Sie eine Variable, die mit `OAUTH_` beginnt, gefolgt von der Eintrags-ID aus der OAuth-Konfiguration. Zum Beispiel:

```
API_URL: https://api.example.com
OAUTH_my_api: true  # Dies wird durch die Token-Daten des OAuth-Eintrags mit der ID "my_api" ersetzt
```

### OAuth-Konfiguration

Konfigurieren Sie OAuth-Endpunkte und Anmeldeinformationen, um automatisch Token für Ihre Frontend-Anwendungen abzurufen:

1. Erstellen Sie einen OAuth-Eintrag mit einer eindeutigen Eintrags-ID
2. Konfigurieren Sie die OAuth-URL und den Grant-Typ
3. Für den Grant-Typ "Client Credentials" geben Sie Client-ID und Secret an
4. Verwenden Sie die Eintrags-ID in Ihren Umgebungsvariablen mit dem Präfix `OAUTH_`

## Verwendung im Frontend

Nach der Konfiguration sind Ihre Umgebungsvariablen im Frontend als JavaScript-Objekt verfügbar:

```javascript
// Bei Verwendung des Standard-Variablennamens "ENV"
console.log(window.ENV);

// Zugriff auf eine bestimmte Variable
const apiUrl = window.ENV.API_URL;

// Zugriff auf ein OAuth-Token
const token = window.ENV.OAUTH_my_api.access_token;
```

## OAuth-Token-Struktur

Bei Verwendung der OAuth-Integration enthält der Token-Datensatz typischerweise:

```javascript
{
  "access_token": "ey...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "scope": "read write"
}
```

Sie können direkt auf diese Felder zugreifen:

```javascript
// Zugriff auf das Access-Token
const accessToken = window.ENV.OAUTH_my_api.access_token;

// Verwendung in fetch-Anfragen
fetch('https://api.example.com/data', {
  headers: {
    'Authorization': `Bearer ${accessToken}`
  }
});
```

## Fehlerbehebung

Wenn Sie Probleme mit dem Addon haben:

1. Aktivieren Sie den Debug-Modus in den Einstellungen
2. Überprüfen Sie das REDAXO-Systemprotokoll für detaillierte Informationen
3. Überprüfen Sie, ob Ihre OAuth-Anmeldeinformationen korrekt sind
4. Überprüfen Sie die Browser-Konsole auf JavaScript-Fehler

## Support

Für Support erstellen Sie bitte ein Issue im [GitHub-Repository](https://github.com/oldjazjef/redaxo-addon-env-middleware).
