var application;

function init() {
    application = new Application();
}

utils = {
    uid: 0,
    getFullHeight: function() {
        if(typeof(window.innerHeight) == 'number' ) {
            return window.innerHeight;
        } else if(document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
            return document.documentElement.clientHeight;
        } else if(document.body && (document.body.clientWidth || document.body.clientHeight)) {
            return document.body.clientHeight;
        }
        return 0;
    },
    initGlobalEvents: function() {
        if(window.addEventListener) {
            window.addEventListener('resize', utils.onResize, false);
        } else if(window.attachEvent) {
            window.attachEvent('onresize', utils.onResize);
        } else {
            window.onresize = utils.onResize;
        }
    },
    onResize: function() {
        application.controlls.desktop.setHeight((utils.getFullHeight() - 60) + 'px');
    },
    generateUID: function() {
        this.uid += 1;
        return this.uid;
    }
}

validators = {
    checkAndSend: function(form, processor, id) {
        form.idvalue = id;
        if(form.validate()) {
            processor.sendData();
        }
    },

    isUnique: function(queryName, idName, idValue, name, value) {
        loader = dhtmlxAjax.getSync('/data/index/type/unique/name/' + queryName +
            '/idname/' + idName + '/idvalue/' + idValue + '/cname/' + name + '/cvalue/' + value);
        return loader.xmlDoc.responseXML.getElementsByTagName('action')[0].getAttribute('result') == 0;
    }
}

String.prototype.startsWith = function(str){
    return (this.indexOf(str) === 0);
}

function Desktop() {
    this.desktop = document.getElementById('desktop');
    this.desktop.style.height = (utils.getFullHeight() - 45) + 'px';
    this.windowsManager = new dhtmlXWindows();
    this.windowsManager.enableAutoViewport(false);
    this.windowsManager.attachViewportTo(this.desktop.id);
    this.windowsManager.setImagePath('/dhtmlx/imgs/');
    this.windows = Array();
}

Desktop.prototype.setHeight = function(height) {
    this.desktop.style.height = height;
}

Desktop.prototype.createWindow = function(window) {
    var uid = utils.generateUID();
    this.windows[uid] = this.windowsManager.createWindow(uid, parseInt(window.get('posX')), parseInt(window.get('posY')), parseInt(window.get('width')), parseInt(window.get('height')));
    this.windows[uid].uid = this.uid;
    this.windows[uid].setText(window.get('title'));
    this.windows[uid].windowURL = window.get('content') + '/wid/' + uid;
    this.windows[uid].attachURL(this.windows[uid].windowURL, true);
    this.windows[uid].attachEvent('onClose', this.onClose);
    this.windows[uid].attachEvent('onResizeFinish', this.onResizeFinish);
    this.windows[uid].attachEvent('onMinimize', this.onResizeFinish);
    this.windows[uid].attachEvent('onMaximize', this.onResizeFinish);
    this.windows[uid].attachEvent('onParkUp', this.onParkUp);
    this.windows[uid].button('stick').show();
    application.controlls.bar.taskbar.addWindow(uid, window.get('title'), window.get('icon'));
}

Desktop.prototype.toolbarAction = function(id) {
    var window;
    var selectedId;
    var item = this.subItem;
    switch(item.awsType) {
        case "grid":
            selectedId = item.getSelectedRowId();
            break;
        case "tree":
            selectedId = item.getSelectedItemId();
            break;
    }
    switch(id) {
        case "add":
            window = application.communicator.getWindow(this.addWindow);
            application.controlls.desktop.createWindow(window);
            break;
        case "edit":
            if(selectedId != null) {
                window = application.communicator.getWindow(this.editWindow + "/id/" + selectedId);
                application.controlls.desktop.createWindow(window);
            } else {
                alert(item.notSelectedWarn);
            }
            break;
        case "delete":
            if(selectedId != null) {
                if(confirm(item.confirmDelete)) {
                    switch(item.awsType) {
                        case "grid":
                            item.deleteSelectedRows();
                            break;
                        case "tree":
                            selectedId = item.getSelectedItemId();
                            item.deleteItem(selectedId, true);
                            break;
                    }
                }
            } else {
                alert(item.notSelectedWarn);
            }
            break;
        case "refresh":
            if(item.awsType == 'grid') {
                item.clearAndLoad(item.url);
            } else if (item.awsType == 'tree') {
                item.refreshItem();
            }
            break;
        case "save":
            this.processor.sendData();
            break;
    }
}

Desktop.prototype.onParkUp = function(event) {
    var window = application.controlls.desktop.windows[event.idd];
    window.hide();
}

Desktop.prototype.onResizeFinish = function(event) {
    var window = application.controlls.desktop.windows[event.idd];
    window.attachURL(window.windowURL, true);
}

