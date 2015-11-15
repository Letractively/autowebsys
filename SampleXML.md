Przykładowy XML dla w pełni funkcjonalnej aplikacji:
```
<?xml version="1.0" encoding="UTF-8"?>

<config>
    <data>
        <!-- Pamiętaj, żeby wpisać tutaj własne dane dostępowe ! -->
        <datasource url="[connection_string]" user="[user]" password="[password]" />
        <queries>
            <!-- Sekcja zapytań dla wymaganych przez mechanizm autoryzacji -->
            <query name="auth_select_user">
                SELECT 
                    www_login, 
                    www_pass, 
                    users_groups.name 
                FROM 
                    users 
                    JOIN users_groups ON (users.id_users_groups = users_groups.id_users_groups) 
                WHERE 
                    www_login = :www_login
            </query>
            <query name="auth_group_parent">
                SELECT 
                    parents.id_users_groups, 
                    parents.name 
                FROM 
                    users_groups 
                    JOIN users_groups_relations ON(users_groups.id_users_groups = users_groups_relations.id_users_group) 
                    JOIN users_groups AS parents ON(users_groups_relations.id_member_of = parents.id_users_groups)
                WHERE 
                    users_groups.name = :name
             </query>
             <!-- Koniec sekcji zapytań dla autoryzacji -->
        </queries>
    </data>
    <parameters>
        <!-- Adapter autoryzacji pozwalający na tworzenie list ACL -->
        <param name="authAdapter">AdvancedDBAdapter</param>
        <!-- Domyślnie cache jest wyłączony, pamiętaj żeby włączyć na środowisku produkcyjnym ! -->
        <param name="cache">false</param>
    </parameters>
    <interface>
        <main-menu>
            <!-- Sekcji menu dla administratorów -->
            <item id="administration_tools" text="Administration tools" img="new.gif" security="admins">
                <item id="users_menu" text="Users" img="new.gif" security="admins">
                <item id="users_groups" text="Users groups" img="new.gif" security="admins"/>
            </item>
            <!-- Sekcja menu dla zwykłych userów -->
            <item id="client_account" text="Account" img="new.gif" security="clients"/>
        </main-menu>
    </interface>
    <windows>
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
    </windows>
    <models>
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
    </models>
    <templates>
        <template>
            <name>dummy_template</name>
            <html>
                <div>
                    HTML content...
                </div>
            </html>
        </template>
    </templates>
    <tags>
    </tags>
    <controllers>
    </controllers>
    <security>
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
    </security>
</config>
```