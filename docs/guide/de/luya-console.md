Konsolenbefehle
================
Um einen *Konsolenbefehl* auszuführen öffnest du ein *Terminal* und gehst in in dein Projekt Verzeichniss (`cd /dein/projekt/`). Damit du **nich**t extra eine Konsolen ausführbare Datei erstellen musst um Konsolenbefehle anzusprechen übernimmt das *LUYA* für dich in der `index.php` Datei. Um einen der untenstehende befehle auszuführen gibt das also folgendes in deiner Konsole ein:

```sh
php index.php COMMAND
```

Wobei *COMMAND* natürlich einem befehl aus der folgenden liste entsprechen sollte.

Befehlsübersicht
----------------

|Befehl            |Optionen                      |Beispiel                  |Beschreibung
| --------         | ---------------              | ---------                 | ---------
|migrate           |-                             |`migrate`                 |Alle Migrations-Scripte in *allen Modulen* werden ausgeführt und die Datenbank-Struktur gemäss Scripte angepasst.
|migrate/create    |$tableName [, $moduleName ]   |`migrate/create team_members teammodule` |Erstellt eine neue Migrationsdatei mit dem Tabellen Name `team_members` innerhalb des modules `teammodule`.
|exec/setup        |-                             |`exec/setup`              |Führ das Luya Setup aus (= Erstellt einen Benutzer, Grupppe und die nötigen Berechtiungen).
|exec/import       |-                             |`exec/import`             |1. Erneuert die Berechtiung. 2. Importierter Cms Blöck. 3. Aktualisiert Cms Layouts 4. Aktualisiert Admin Filters Sie können auch [eigene importers](app-module.md#import-methode) anhängen.
|crud/create       |-                             |`crud/create`             |Erstellt alle nötigen Daten für ein [NgRest Crud](app-admin-module-ngrest.md) mit einem Assistenten.
|module/create     |-                             |`module/create`           |Erstellt ein [Frontend/Admin Module](app-module.md) mit einem Assistenten.
|command           |$moduleName, $route           |`command teammodule controller/action` |Ausführen eines eigenens Konsolenbefehls.

Einen Command erstellen
------------------------
Eigene Konsolenbehle (auch commands gennant) werden innerhalb eines Modules im Ordner `commands` hinterlegt. Der wesentliche Unterschied zu normalen Controllern liegt darin das Sie keinen Return Output haben. So könnte ein Beispiel befehl aussehen:

```php
<?php

namespace yourmodule\commands;

class NotifyController extends \luya\base\Command
{
    public function actionIndex()
    {
        return $this->outputSuccess('action successfully done');
    }
    
    public function actionBar()
    {
        return $this->ouputError('Something failed inside this action');
    }
}
```

> Verwenden Sie immer `ouputError($message)` oder `outputSuccess($message)` um innerhalb der Commands outputs zu generieren. Somit kannst du deine Commands einfach mit PHPUnitTest versehen.

Um die Action `actionIndex()` in diesem Controller `NotifyController` auszuführen Geben Sie nun 

```sh
php index.php command yourmodule notify
```

ein. Wobei `actionIndex` als Default/Standard action gebnutzt wird. Um die Action `actionBar()` auszuführen geben Sie  ein:

```sh
php index.php command yourmodule notify/bar
```

