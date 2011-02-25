function isExtensionUnique(value, form) {
    return validators.isUnique('user_select_by_extension', 'id', form.idvalue, 'extension', value);
}

function isUsernameUnique(value, form) {
    return validators.isUnique('user_select_by_username', 'id', form.idvalue, 'username', value);
}    



