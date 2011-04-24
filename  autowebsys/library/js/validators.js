function isDomainNameUnique(value, form) {
    return validators.isUnique('dictionaries_domains_form_unique_name', 'id_domains', form.idvalue, 'name', value);
}

function isGroupNameUnique(value, form) {
    return validators.isUnique('dictionaries_groups_form_unique_name', 'id_groups', form.idvalue, 'name', value);
}

function isUserGroupNameUnique(value, form) {
    return validators.isUnique('users_groups_form_unique_name', 'id_users_groups', form.idvalue, 'name', value);
}