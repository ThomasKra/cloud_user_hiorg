# nextcloud_user_hiorg

Diese Benutzerschnittstelle bietet die Möglichkeit, dass sich Benutzer mit ihrem HiOrg-Benutzerdaten an einem Nextcloud-Server authentifizieren können.
Funktioniert sowohl im Webinterface, alsauch in den Apps (Android, iOS, MacOS, Windows).

## Erste Schritte

### Gruppen erstellen

Um HiOrg-Benutzer von den Standard-Nextcloud Benutzern unterscheinen zu können, ist es nötig eine Gruppe für alle Benutzer, die sich über das HiOrg-Interface authentifizieren zu erstellen.

Alle HiOrg-Nutzer werden dann Mitglieder in dieser Gruppe.

Außerdem besteht die Möglichkeit die Gruppenzugehörigkeit in HiOrg auf Gruppen im Nextcloud zu mappen. Hierfür sind für alle zu nutzenden HiOrg-Gruppen die entsprechenden Gruppen in Nextcloud zu erstellen. Die Namen müssen hierfür nicht identisch sein, die Gruppen werden dann in den Einstellungen zugewiesen.

Dies bietet die Möglichkeit Freigaben für Gruppen zu realisieren und somit einer Benutzergruppe eine Freigabe bereitzustellen.

### Einstellungen

Die Einstellungen zum HiOrg-Interface befinden sich unter "Verwaltung -> Zusätzliche Einstellungen".

Hier werden zum einen das Kürzel des HiOrg-Servers benötigt, sowie die sog. "Allg. Hi-Org Gruppe". Jeder Benutzer der sich über einen HiOrg-Server authentifiziert wird automatisch Mitglied dieser Gruppe.

Außerdem gibt es hier die Möglichkeit einer Zuweisung der HiOrg-Gruppen auf die Nextcloud-Gruppen. Die Gruppenzuweisung jedes Nutzers wird bei jedem Login überprüft und ggf. geändert.

Außerdem kann ein Quota für jeden HiOrg-Benutzer angelegt werden. Dies ist sehr sinnvoll, falls man unterbinden will, dass Benutzer Daten auf dem eigenen Account anlegen können.
