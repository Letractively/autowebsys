function exec(url, response) {
    loader = dhtmlxAjax.getSync(url);
    document.getElementById(response).innerHTML = loader.xmlDoc.responseText;
}