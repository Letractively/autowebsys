# Szybki start #

## Pobranie źródeł AWS ##
Nie istnieje żadna procedura budowy paczek, aktualna wersja jest dostępna na SVN. W trunku powinna być wersja stabilna. SVN jest dostępny w trybie do odczytu pod: `http://autowebsys.googlecode.com/svn/trunk/ autowebsys-read-only`
## Konfiguracja środowiska ##
Platforma jest przystosowana do pracy pod kontrolą serwera Apache. Wymagane jest włączenie modułów vhost oraz RewriteEngine. Przy konfigurowaniu Virtual Hosta należy pamiętać o formułce `AllowOverride All` oraz wskazaniu folderu autowebsys/public jako początkowego. Bez tego strona nie będzie działać poprawnie. RewriteEngine wystarczy, że będzie włączony, definicje przepisywania url'i są zdefiniowane w aplikacji.

Apache musi być skompilowany z obsługą PHP w wersji co najmniej 5.3.5 oraz z włączoną obsługą short tags - domyślnie jest to wyłączone.

W celu optymalizacji parsowania XML, platforma wymaga serwera [Memcache](http://memcached.org/) działającego na localhoscie na standardowym porcie(11211). Dodatkowo potrzebna będzie biblioteka PHP to obsługi [Memcache'a](http://pecl.php.net/package/memcache).

Logi platformy potrzebują biblioteki [Pear Log](http://pear.php.net/package/Log/redirected)

## Podstawowa konfiguracja platformy ##
AWS pozwala na dosyć prostą implementację własnych adapterów autoryzacji, jednak zalecane jest użycie adaptera w oparciu o role i grupy dostępu z konfiguracją w bazie danych.
### Przygotowanie struktury bazy danych pod autoryzacje ###
Jeden użytkownik może należeć do jednej grupy, dodatkowo każda grupa może należeć do dowolnie wielu innych. Prawa dostępu użytkownika są sumą praw danej grupy i jej zależności.
Przy definicji struktury zależności między grupami, należy zbudować strukturę drzewiastą, AWS nie ma mechanizmu wykrywania cykli(tzn. chyba ma ale nigdy nie testowałem).
```
CREATE TABLE users (
  id_users serial,
  www_login character varying,
  www_pass character varying,
  id_users_groups integer,
  CONSTRAINT users_users_groups_fkey FOREIGN KEY (id_users_groups)
      REFERENCES users_groups (id_users_groups) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE SET NULL,
  CONSTRAINT www_login_unique UNIQUE (www_login)
);

CREATE TABLE users_groups (
  id_users_groups serial,
  name character varying,
);

CREATE TABLE users_groups_relations (
  id_users_groups_relations serial,
  id_users_group integer NOT NULL,
  id_member_of integer NOT NULL,
  CONSTRAINT id_users_groups_fkey FOREIGN KEY (id_users_group)
      REFERENCES users_groups (id_users_groups) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION,
  CONSTRAINT id_users_groups_member_fkey FOREIGN KEY (id_member_of)
      REFERENCES users_groups (id_users_groups) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);
```
### Minimalna konfiguracja aplikacji ###
#### Główne założenia ####
Z założenia, platforma ma umożliwiać uruchomienie pełnej i funkcjonalnej aplikacji bez konieczności pisania kodu PHP. Całość ma być skonfigurowana za pomocą plików XML. Oczywiście są też mechanizmu ułatwiające włączanie własnych rozwiązań, który wymagają kodowania.
#### Struktura pliku XML ####
Konfiguracja XML została podzielona na kilka sekcji:
  * warstwa danych
  * parametry niestandardowe
  * interfejs użytkownika
  * okna
  * modele danych
  * szablony
  * tagi
  * kontrolery
  * autoryzacja
Minimalny plik XML ma strukturę:
```
<?xml version="1.0" encoding="UTF-8"?>

<config>
    <data>
        <datasource url="[connection_string]" user="[user]" password="[password]" />
        <queries>
            
        </queries>
    </data>
    <parameters>
        <param name="authAdapter">DBAdapter</param>
        <param name="cache">true</param>
    </parameters>
    <interface>
        <main-menu>
        </main-menu>
    </interface>
    <windows>
    </windows>
    <models>
    </models>
    <templates>
    </templates>
    <tags>
    </tags>
    <controllers>
    </controllers>
    <security>
    </security>
</config>
```
Plik można dowolnie dzielić używając dyrektywy `<include path="<relative_path_to_xml>" />` w dowolnym miejscu pliku
#### Dostęp do warstwy danych ####
AWS wykorzystuje PDO do łączenia się z bazą danych, zatem nie jest zależny od jednego dostawcy. Definicja dostępu do DB jest opisana za pomocą `connection string'a`. Przykład dla MySQL'a: http://www.php.net/manual/en/pdo.connections.php

Wszystkie zapytania do bazy danych wykorzystywane przez aplikację, muszą być zawarte w drzewie `<queries>`. Zapytania umieszcza się w formie `<query name="select_users">SELECT * FROM users</query>`. Nazwa zapytania musi być unikatowa. W przypadku powtórzenia nazwy, zapytanie zostanie nadpisane. W zapytaniach można używać parametrów w formie `:PARAM_NAME` - wymagane w przypadku np. użycia formularzy.
#### Autoryzacja ####
Dla zalecanej konfiguracji dostępu, drzewo `<security>` powinno zawierać poniższe wpisy:
```
<db>
    <groups>
        <queries>
            <select>auth_group_parent</select>
        </queries>
    </groups>
    <roles>
        <queries>
            <select>auth_select_user</select>
        </queries>
    </roles>
</db>
```
Zapytania dla wspomnianej struktury muszą wyglądać następująco:
```
<query name="auth_select_user">
SELECT www_login, www_pass, users_groups.name FROM users JOIN users_groups ON (users.id_users_groups = users_groups.id_users_groups) WHERE www_login = :www_login
</query>
<query name="auth_group_parent">
SELECT parents.id_users_groups, parents.name FROM users_groups JOIN users_groups_relations ON(users_groups.id_users_groups = users_groups_relations.id_users_group) JOIN users_groups AS parents ON(users_groups_relations.id_member_of = parents.id_users_groups) WHERE users_groups.name = :name
</query>
```
#### Interfejs użytkownika ####
Interfejs użytkownika jest wzorowany na tym, znanym z Windows'a. Pasek menu jest definiowany w drzewie `<main-menu>`, okna w `<windows>`. Pasek menu może zawierać dowolnie wiele zagłębień. Przycisk `Logout` jest dodawany automatycznie.
Poniżej przedstawiam przykładową definicję menu:
```
<main-menu>
    <item id="administration_tools" text="Administration tools" img="new.gif" security="admins">
        <item id="users_menu" text="Users" img="new.gif" security="admins">
        <item id="users_groups" text="Users groups" img="new.gif" security="admins"/>
    </item>
    <item id="client_account" text="Account" img="new.gif" security="clients"/>
</main-menu>
```
Opis parametrow:
  * id - dla liście drzewa, oznacza że należy otworzyć okno o takim samym id
  * test - etykieta pozycji
  * img - ścieżka do ikonki pozycji
  * security - nazwa grupy posiadającej dostęp do tej pozycji. W przypadku braku dostępu, pozycja nie jest w ogole rysowana

Przykładowa definicja okna:
```
<window id="client_account">
    <title>Account</title>
    <security>GROUP_NAME</security>
    <width>400</width>
    <height>150</height>
    <pos_x>10</pos_x>
    <pos_y>10</pos_y>
    <content>
        <html>
            <h1>Title</h1>
            HTML content...<br/>
        </html>
    </content>
</window>
```
Opis parametrow:
  * id - ID okna, wymagane do związania z pozycja w menu
  * title - tytuł okna
  * security - co prawda, menu ukrywa dostęp do pozycji, do których użytkownik nie ma dostępu. Jednak dostęp do zawartości okna można uzyskać również za pomocą spreprarowanego URL'a, więc w definicji okna trzeba powtórzyć poziom dostępu
  * width - szerokość okna
  * height - wysokość okna
  * pos\_x - pozycja X
  * pos\_y - pozycja Y
  * content - zawartość okna - w celu poprawienia czytelności może zawierać dowolnie dużo poddrzew `<html>`
#### Parametry niestandardowe ####
W konfiguracji XML można również umieszczać własne parametry dostępne w ramach całej platformy. AWS wymaga obecności dwóch parametrów:
  * authAdapter - nazwa adaptera autoryzacji, nazwa musi się pokrywać z nazwą pliku w `autowebsys/library/adapters/auth`
  * cache - przełącznik cachowania konfiguracji XML, przyjmuje wartości true|false. W przypadku włączenia, po zmianie flagi trzeba zresetować serwer memcache.
#### Szablony ####
W celu poprawienia przejrzystości konfiguracji okien, wydzielona została sekcja tworzenia szablonów, które można wstawiać w dowolne okna(opis użycia znajduje się poniżej, w sekcji Tagi). Poniżej definicja przykładowego szablonu:
```
<template>
    <name>dummy_template</name>
    <html>
        <div>
            HTML content...
        </div>
    </html>
</template>
```
#### Tagi ####
Oprócz umieszczania statycznych elementów w plikach XML, istnieje możliwość używania tagów, generujących treści przy każdym żądaniu zasobu. Tagi mogą być używane w dowolnym miejscu, w postaci ${[tag](tag.md)([param1[, param2 ...]])}. Poniżej lista kilku przydatnych tagów:
  * ${translator(BUNDLE\_KEY)} - generator zlokalizowanych etykiet
  * ${template(TEMAPLATE\_NAME)} - wstawia wskazany szablon
  * ${model(MODEL\_TYPE, MODEL\_NAME)} - wstawia wskazany model
  * ${tabbar(tab1[, tab2 ...])} - tworzy tabbar'a ze wskazanych okien
  * ${param(PARAM\_NAME)} - wstawia parametr systemowy
  * ${HTMLCombo(COMBO\_NAME, BIND[, CHILD\_COMBO[, CHILD\_INPUT]])} - wstawia combo box'a dla wskazanego modelu
#### Modele danych ####
W celu wstawienia tabeli czy formularza w oknie, należy stworzyć jego model danych i umieścić w drzewie `<models>`. Kontrolki wstawia się za pomocą taga ${model(MODEL\_TYPE, MODEL\_NAME)}. MODEL\_TYPE przyjmuje wartość `grid` dla tabeli oraz `form` dla formularza.
##### Tabele #####
Poniżej minimalny model wymagany do stworzenia tabeli:
```
<model>
    <name>GRID_NAME</name>
    <security>GROUP_NAME</security>
    <type>sql</type>
    <sql>
        <select>SELECT_QUERY_NAME</select>
        <delete>DELETE_QUERY_NAME</delete>
        <id>ID_FIELD</id>
        <columns>COLUMN_1,COLUMN_2,...,COLUMN_N</columns>
    </sql>
</model>
```
Opis parametrów:
  * name - nazwa tabeli, wymagana w celu identyfikacji
  * security - analogicznie jak w przypadku okien, do danych można dostać się za pomocą spreparowanego url'a, więc tutaj również wymagane jest ustalenie praw dostępu
  * type - zawsze przyjmuje wartość `sql`
  * sql - poddrzewo definicji zarządzania danymi
Rozszerzony opis modelu dla tabeli znajduje się [tutaj](DataGrid.md).
##### Formularze #####
Formularz składa się z dwóch części: modelu i szablonu. Poniżej minimalny model:
```
<model>
    <name>FORM_NAME</name>
    <security>GROUP_NAME</security>
    <type>sql</type>
    <template>TEMPLATE_NAME</template>
    <sql>
        <insert>INSERT_QUERY_NAME</insert>
        <select>SELECT_QUERY_NAME</select>
        <update>UPDATE_QUERY_NAME</update>
        <sequence>SEQUENCE_NAME</sequence>
        <id>ID_FIELD</id>
        <columns>FIELD_1,FIELD_2,...,FIELD_N</columns>
    </sql>
</model>
```
Formatki dla szablonu, skladają sie ze standardowego pola typu `input`: `<input type="text" bind="FIELD_A" validate="NotEmpty" />`. Atrybut `bind` oznacza, że wartość pola zostanie wstawiona pod parametr i takiej samej nazwie w zapytaniu SQL. Dodatkowo wymagane są dwa specjalne pola:
  * pole ID - `<input type="hidden" bind="ID_FIELD" />`
  * pole save - `<input type="button" id="save" value="Save" />`
Pole ID 'musi' być zbindowane do pola ID wskazanego w modelu. Pole save musi posiadać atrybut `id` z wartością `save`
## Podsumowanie ##
Powyższy opis nie pokrywa wszystkich możliwości platformy, jest to tylko zupełne minimum wymagane do postawienia aplikacji. Szerszy opis możliwości jest zawarty na pozostałych stronach tego wiki. Pełny XML do przykładu opisanego na tej stronie jest dostępny [tutaj](SampleXML.md)