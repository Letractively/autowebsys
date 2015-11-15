# Internacjonalizacja #
Silnik `autowebsys` dostarcza mechanizmu automatycznego wyboru języka strony na podstawie mechanizmów zawartych w protokole HTTP. Wszystkie etykiety zdefiniowane przez użytkownika powinny zostać wpisane do pliku/ów `.ini` i umieszczone zgodnie ze [strukturą plików](strukturaFolderow.md).

Jeśli zgłoszony język nie zostanie odnaleziony, wybrany zostanie j. angielski. Jeśli użyta zostanie etykieta, której nie ma w wybranym języku, system sprobuje użyć etykiety z j. angielskiego(zostanie wygenerowane ostrzeżenie przez PHP, w środowisku produkcyjnym ostrzeżenia powinny być wyłączone), jeśli ta również nie zostanie odnaleziona, użyta zostanie nazwa etykiety.

Etykiety muszą być umieszczone w pliku `.ini` według schematu:
```
<klucz> = "<etykieta>"
```
np.
```
USERNAME = "Username:"
PASSWORD = "Password:"
```
W celu użycia etykiety należy zastosować kontrukcję: ` {translator:<key>} ` dla plików XML oraz: ` Translator::_(<key>) ` dla plików PHP