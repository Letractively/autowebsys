function Application() {
}

Application.prototype.initDom = function() {
    this.desktop = document.createElement('div');
    this.desktop.id = 'desktop';
    this.desktop.style.border = '1px solid gray';
    this.desktop.style.height = (this.getFullHeight() - 45) + 'px';
    this.taskbar = document.createElement('div');
    this.taskbar.id = 'taskbar';
    document.body.appendChild(this.desktop);
    document.body.appendChild(this.taskbar);
    this.windowsManager = new WindowsManager(this.desktop);
    this.mainMenu = new MainMenu(this.taskbar);
}

Application.prototype.initEvents = function() {
    if(window.addEventListener) {
        window.addEventListener('resize', this.onResize, false);
    } else if(window.attachEvent) {
        window.attachEvent('onresize', this.onResize);
    } else {
        window.onresize = this.onResize;
    }
}

Application.prototype.getFullHeight = function() {
    if(typeof(window.innerHeight) == 'number' ) {
        return window.innerHeight;
    } else if(document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)) {
        return document.documentElement.clientHeight;
    } else if(document.body && (document.body.clientWidth || document.body.clientHeight)) {
        return document.body.clientHeight;
    }
    return 0;
}

Application.prototype.onResize = function(x, y) {
    application.desktop.style.height = (application.getFullHeight() - 45) + 'px';
}

function WindowsManager(div) {
    this.windowsManager = new dhtmlXWindows();
    this.windowsManager.enableAutoViewport(false);
    this.windowsManager.attachViewportTo(div.id);
    this.windowsManager.setImagePath('/dhtmlx/imgs/');
    this.uid = 1;
    this.windows = Array();
}

WindowsManager.prototype.createWindow = function(title, pos_x, pos_y, width, height, icon, content) {
    this.uid++;
    icon = icon || 'new.gif';
    content = content || 'not found';
    this.windows[this.uid] = this.windowsManager.createWindow(this.uid);
    this.windows[this.uid].setDimension(width, height);
    this.windows[this.uid].setPosition(pos_x, pos_y);
    this.windows[this.uid].uid = this.uid;
    this.windows[this.uid].setText(title);
    this.windows[this.uid].attachHTMLString(content);
    this.windows[this.uid].attachEvent('onClose', application.windowsManager.onClose);
    application.mainMenu.addWindowToTask(this.uid, title, icon);
}

WindowsManager.prototype.onClose = function(window) {
    window.hide();
    application.windowsManager.windows[window.uid] = null;
    application.mainMenu.removeWindowFromTask(window.uid);
}

function MainMenu(div) {
    this.start = document.createElement('div');
    this.start.id = 'start';
    this.start.style.width = '60px';
    this.start.style.cssFloat = 'left';
    this.bar = document.createElement('div');
    this.bar.id = 'bar';
    div.appendChild(this.start);
    div.appendChild(this.bar);
    this.menu = new dhtmlXMenuObject(this.start.id, 'dhx_skyblue');
    this.menu.setIconsPath("/imgs/");
    this.menu.loadXML('/data/index/type/main-menu');
    this.menu.attachEvent("onClick", this.clickOnMenu);
    this.taskbar = new dhtmlXToolbarObject(this.bar.id, 'dhx_skyblue');
    this.taskbar.setIconPath("/imgs/");
    this.taskbar.attachEvent('onClick', this.clickOnTaskbar);
}

MainMenu.prototype.clickOnMenu = function(id) {
    if(id.startsWith("m_")) {
        application.windowsManager.getWindow(id.substring(2, id.length));
    } else {
//logout
}
}

MainMenu.prototype.addWindowToTask = function(id, title, icon) {
    this.taskbar.addButton('t' + id, null, title, icon);
}

MainMenu.prototype.removeWindowFromTask = function(id) {
    this.taskbar.removeItem('t' + id);
}

MainMenu.prototype.clickOnTaskbar = function(id) {
    id = id.substring(1, id.length);
    if(application.windowsManager.windows[id].isHidden() || !application.windowsManager.windows[id].isOnTop()) {
        application.windowsManager.windows[id].show();
        application.windowsManager.windows[id].bringToTop();
    } else if(application.windowsManager.windows[id].isOnTop()) {
        application.windowsManager.windows[id].hide();
    }
}

WindowsManager.prototype.getWindow = function(id) {
    var serializer = new XMLSerializer();
    var description = dhtmlxAjax.getSync("/data/index/type/window-content/name/" + id).xmlDoc.responseXML;
    var title = description.getElementsByTagName('title')[0];
    title = (title ? title.textContent : "");
    var pos_x = description.getElementsByTagName('pos_x')[0];
    pos_x = (pos_x ? pos_x.textContent : undefined);
    var pos_y = description.getElementsByTagName('pos_y')[0];
    pos_y = (pos_y ? pos_y.textContent : undefined);
    var width = description.getElementsByTagName('width')[0];
    width = (width ? width.textContent : undefined);
    var height = description.getElementsByTagName('height')[0];
    height = (height ? height.textContent : undefined);
    var icon = description.getElementsByTagName('icon')[0];
    icon = (icon ? icon.textContent : undefined);
    var content = description.getElementsByTagName('content')[0];
    if(content) {
        content = serializer.serializeToString(content);
        alert(content);
        var begin = 9;
        var length = content.length - 10;
        alert(begin + " " + length);
        content = content.substring(begin, length);
        alert(content);
    }
    this.createWindow(title, pos_x, pos_y, width, height, icon, content);
}

String.prototype.startsWith = function(str){
    return (this.indexOf(str) === 0);
}

var application = new Application();
application.initDom();
application.initEvents();
