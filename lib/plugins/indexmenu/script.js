function indexmenu_findExt (path){
    var ext = "gif";
    var cext = path.lastIndexOf(".");
    if ( cext > -1){
	cext++;
	cext = path.substring(cext, path.length).toLowerCase();
	if ((cext == "png") || (cext == "jpg")) {ext = cext;}
    }
    return ext;
}

function indexmenu_ajax (get,picker,btn,notoc) {
    var indx_list= $(picker);
    var show = false;
    if (!indx_list) {
	indx_list=indexmenu_createPicker(picker);
	indx_list.className='dokuwiki indexmenu_toc';
	indx_list.innerHTML='<a href="javascript: indexmenu_showPicker(\''+picker+'\');"><img src="'+DOKU_BASE+'lib/plugins/indexmenu/images/close.gif" /></a>';
	tocobj = document.createElement('div');
	indx_list.appendChild(tocobj);
    }
    if (!notoc) {
	show=true;
	indx_list=indx_list.childNodes[1];
    }
    indexmenu_showPicker(picker,btn,show);
    // We use SACK to do the AJAX requests
    var ajax = new sack(DOKU_BASE+'lib/plugins/indexmenu/ajax.php');
    ajax.encodeURIString=false;
    ajax.onLoading = function () {
	indx_list.innerHTML='<div class="tocheader">Loading .....</div>';
    };
    
    // define callback
    ajax.onCompletion = function(){
        var data = this.response;
	indx_list.innerHTML="";
	if (notoc) {
	    if(data.substring(0,9) != 'indexmenu'){ indx_list.innerHTML="Retriving error";return; }
	    indexmenu_createThemes(data,indx_list);
	} else {
	    indx_list.innerHTML=data;
	}
    };
    
    ajax.runAJAX(encodeURI(get));
}

function indexmenu_createPicker(id,cl) {
    var indx_list = document.createElement('div');
    indx_list.className = cl || 'picker';
    indx_list.id=id;
    indx_list.style.position = 'absolute';
    indx_list.style.display  = 'none';
    var body = document.getElementsByTagName('body')[0];
    body.appendChild(indx_list);
    return indx_list;
}

function indexmenu_showPicker(pickerid,btn,show){
    var picker = $(pickerid);
    var x = 0;
    var y = 0;
    if (btn) {
	x = findPosX(btn);
	y = findPosY(btn);
    }
    if(picker.style.display == 'none' || show){
	picker.style.display = 'block';
	picker.style.left = (x+3)+'px';
	var offs = (btn.offsetHeight) ? btn.offsetHeight : 10;
	picker.style.top = (y+offs+3)+'px';
    }else{
	picker.style.display = 'none';
    }
}

function indexmenu_loadtoolbar (){
    var toolbar = $('tool__bar');
    if(!toolbar) return;
    indexmenu_loadJs(DOKU_BASE+'lib/plugins/indexmenu/edit.js');
}

function indexmenu_loadJs (f){
    var oLink = document.createElement("script");
    oLink.src = f;
    oLink.type = "text/javascript";
    oLink.charset="utf-8";
    document.getElementsByTagName("head")[0].appendChild(oLink);

}

function indexmenu_checkcontextm(n,obj,e){
  var k=0;
  e=e||event;
  if ((e.which == 3 || e.button == 2) || (window.opera && e.which == 1 && e.ctrlKey)) {
    obj.contextmenu (n,e);
    indexmenu_stopevt(e);
  }
}

function indexmenu_stopevt(e) {
    e=e||event;
    e.preventdefault? e.preventdefault() : e.returnValue = false;
    return false;
}
addInitEvent(indexmenu_loadtoolbar);
