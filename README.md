# Environment- & Proxy-Middleware-Addon für REDAXO

Dieses Addon bietet eine integrierte Lösung zur Verwaltung von Umgebungen, OAuth-Authentifizierung und API-Proxying in REDAXO. Es ermöglicht die sichere Bereitstellung von Umgebungsvariablen im Frontend und die serverseitige Handhabung sensibler Authentifizierungsdaten.

## Funktionen

- Konfiguration und Verwaltung mehrerer Umgebungen mit unterschiedlichen Einstellungen  
- Zugriff auf Umgebungsvariablen im Frontend über JavaScript  
- Konfiguration von OAuth-Endpunkten mit Authentifizierungsinformationen  
- Sichere API-Kommunikation über Proxy-Endpunkte mit automatischer Authentifizierung  
- Kontrolle der SSL-Zertifikatsprüfung für Entwicklungsumgebungen  

## Installation

1. Addon über den REDAXO-Installer installieren oder manuell hochladen  
2. Addon im REDAXO-Backend aktivieren  

## Konfiguration

### Umgebungen

Der Bereich „Umgebungen“ ermöglicht dir:

1. **Mehrere Umgebungen definieren**: Erstelle Sets von Umgebungsvariablen für verschiedene Kontexte (z. B. Entwicklung, Test, Produktion).  
2. **Aktive Umgebung auswählen**: Lege fest, welche Umgebungskonfiguration aktiv ist und im Frontend verfügbar sein soll.  
3. **JavaScript-Variablennamen konfigurieren**: Definiere den globalen JavaScript-Variablennamen, über den im Browser auf Umgebungsvariablen zugegriffen wird (Standard: `ENV`).

![rex-1](https://github.com/user-attachments/assets/5e2153e8-5717-45b8-a189-7cb350820d63)

### OAuth-Konfiguration

Der Bereich „OAuth“ ermöglicht dir:

1. **Authentifizierungsendpunkte konfigurieren**: Lege die OAuth-Endpunkt-URLs und erforderlichen Zugangsdaten fest.  
2. **OAuth-Eintrags-IDs definieren**: Erstelle eindeutige Bezeichner für jeden OAuth-Endpunkt.  
3. **Grant Types konfigurieren**: Unterstützung für verschiedene OAuth-Grant-Typen, aktuell nur Client Credentials supported.  
4. **Client-IDs und Secrets verwalten**: Authentifizierungsdaten sicher speichern.

![rex-2](https://github.com/user-attachments/assets/0b871f19-871a-40de-80e3-9bb7b4162e04)

### Proxy-Konfiguration

Der Bereich „Proxy“ ermöglicht dir:

1. **API-Proxys erstellen**: Richte Endpunkte ein, die als Vermittler zwischen deinem Frontend und Backend-APIs fungieren.  
2. **Mit OAuth-Einträgen verknüpfen**: Verbinde Proxys mit den OAuth-Konfigurationen für automatische Authentifizierung.  
3. **SSL-Verifizierung steuern**: SSL-Zertifikatsprüfung pro Proxy für Entwicklungsumgebungen ein- oder ausschalten.  
4. **Sicheren API-Zugriff gewährleisten**: Halte sensible Authentifizierungsinformationen auf dem Server und außerhalb des Frontend-Codes.  

Proxys helfen, die Sicherheit zu erhöhen, indem sie verhindern, dass Zugangsdaten im Clientcode offengelegt werden. Sie fügen automatisch die korrekten Authentifizierungsheader in API-Anfragen ein.

![rex-3](https://github.com/user-attachments/assets/67828759-ee70-4833-b507-073438ff8dae)

## Funktionsweise

### 1. Verwaltung von Umgebungen

- Definiere mehrere Umgebungen mit unterschiedlichen Konfigurationswerten  
- Setze eine Umgebung als „aktiv“, um sie im Frontend verfügbar zu machen  
- Greife über ein globales JavaScript-Objekt auf Umgebungsvariablen zu  
- Bestimme, unter welchem Namen die Variable verfügbar ist (z. B. `window.ENV`, `window.CONFIG`, etc.)  

### 2. OAuth-Integration

- Konfiguriere OAuth-Endpunkte mit Authentifizierungsdetails  
- Unterstützung für verschiedene OAuth-Grant-Typen  
- Automatische Token-Abfrage und -Erneuerung  
- Sichere Speicherung von Client-Secrets auf dem Server  

### 3. API-Proxying

- Erstelle Proxy-Endpunkte, die Anfragen an externe APIs weiterleiten  
- Füge automatisch Authentifizierungsheader in Anfragen ein  
- Halte Zugangsdaten sicher, indem die Authentifizierung serverseitig erfolgt  
- Steuere SSL-Verifikationseinstellungen für jeden Proxy individuell  

## Anwendungsbeispiele

### Zugriff auf Umgebungsvariablen in JavaScript

```javascript
// Bei Nutzung des Standard-Variablennamens "API_ENV"
console.log(window.API_ENV);

// Zugriff auf eine bestimmte Variable
const apiUrl = window.API_ENV;
```

### Proxy verwenden

```javascript
// Bei Nutzung des Standard-Variablennamens "API_ENV"
const myEnv = window.API_ENV;
const isProd = myEnv === 'production';
const apiUrl = isProd ? 'https://api.my-prod-api.wow/...' : 'not so wow but still awesome';

// proxy-id = id von konfiguriertem Proxy Eintrag in Redaxo
// target = url des angezielten api Endpunktes
fetch(`${apiUrl}/?rex-api-call=proxy_request&proxy-id=my-cool-proxy-id&target=my-even-cooler-api-endpoint`, ....)
```


