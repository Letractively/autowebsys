# Charakterystyka projektu #

Wiele aktualnie działających systemów wspierających zarządzanie firmą/produktami opiera się  o schemat:
  * Baza danych przechowująca informacje;
  * Logika biznesowa oparta głownie o operacje CRUD na DB;
  * Interfejs użytkownika nastawiony na edycję formularzy i reprezentację danych z DB w formie tabelek i drzew.

Celem projektu jest zbudowanie, w oparciu o ten model, uniwersalnego i konfigurowalnego systemu dostarczającego gotowe rozwiązania przy włożeniu minimalnego wysiłku w ich stworzenie.

# Interfejs użytkownika #
W celu usprawnienia obsługi, zarządzanie systemem powiela wzorce znane z systemów operacyjnych - Pasek zadań(przycisk 'Start' + skróty + lista otwartych okien) + aplikacje/funkcje systemu prezentowane w oknach. Interfejs został zbudowany w oparciu o bibliotekę [DHTMLX](http://dhtmlx.com) na licencji GPL.

# Podstawowe założenia #
## Konfigurowalność ##
Użytkownik jest w stanie stworzyć kompletny system podając minimum danych:
  * Dane dostępowe do bazy danych;
  * Definicje głównego menu(lista funkcjonalności);
  * Definicje okien wraz z ich zawartością(paski narzędzi, tabele, drzewa, formularze);
  * Logika biznesowa(w oparciu o pliki [XML](http://code.google.com/p/autowebsys/wiki/XMLConfigExample) i ew. własne kontrolery po stronie serwera).
## Elastyczność ##
Jeśli potrzebne są funkcjonalności bardziej złożone, użytkownik ma możliwość ręcznego definiowania funkcji/okien za pomocą standardowych narzędzi(HTML, CSS, JS), przy możliwości pełnego dostępu do API _autowebsys_. Możliwe jest również dopisywanie kontrolerów po stronie serwera. Dostępny jest także mechanizm [internacjonalizacji](Internacjonalizacja.md)
## Bezpieczeństwo ##
System dostarcza mechanizmu weryfikacji oraz autoryzacji użytkowników na poziomie zasobów oraz funkcji. Weryfikacja oraz autoryzacja może być oparta o bazę danych, plik tekstowy lub XML. Użytkownik musi tylko zaimplementować interfejs i wpisać go do pliku konfiguracyjnego jako domyślny adapter autoryzacji. Więcej w [Autoryzacja](Autoryzacja.md)