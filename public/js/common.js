var scriptname="Orca Rpc Server Control Interface";
var scriptver="0.0.1 Alpha";




function GetSelectedRow(ModuleID) {
	var sm = ModuleID.getSelectionModel();
	var record = sm.getSelection()[0];
	return record;
}

function _getSelectedRowInGrid( gridID ) {
	var grid=Ext.getCmp(gridID);
	var s = grid.getSelectionModel().getSelection()[0];
	if ( !s ) {
		Ext.Msg.alert("Kayıt Seçmediniz!", "İşlemi yapabilmek için önce kayıt seçmeniz gerekmektedir.");
		return false;
	}
	return s.data;
}


function unixtime2date(val) {
	var date = Ext.Date.parse(val, 'U');
	return Ext.Date.format(date, 'd M Y H:i');
}

function loadBalance(serverID, panelID) {
	var balanceDiv=Ext.getCmp('balance_'+panelID);
	if ( !balanceDiv )
		return false;
	Ext.Ajax.request({
		url: 'api2client.php?action=getbalance&serverID='+serverID,
		success: function(response){
			var result = Ext.JSON.decode(response.responseText);
			if ( result.success ) {
				balanceDiv.update( "<h2>Balance: "+result.root+"</h2>");
			}
			else {
				balanceDiv.update( "<h2>Balance: #NAN</h2>");
			}
		}
	});
}



function Request2Api(args, store) {
	if ( (typeof(store) === 'undefined') || store == null )
		var myMask = new Ext.LoadMask(Ext.getBody(), {id:'IDLoadingMask', msg:"Please wait..."}).show();
	Ext.Ajax.request({
		url: 'client2api.php', method:'POST',
		params: args,
		success: function(response){
			var pMask=Ext.getCmp('IDLoadingMask');
			if ( pMask ) pMask.hide();
			var result= Ext.decode(response.responseText);
			if ( !result.success) Ext.Msg.alert(result.error.title, result.error.reason );
			else if ( store != undefined &&  store.getProxy().url != undefined )  store.load();
			else if ( store == undefined ) Ext.Msg.alert('DONE', 'Process completed.');
		},
		failure: function(response ) {
			var pMask=Ext.getCmp('IDLoadingMask');
			if ( pMask ) pMask.hide();
			if (response.timedout)
				Ext.Msg.alert('Timeout!', "A connection timeout occurred.");
			else if (response.aborted)
				Ext.Msg.alert('Aborted!', 'Transfer aborted by user.');
		}
	});
}

function ShowErrorMessage( request )  { //duzenle beni
	if ( typeof(request.responseText) !== undefined) {
			responseObj = Ext.decode(request.responseText,true);
			if ( !responseObj.success ) {
				Ext.Msg.alert(responseObj.error.title, responseObj.error.reason); 
			}
			else { Ext.Msg.alert('Hata','Sunucudan bu hata hakkinda bilgi alinamadi. Bos bir sonuç kümesi döndürülmüş olabilir!<br>Gerekçe:'+ResponseText); }
	}
	else if (response.timedout)
		Ext.Msg.alert('Zaman Aşımı', "Sorgulama zaman aşımına uğradı. Sunucu çok geç cevap veriyor yada internet bağlantınızda problem olabilir.");
	else if (response.aborted)
		Ext.Msg.alert('İletişim Durduruldu!', 'Sorgulama işlemi kullanıcı tarafından durduruldu!');
}

function HashSHA1(str) {
	var rotate_left = function (n, s) {
		var t4 = (n << s) | (n >>> (32 - s));
		return t4;
	};
	var cvt_hex = function (val) {
		var str = "";
		var i;
		var v;
		for (i = 7; i >= 0; i--) {
			v = (val >>> (i * 4)) & 0x0f;
			str += v.toString(16);
		}
		return str;
	};
	var blockstart;
	var i, j;
	var W = new Array(80);
	var H0 = 0x67452301;
	var H1 = 0xEFCDAB89;
	var H2 = 0x98BADCFE;
	var H3 = 0x10325476;
	var H4 = 0xC3D2E1F0;
	var A, B, C, D, E;
	var temp;
	//str = this.utf8_encode(str);
	var str_len = str.length;
	var word_array = [];
	for (i = 0; i < str_len - 3; i += 4) {
		j = str.charCodeAt(i) << 24 | str.charCodeAt(i + 1) << 16 | str.charCodeAt(i + 2) << 8 | str.charCodeAt(i + 3);
		word_array.push(j);
	}
	switch (str_len % 4) {
		case 0: i = 0x080000000; break;
		case 1: i = str.charCodeAt(str_len - 1) << 24 | 0x0800000; break;
		case 2: i = str.charCodeAt(str_len - 2) << 24 | str.charCodeAt(str_len - 1) << 16 | 0x08000; break;
		case 3: i = str.charCodeAt(str_len - 3) << 24 | str.charCodeAt(str_len - 2) << 16 | str.charCodeAt(str_len - 1) << 8 | 0x80; break;
	}
	word_array.push(i);
	while ((word_array.length % 16) != 14) {
		word_array.push(0);
	}
	word_array.push(str_len >>> 29);
	word_array.push((str_len << 3) & 0x0ffffffff);
	for (blockstart = 0; blockstart < word_array.length; blockstart += 16) {
		for (i = 0; i < 16; i++) {
			W[i] = word_array[blockstart + i];
		}
		for (i = 16; i <= 79; i++) {
			W[i] = rotate_left(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1);
		}
		A = H0;
		B = H1;
		C = H2;
		D = H3;
		E = H4;
		for (i = 0; i <= 19; i++) {
			temp = (rotate_left(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B, 30);
			B = A;
			A = temp;
		}
		for (i = 20; i <= 39; i++) {
			temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B, 30);
			B = A;
			A = temp;
		}
		for (i = 40; i <= 59; i++) {
			temp = (rotate_left(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B, 30);
			B = A;
			A = temp;
		}
		for (i = 60; i <= 79; i++) {
			temp = (rotate_left(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff;
			E = D;
			D = C;
			C = rotate_left(B, 30);
			B = A;
			A = temp;
		}
		H0 = (H0 + A) & 0x0ffffffff;
		H1 = (H1 + B) & 0x0ffffffff;
		H2 = (H2 + C) & 0x0ffffffff;
		H3 = (H3 + D) & 0x0ffffffff;
		H4 = (H4 + E) & 0x0ffffffff;
	}
	temp = cvt_hex(H0) + cvt_hex(H1) + cvt_hex(H2) + cvt_hex(H3) + cvt_hex(H4);
	return temp.toLowerCase();
}
