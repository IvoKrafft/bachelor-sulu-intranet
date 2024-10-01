# Bachelorarbeit Ivo Krafft: Sulu Intranet

In diesem Dokument wird erklärt, wie Sie ein bestehendes Sulu-Projekt mit Docker starten können. Sulu ist ein Content-Management-System (CMS) auf Basis des Symfony PHP-Frameworks.

## Entwicklungsumgebung

### Frontend

Für die Entwicklung vom Frontend (Login, Passwort zurücksetzen, usw.) wird [Encore](https://symfony.com/doc/current/frontend.html) genutzt, hierfür muss [Node.js](https://nodejs.org) installiert sein. Mit `npm install` die benötigten Abhängigkeiten installieren.

Anschließend kann mit folgendem Befehl der Build Prozess gestartet werden, es wird automatisch auf Änderungen an Dateien geprüft und neu gebaut.
```console
npm run watch
```

## Deployment

### Frontend
Das Frontend kann mit folgendem Befehl für den produktiven Einsatz gebaut werden.
```console
npm run build
```
> Die gebauten Dateien werden automatisch im `public/build` Ordner abgelegt.

## Voraussetzungen

- Docker
- Docker Compose

### Umgebungsvariablen für Docker
Bevor Sie das Projekt starten, müssen Sie sicherstellen, dass die folgenden Umgebungsvariablen gemäß Ihrer Umgebung gesetzt sind:

- COMPOSE_PROJECT_NAME: Der Name für Docker Compose Projekt muss eindeutig auf dem Server sein, um aktive Installationen nicht zu stören
- WEB_PORT: Der Port, über den Ihr Webserver erreichbar sein soll
- MYSQL_ROOT_PASSWORD: Das Root-Passwort für die MySQL-Datenbank
- MYSQL_DATABASE: Der Name der MySQL-Datenbank für Ihr Projekt
- MYSQL_USER: Der MySQL-Benutzername für Ihr Projekt
- MYSQL_PASSWORD: Das MySQL-Passwort für Ihr Projekt
- PMA_PORT: Der Port für PhpMyAdmin

Diese Variablen können in einer .env-Datei in Ihrem Projektverzeichnis gesetzt werden. Erstellen Sie diese Datei, falls sie noch nicht vorhanden ist, und fügen Sie die oben genannten Variablen hinzu, z. B.:
```
COMPOSE_PROJECT_NAME=SULU-INTRANET
CONTAINER_PREFIX=sulu
WEB_PORT=8080
MYSQL_ROOT_PASSWORD=secret
MYSQL_DATABASE=suludb
MYSQL_USER=user
MYSQL_PASSWORD=pass
PMA_PORT=8081
```
Passen Sie die Werte entsprechend Ihrer Umgebung an.

### Umgebungsvariablen für Symfony

Bevor Sie das Projekt starten, müssen Sie sicherstellen, dass die folgenden Umgebungsvariablen gemäß Ihrer Umgebung für Symfony gesetzt sind:

- APP_ENV: Diese Variable bestimmt die Anwendungsumgebung. Für eine Entwicklungsumgebung sollte dieser Wert auf dev gesetzt werden.
- APP_SECRET: Diese Variable ist ein zufälliger String, der zum Erzeugen von verschlüsselten Cookies oder zum Signieren von temporären Links verwendet wird.
- DATABASE_URL: Diese Variable gibt die Verbindungsinformationen für die Datenbank an, mit der Ihre Anwendung interagieren wird.
- MAILER_DSN: Diese Variable gibt die Verbindungsinformationen für den Mailer-Dienst an. Dies wird zur Konfiguration der E-Mail-Dienste verwendet, die von Ihrer Anwendung genutzt werden.

```
APP_ENV=dev
APP_SECRET=your_secret_string
DATABASE_URL=mysql://db_user:db_password@database/db_name
MAILER_DSN=smtp://localhost
```
Bitte ersetzen Sie db_user, db_password, db_name, your_secret_string und andere Platzhalter durch Ihre tatsächlichen Werte.

Beachten Sie, dass Sie möglicherweise weitere Umgebungsvariablen benötigen, abhängig von den spezifischen Anforderungen und Abhängigkeiten Ihrer Anwendung.

### Schritte zum Starten des Projekts
1. Klonen Sie das Repository
2. .env Variablen (s. oben) setzen
3. Starten Sie die Docker-Container `docker-compose up -d`
4. Wechseln Sie in den `app` Container mit `docker-compose exec app bash`
5. Führen Sie `composer install` aus
6. Initialisieren der Datenbank mit `php bin/console sulu:build dev`

Danach sollte das System unter dem eingegebenen Port erreichbar sein. Das Admin-System kann über `/admin` erreicht werden.

### Importieren von Datenbank und Dateien
In dem Ordner 'import-data' befindet sich die Datei `sulu.sql`, die die Datenbankstruktur und die Daten enthält. Sie können diese Datei in Ihre Datenbank importieren, um Test Daten zu erhalten.