Desktop.prototype.onClose = function(window) {
    window.hide();
    application.controlls.bar.taskbar.removeWindow(window.idd);
    application.controlls.desktop.windows[window.idd] = null;
}

Desktop.prototype.refreshWindow = function(wid) {
    var window = application.controlls.desktop.windows[wid];
    window.attachURL(window.windowURL, true);
}

Desktop.prototype.closeWindow = function(wid) {
    var window = application.controlls.desktop.windows[wid];
    window.close();
}

Desktop.prototype.getWindowURL = function(wid) {
    return (application.controlls.desktop.windows[wid]).windowURL;
}

Desktop.prototype.setWindowURL = function(wid, url) {
    (application.controlls.desktop.windows[wid]).windowURL = url;
    this.refreshWindow(wid);
}

function MainMenu() {
    this.menu = new dhtmlXMenuObject('start', 'dhx_skyblue');
    this.menu.setIconsPath("/imgs/");
    this.menu.loadXML('/data/index/type/main-menu');
    this.menu.attachEvent("onClick", this.clickOnMenu);
}

MainMenu.prototype.clickOnMenu = function(id) {
    if(id.startsWith("m_")) {
        var window = application.communicator.getWindow(id.substring(2, id.length));
        application.controlls.desktop.createWindow(window);
    } else {
        document.location = '/index/logout';
    }
}

function Taskbar() {
    this.taskbar = new dhtmlXToolbarObject('taskbar', 'dhx_skyblue');
    this.taskbar.setIconPath("/imgs/");
    this.taskbar.attachEvent('onClick', this.onClick);
}

Taskbar.prototype.onClick = function(id) {
    id = id.substring(1, id.length);
    var windows = application.controlls.desktop.windows;
    if(windows[id].isHidden()) {
        windows[id].show();
        if(windows[id].isParked()) {
            windows[id].park();
        }
    } else if(windows[id].isOnTop()) {
        windows[id].hide();
    } else {
        windows[id].bringToTop();
    }
}

Taskbar.prototype.addWindow = function(id, title, icon) {
    this.taskbar.addButton('t' + id, null, title, icon);
}

Taskbar.prototype.removeWindow = function(id) {
    this.taskbar.removeItem('t' + id);
}

function Bar() {
    this.mainMenu = new MainMenu(this.taskbar);
    this.taskbar = new Taskbar(this.taskbar);
}

function Controlls() {
    this.desktop = new Desktop();
    this.bar = new Bar();
}

function Communicator() {

}

Communicator.prototype.getWindow = function(id) {
    var url = "/data/index/type/window-description/name/" + id;
    if(arguments.length > 1) {
        url += arguments[1];
    }
    var xml = dhtmlxAjax.getSync(url).xmlDoc.responseXML;
    xml = (xml.childNodes[0].childNodes.length == 0 ? xml.childNodes[1] : xml.childNodes[0]); //IE
    var window = new Window();
    for(var i = 0 ; i < xml.childNodes.length ; i++) {
        var item = xml.childNodes.item(i);
        if(item.nodeType == 1) {
            var name = item.nodeName;
            var value;
            try {
                value = (new XMLSerializer()).serializeToString(item);
            } catch(e) {
                value = item.xml; //IE
            }
            value = value.substring(name.length + 2, value.length - name.length - 3);
            window.set(name, value);
        }
    }
    window.set('content', '/data/index/type/window-content/name/' + id);
    return window;
}

function Application() {
    this.controlls = new Controlls();
    this.communicator = new Communicator();
    this.register = new Register();
    utils.initGlobalEvents();
}

function Window() {
    this.items = Array();
    this.items['title'] = 'window title';
    this.items['posX'] = 10;
    this.items['posY'] = 10;
    this.items['width'] = 500;
    this.items['height'] = 200;
    this.items['content'] = 'empty';
    this.items['icon'] = 'new.gif';
}

Window.prototype.set = function(name, value) {
    this.items[name] = value;
}

Window.prototype.get = function(name) {
    return this.items[name];
}

function Register() {
    this.register = Array();
}

Register.prototype.add = function(type, item) {
    if(this.register[type] == undefined) {
        this.register[type] = Array();
    }
    (this.register[type])[item.name] = item;
}

Register.prototype.get = function(type, name) {
    return (this.register[type])[name];
}

//TODO: Jak przy zamknieciu okna usunac tabelke z rejestru ?
Register.prototype.del = function(type, id) {
    (this.register[type])[id] = undefined;
}

Register.prototype.refresh = function(type) {
    for(var item in this.register[type]) {
        try {
            var item = (this.register[type])[item];
            if(item.awsType == 'grid') {
                item.clearAndLoad(item.url);
            } else if (item.awsType == 'tree') {
                item.refreshItem();
            }
        }catch(e) {
        //na wypadek gdyby tabelka istniaja w rejestrze ale zniknal
        //element html, np z powodu zamkniecia/przeladowania okna
        }
    }
}