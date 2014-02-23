Ext.Loader.setConfig({enabled: true});
Ext.Loader.setPath('Ext.ux', '../js/ext4/ux');

Ext.require([
    'Ext.window.MessageBox',
    'Ext.tip.*',
	'Ext.window.Window',
	'Ext.tab.*',
    'Ext.window.*',
    'Ext.layout.container.Border',
    'Ext.grid.*',
    'Ext.ux.grid.FiltersFeature',
    'Ext.chart.*'
]);

Ext.onReady(function() {
	Ext.QuickTips.init();
	Ext.state.Manager.setProvider(Ext.create('Ext.state.CookieProvider'));
	Ext.override(Ext.data.proxy.Ajax, { timeout:7200000 });
	function SetProxy(store, dataUrl) {
		store.setProxy({
			type: 'ajax', url: dataUrl, reader: { type: 'json', root: 'root', single:true },
			listeners: {
				exception: function (proxy, request, operation) {
					if (request.responseText != undefined) {
						responseObj = Ext.decode(request.responseText,true);
						if (responseObj != null && responseObj.error != undefined) {
							Ext.Msg.alert(responseObj.error.title, responseObj.error.reason );
						}
						else {
							Ext.Msg.alert('Error','unable load record...');
						}
					}
					else {
						Ext.Msg.alert('Error', 'Transfer failed.');
					}
				}
			} 
		})
	}
	
	function showDonateWindow() {
		var pwin=Ext.create('Ext.window.Window', {
			id: 'WindowLogin', modal: true,
			closable: true,
			closeAction: 'destroy',
			title: 'Donate', layout: 'fit',
			resizable: false,
			items:[
				{ xtype : 'form', defaultType : 'field', frame: true,
					bodyPadding: 5, width: 400, 
					layout: 'anchor', defaults: { anchor: '100%'}, defaultType: 'textfield',
					items: [
						{ fieldLabel: 'BitCoin', value:'1BrfpNZfaPhmcYR4jyFZihKEeWtFrZyzft'},
						{ fieldLabel: 'LiteCoin', value: 'LdPbdvj6niFHZcdMDnBXkMDP9PzY4KZDZ6'},
						{ fieldLabel: 'VertCoin', value: 'VezKQTE19txue7RL6xJAvwaxzPzf1FwXEV'},
						
					],
					buttons: []
				}
			]
		}).show();
	}
	
	
	function showAccountAddress(servID, accountname ) {
		var tUniqID=servID+accountname;
		var addrstore=CreateStore( 'StoreAddressList'+tUniqID,
			'account_address_t',
			'api2client.php?action=getaddressesbyaccount&serverID='+ servID+"&account="+accountname,
			'bbarAccountAddressList'+tUniqID 
		);
		addrstore.load();
		var pwin = new Ext.create('Ext.window.Window', {
			id: 'WindowShowAccountAddress'+tUniqID,
			modal: true,
			closable: true, frame: true,
			closeAction: 'destroy',
			title: 'Account Address List for '+accountname,
			layout: 'fit', resizable: true,
			items:[
				{
					xtype: 'gridpanel', 
					width:400,
					minHeight: 200,
					enableTextSelection: true,
					id:'GridAddressList'+tUniqID,
					store: addrstore,
					columns: [
						{ text: 'ACCOUNT', flex: 1, sortable : true, dataIndex: 'address' }
					],
					bbar: [
						{html:'Status:', xtype:'label', style:'font-weight:bold;'},
						{id:'bbarAccountAddressList'+tUniqID, xtype:'label', html: '&nbsp;&nbsp;<img src="images/loading-balls.gif" alt="Loading..." />'}
					],
					plugins:[
						Ext.create('Ext.grid.plugin.RowEditing', { 
							 clicksToEdit: 1,
							 listeners: {
								 edit: function (editor,e) {
									 Ext.Msg.alert('Error', 'You can\'t change the address on your account.');
								}
							}
						})
					],
				}
			
			]
		}).show();
	}
	
	
	
	function WalletBackup() {
		var pwin = new Ext.create('Ext.window.Window', {
			id: 'WindowWalletBackup',
			modal: true,
			closable: true, 
			closeAction: 'destroy',
			title: 'Backup Results',
			layout: 'fit',
			width: 600,
			height:200,
			resizable: false,
			items:[
				Ext.create('Ext.grid.Panel', {
					split:true, closable:false, stateful: false, frame: true,
					store: Ext.create('Ext.data.Store', { model: 'rpc_server_backup_info_t', autoLoad: true, single: true}),
					columns: [
						{ text: 'TITLE', dataIndex: 'title', flex:1 },
						{ text: 'SERVER', dataIndex: 'server', flex:1 },
						{ text: 'BACKUP FILENAME', dataIndex: 'backupfilepath', flex:1},
						{ text: 'STATUS', dataIndex: 'status', flex:1 }
					]
				})
			]
		}).show();
	}
	
	
	
	
	function FormNewRpcServer() {
		var pwin = new Ext.create('Ext.window.Window', {
			id: 'WindowNewRpcServer',
			modal: true, closable: true,
			resizable: false, closeAction: 'destroy', frame: true,
			title: 'Add Rpc Server',
			items:[
			Ext.widget({
				xtype: 'form', id: 'TabNewRpcServer', width: 450, border: true,
				bodyPadding: 5, url: "client2api.php",
				bodyBorder: false,
				fieldDefaults: { labelWidth: 100, msgTarget: 'side'},
				items: {
					xtype:'tabpanel', activeTab: 0, 
					defaults:{ bodyPadding: 10, layout: 'anchor' },
					items:[
						{ title:'Server Configuration', defaultType: 'textfield', defaults: { anchor: '100%' },
							items: [
								{ xtype:'hidden', name: 'action', value:'AddRpcServer', allowBlank: false, hidden:true},
								{ fieldLabel: 'Host', name: 'rpc_host', allowBlank:false},
								{ fieldLabel: 'Port', name: 'rpc_port', allowBlank:false, xtype: 'numberfield', minValue: 1, maxValue: 65535},
								{ fieldLabel: 'Username', name: 'rpc_user', allowBlank:false},
								{ fieldLabel: 'Password', name: 'rpc_pass', allowBlank:false},
								{ xtype: 'checkboxfield', name: 'rpc_verify', label: 'Verify',  boxLabel: 'verify', value:1, inputValue:1, checked: true }
							]
						},
						{ title:'SSL Settings', defaultType: 'textfield', defaults: { anchor: '100%' },
							items: [
								{ xtype: 'checkboxfield', name: 'rpc_ssl_enabled', label: 'use SSL',  boxLabel: 'use SSL', value:1, inputValue:1, checked: false },
								{
									xtype: 'combobox',
									id: 'rpc_ssl_options',
									name : 'rpc_ssl_options[]',
									fieldLabel: 'SSL options',
									displayField: 'key',
									queryMode: 'remote',
									valueField: 'value',
									delimiter: ' AND ',
									store: new Ext.data.ArrayStore(
										{ fields: ['key', 'value'],
											data : [['VERIFY_RPC_SERVER', 4],['VERIFY_LOCAL', 8] ]
										}
									),
									multiSelect: true,
									editable: false, allowBlank: true,
									inputAttrTpl: " data-qtip='Multi select enabled<br />You can set host and peer verification.' ",
									listeners: {
										change: function(combo, eOpts){
											var cainfoPath=Ext.getCmp('rpc_peer_cainfo_path');
											if ( combo.getValue().indexOf(8) != -1 ) {
												cainfoPath.setDisabled(false);
											}
											else {
												cainfoPath.setDisabled(true);
											 }
										}
									}
									
									
									
									
								},
								{ fieldLabel: 'cert(*.pem) path', id: 'rpc_peer_cainfo_path', name: 'rpc_peer_cainfo_path', disabled:true, allowBlank:false},
							]
						},
						{ title:'Other Settings', defaultType: 'textfield', defaults: { anchor: '100%' },
							items: [
								{
									xtype: 'combobox',
									name : 'rpc_icon',
									fieldLabel: 'Icon',
									displayField: 'value',
									queryMode: 'remote',
									valueField: 'key',
									store: Ext.create('Ext.data.Store', {  model: 'icons_t', autoLoad: true, single: true}),
									editable: false, allowBlank: true
								},
								{ fieldLabel: 'Title', name: 'rpc_title', value:'Untitled', allowBlank:true},
								{ fieldLabel: 'Backup Path', name: 'rpc_backup_path', value:'', allowBlank:true},
							]
						}
					]
				},
				buttons: [ 
					{text: 'Save', formBind: true,  disabled: true,
						handler: function() {
							var form = this.up('form').getForm();
							if (form.isValid()) {
								form.submit({
									success: function(form, action) {
										var result = Ext.JSON.decode(action.response.responseText);
										if ( result.success ) {
											Ext.getCmp('GridRpcServers').store.load();
											Ext.getCmp('WindowNewRpcServer').destroy();
										}
										else {
											Ext.Msg.alert(action.result.error.title, action.result.error.reason);
										}
									},
									failure: function(form, action) {
										if (action.failureType === Ext.form.Action.CONNECT_FAILURE)
											Ext.Msg.alert('Failure', 'Server reported:'+action.response.status+' '+action.response.statusText);
										else if (action.failureType === Ext.form.Action.SERVER_INVALID)
											Ext.Msg.alert(action.result.error.title, action.result.error.reason);
									}
								});
							}
						}
					}
				]
			})
		]
		}).show();
	}
	
	
	
	function ShowAlertMsg( msg_title, msg_text ) {
		Ext.Msg.alert(msg_title, msg_text);
	}
	
	function TransactionDetails(server, txID) {
		var tstore=Ext.create('Ext.data.Store', {  model: 'transaction_details_t', autoLoad: true, single: true});
		tstore.getProxy().url = 'api2client.php?action=gettransaction&serverID='+ server.id+"&txID="+txID;
		
		var pwin = new Ext.create('Ext.window.Window', {
			id: 'WindowTransaction'+txID, 
			modal: true,
			closable: true, closeAction: 'destroy',
			title: 'Transaction Details',
			layout: 'fit', resizable: false,
			width:800,
			height: 400,
			items:[
				Ext.create('Ext.form.Panel', {
					id: 'TransactionDetails',
					//title: 'Transaction Details',
					frame: true,
					bodyPadding: 5,
					width: 750,
					layout: 'column',
					fieldDefaults: { labelAlign: 'left', msgTarget: 'side'},
					items: [
						{
							columnWidth: 0.60, xtype: 'gridpanel', height: 400,
							id:'GridTransactionDetails',
							store: tstore,
							columns: [
								{ text: 'ACCOUNT', flex: 1, sortable : true, dataIndex: 'account' },
								{ text: 'ADDRESS', width:75, sortable : true, dataIndex: 'address' },
								{ text: 'CATEGORY', width:75, sortable : true, dataIndex: 'category' },
								{ text: 'AMOUNT', width:75, sortable : true, dataIndex: 'amount' },
								{ text: 'FEE', width: 85, sortable : true, dataIndex: 'fee' }
							]
						},
						{
							columnWidth: 0.40,
							margin: '0 0 0 10',
							xtype: 'fieldset',
							title:'Transaction details',
							defaults: { labelWidth: 90 },
							defaultType: 'textfield',
							items: [
								{ fieldLabel: 'Amount', id:'TransactionAmount', name: 'TransactionAmount' },
								{ fieldLabel: 'Confirmations', id:'TransactionConfirmations', name: 'TransactionConfirmations' },
								{ fieldLabel: 'Date',  id: 'TransactionTime', name:'TransactionTime' },
								{ fieldLabel: 'TXID', xtype:'textareafield', id:'TransactionID', name: 'TransactionID' }
							]
						}
					]
				})
			]
		}).show();
		tstore.addListener('load', function(records, operation, success) {
			if( success ) {
				Ext.getCmp('TransactionAmount').setValue(tstore.getProxy().getReader().rawData.troot.amount);
				Ext.getCmp('TransactionConfirmations').setValue(tstore.getProxy().getReader().rawData.troot.confirmations);
				Ext.getCmp('TransactionID').setValue(tstore.getProxy().getReader().rawData.troot.txid);
				Ext.getCmp('TransactionTime').setValue(unixtime2date(tstore.getProxy().getReader().rawData.troot.time));
			}
		});
	}
	
	



    var PanelWest=Ext.create('Ext.grid.Panel', {
		id: 'GridRpcServers', stateId:'GridRpcServers', title: 'Rpc Servers',
		iconCls: 'icon-rpcserver', 
		split:true, closable:false, stateful: false, frame: true,
		region: 'west', split: true, frame: true,
        width: 200,
        minWidth: 175,
		store: Ext.create('Ext.data.Store', {
			model: 'rpc_server_t', autoLoad: true, single: true,
			listeners: {
				'load': function(store, records, successful) {
					var TextMsg= ( successful ) ?store.getProxy().getReader().rawData.message: 'Error...';
					var bbar=Ext.getCmp('BBarGridRpcServers');
					if ( bbar  ) bbar.setText(TextMsg);
				}
			}
		}),
		columns: [
			{ text: 'ID', dataIndex: 'id', hidden:true },
			{ text: 'HOST', dataIndex: 'host', hidden:true },
			{ text: 'PORT', dataIndex: 'port', hidden:true },
			{ text: 'USER', dataIndex: 'username', hidden:true },
			{ text: 'PASS', dataIndex: 'password', hidden:true },
			{ text: 'CREATED', dataIndex: 'created', hidden:true },
			{ text: 'SSL', dataIndex: 'useSSL', width:30  },
			{ text: 'TITLE', dataIndex: 'title', flex:1 },
			{ text: 'ICON', dataIndex: 'icon', hidden:true },
			{ text: 'CERT PATH', dataIndex: 'ca_path', hidden:true },
			{ text: 'BACKUP PATH', dataIndex: 'backup_path', hidden:true }
		],
		dockedItems: [
			{ xtype: 'toolbar', dock: 'top', 
				items: [
					{ iconCls: 'icon-add', text: 'Add New', handler: function() { FormNewRpcServer(); } },
					{ iconCls: 'icon-delete', text: 'Delete', handler: function() { 
						var record=_getSelectedRowInGrid( 'GridRpcServers' );
						if ( !record ) {
							Ext.MessageBox.alert('Error', 'No record selected');
							return false;
						}
						Ext.MessageBox.confirm('Confirm', "Are you sure you want to delete this rpc server?<br /><b>"+record.title+"<br />"+record.host+":"+record.port+"</b>",
						function (id, value) {
							if (id === 'yes') {
								var opt={ action: "TerminateRpcServerByID", ID:record.id};
								Request2Api(opt, Ext.getCmp('GridRpcServers').store );
							}
						}, this);
					}}
				]
			}
		],
		tools: [
			{ xtype: 'tool', type: 'refresh', tooltip: 'Refresh rpc server list', handler: function() { PanelWest.store.load(); }}
		],
		bbar: [
			{html:'Status:', xtype:'label', style:'font-weight:bold;'},
			{id:'BBarGridRpcServers', xtype:'label', html: '&nbsp;&nbsp;<img src="images/loading-balls.gif" alt="Loading..." />'}
		],
		listeners : {
			itemdblclick: function(dv, record, item, index, e) {
				CreateTab(record.data,"tmp");
			}
		}
	});
	var PanelEast={
		xtype: 'gridpanel', region: 'east',
		title: 'Brute History',
		id:'GridBruteForceHistory',
		animCollapse: false, collapsible: true, split: true, collapsed: true,
		width: 225, minSize: 175, maxSize: 400,
		margins: '0 5 0 0',
		store: Ext.create('Ext.data.Store', {  model: 'brutes_t', autoLoad: true, single: true}),
		columns: [
			{ text: 'ID',  dataIndex: 'id', width:30, },
			{ text: 'DATE', dataIndex: 'created', flex: .7 },
			{ text: 'ip', dataIndex: 'ip', flex: .5 },
			{ text: 'Login Data', dataIndex: 'logindata', flex:1 }
		],
		dockedItems: [
			{ xtype: 'toolbar', dock: 'top',  
				items: [
					{ iconCls: 'icon-flush', text: 'Flush Brutes', handler: function() {
						Ext.MessageBox.confirm('Confirm', "Are you sure you want to delete all records?",
						function (id, value) {
							if (id === 'yes') {
								var opt={ action: "FlushBrutes"};
								Request2Api(opt, Ext.getCmp('GridBruteForceHistory').store );
							}
						}, this);
					}}
				]
			}
		],
		tools: [
			{ type: 'refresh', tooltip: 'Refresh List', handler: function(){ Ext.getCmp('GridBruteForceHistory').store.load(); } }
		],
		listeners: {
			expand: function () {
				Ext.getCmp('GridBruteForceHistory').store.load();
			}
		}
	};
	
	
	
	
	
	var PanelSouth=Ext.create('Ext.grid.Panel', { 
		region: 'south',  split:true, closable:false, collapsible:true, collapsed: true, stateful: false, frame: true, 
		id:"PanelLogs", stateId: 'PanelLogs', height: 300, minSize: 100, 
		animCollapse: false,
		collapsible: true, title: 'LOGS',
		store: Ext.create('Ext.data.Store', { model: 'log_t' }),
		tools: [{ type: 'refresh', handler: function() { Ext.getCmp('PanelLogs').store.load(); } }],
		features: [  { ftype: 'filters', local: true, encode: false, } ],
		dockedItems: [
			{ xtype: 'toolbar', dock: 'top',  
				items: [
					{ iconCls: 'icon-flush', text: 'Flush Logs', handler: function() {
						Ext.MessageBox.confirm('Confirm', "Are you sure you want to delete all records?",
						function (id, value) {
							if (id === 'yes') {
								var opt={ action: "FlushLogs"};
								Request2Api(opt, Ext.getCmp('PanelLogs').store );
							}
						}, this);
					}}
				]
			}
		],
		columns: [
			{ text: 'Id', dataIndex: 'id' },
			{ text: 'DATE', dataIndex: 'created', flex:1, filterable: true, width: 30 },
			{ text: 'NODE', dataIndex: 'node', flex:1,  filterable: true, width: 30 },
			{ text: 'MESSAGE', dataIndex: 'message'},
			{ text: 'EXTRAINFO', dataIndex: 'extrainfo'},
			{ text: 'STATUS', dataIndex: 'status', flex:1, filterable: true, width: 30 }
		],
		listeners: {
			expand: function () {
				Ext.getCmp('PanelLogs').store.load();
			}
		},
		
	});
	
	
	function CreateStoreForWallet(storeID, pmodel, storeuri, bbarID, panelID ) {
		var pstore= Ext.create('Ext.data.Store', { 
			storeId: storeID,
			model: pmodel,
			autoLoad: false,
			single: true,
			listeners: {
				'load': function(store, records, successful) {
					var TextMsg='Data transfer failure';
					if ( successful ) {
						TextMsg=store.getProxy().getReader().rawData.message;
						var record = store.findRecord('key', 'balance');
						if ( record ) {
							Ext.getCmp('balance_'+panelID).update( "<h2>Balance: "+record.data.value+"</h2>");
						}
					}
					Ext.getCmp(bbarID).setText(TextMsg);
				}
			}
		});
		SetProxy(pstore, storeuri);
		return pstore;
	}
	function CreateStore(storeID, pmodel, storeuri, bbarID) {
		var pstore= Ext.create('Ext.data.Store', { 
			storeId: storeID,
			model: pmodel,
			autoLoad: false,
			single: true,
			listeners: {
				'load': function(store, records, successful) {
					var TextMsg='Data transfer failure';
					var bbar=Ext.getCmp(bbarID);
					if ( successful )
						TextMsg=store.getProxy().getReader().rawData.message;
					if ( bbar  ) bbar.setText(TextMsg);
				}
			}
		});
		SetProxy(pstore, storeuri);
		return pstore;
	}
	
	
	function CreateTab(data, PanelTitle) {
		var _panelTitle=data.title+' ('+data.username+'@'+data.host+':'+data.port+')';
		var _panelID=data.id;
		
		
		
		if ( Ext.getCmp(_panelID) ) {
			Ext.getCmp('TabPanelCenter').setActiveTab(_panelID );
			return false;
		}
		
		var newTab=Ext.create('Ext.tab.Panel', {
			title: _panelTitle,
			id: _panelID,
			frame: true,
			split: true,
			region: 'center',  autoHeight: true,
			closable: true,
			items: [
				/*<overview>*/
				Ext.create('Ext.container.Container', {
					layout: { type: 'vbox', align: 'stretch' },
					title: "Overview", iconCls: 'icon-overview16',
					listeners: {
						activate: function() {
							Ext.getCmp('ACCOUNTS'+_panelID).store.load();
							Ext.getCmp('WALLETNFO'+_panelID).store.load();
							Ext.getCmp('ChartAccount'+_panelID).bindStore(Ext.data.StoreManager.lookup('StoreAccountList'+data.id));
						}
					},
					 
					items: [
						{ xtype: 'container',
							layout: {  type: 'hbox', align: 'stretch' },
							items: [
								{ pack: 'end', xtype:'container', id: 'icon_balance_'+_panelID, 
										html:'<img src="images/icons/cryptocurrency/'+data.icon+'" />', margins: 8 
									},
									{ pack: 'end', xtype:'container', id: 'balance_'+_panelID, html:'<h2>Balance: #NAN</h2>', margins: 8 },
					
							]
						},
						{
							xtype: 'container', flex: 1,
							layout: {  type: 'hbox', align: 'stretch' },
							items: [
								Ext.create('Ext.grid.Panel', {
									title: 'Wallet Info',
									id: 'WALLETNFO'+_panelID,
									iconCls: 'icon-walletinfo16', 
									tools: [
										{ itemId: 'refresh', type: 'refresh', tooltip: 'Refresh Wallet information', handler: function(){
											var s=Ext.getCmp('WALLETNFO'+_panelID);
											if ( s )
												s.store.load();
											}
										}
									],
									store: CreateStoreForWallet(
										'StoreWallet'+data.id,
										'wallet_info_t',
										'api2client.php?action=getinfo&serverID='+ data.id,
										'bbarWalletInfo'+_panelID,
										_panelID
									),
									bbar: [
										{html:'Status:', xtype:'label', style:'font-weight:bold;'},
										{id:'bbarWalletInfo'+_panelID, xtype:'label', html: '&nbsp;&nbsp;<img src="images/loading-balls.gif" alt="Sorgulan?yor..." />'}
									],
									margins:8,
									hideHeaders: true,
									columns: [
										{ text: 'Key',  dataIndex: 'key', flex: 1 },
										{ text: 'value', dataIndex: 'value', flex: 1 }
									],
									height: 400,
									width: '20%'
								}),
								Ext.create('Ext.grid.Panel', {
									title: 'Accounts',
									id: 'ACCOUNTS'+_panelID,
									iconCls: 'icon-accounts16',
									dockedItems: [
										{ xtype: 'toolbar', dock: 'top',  
											items: [
												{ iconCls: 'icon-add', text: 'New Address', handler: function() {
													Ext.MessageBox.prompt('Label', 'Label for new address', function(btn, text){
														if (btn == 'ok'){
															if ( text == "" ) {
																Ext.Msg.alert('Error', 'Label Cannot be empty!');
																return false;
															}
															var opt={ action: "getnewaddress", serverID: data.id, label: text };
															Request2Api(opt, Ext.getCmp('ACCOUNTS'+_panelID).store );
														}
													});
												}}
												
											]
										}
									],
									tools: [
										{
											type: 'plus', tooltip: 'Create new address', handler: function(){
												Ext.MessageBox.prompt('Label', 'Label for new address', function(btn, text){
													if (btn == 'ok'){
														var opt={ action: "getnewaddress", serverID: data.id, label: text };
														Request2Api(opt, Ext.getCmp('ACCOUNTS'+_panelID).store );
													}
												});
											}
										},
										{ type: 'refresh', tooltip: 'Refresh Account List', handler: function(){
											var s=Ext.getCmp('ACCOUNTS'+_panelID);
											if ( s )
												s.store.load();
											}
										}
									],
									margins: 8,
									store: CreateStore(
										'StoreAccountList'+data.id,
										'account_info_t',
										'api2client.php?action=listaccounts&serverID='+ data.id,
										'bbarAccountList'+_panelID
									),
									columns: [
										{ text: 'Account',  dataIndex: 'account' },
										{ text: 'Balance', dataIndex: 'balance', flex: 1 }
									],
									bbar: [
										{html:'Status:', xtype:'label', style:'font-weight:bold;'},
										{id:'bbarAccountList'+_panelID, xtype:'label', html: '&nbsp;&nbsp;<img src="images/loading-balls.gif" alt="Sorgulan?yor..." />'}
									],
									width: '30%',
									listeners : {
										itemdblclick: function(dv, record, item, index, e) {
											showAccountAddress(data.id, record.data.account);
										}
									}
								}),
								Ext.create('widget.panel', {
									title: 'Balance Graph',
									width: '50%',
									layout: 'fit',
									iconCls: 'icon-money16', 
									margins: 8,
									items:[
										Ext.create('Ext.chart.Chart', {
											animate: true, shadow: true,
											layout: 'fit',
											id: 'ChartAccount'+_panelID,
											store: Ext.create('Ext.data.Store', {  model: 'account_info_t', autoLoad: false, single: true}),
											axes: [
											{ type: 'Numeric', position: 'left', fields: ['balance'], grid: true },
											{ type: 'Category', position: 'bottom', fields: ['account'], label: {
												rotate: { degrees: 270 } } }
											],
											series: [
												{ 
													type: 'column', axis: 'left', gutter: 80, xField: 'account', yField: 'balance',
													highlight: true,
													label: {
														display: 'insideEnd',
														field: 'balance',
														orientation: 'vertical',
														color: '#FFFFFF', 
														'text-anchor': 'middle'
													},
													tips: {
														trackMouse: true,
														width: 250, height: 28,
														renderer: function(storeItem, item) {
															this.setTitle(storeItem.get('account') + ': #' + storeItem.get('balance') );
														}
													},
													renderer: function(sprite, record, attr, index, store) {
														var value =  Math.abs(Math.floor((record.data.balance%11)));
														var color = [
															'rgb(114, 19, 15)',  'rgb(92, 48, 71)',  'rgb(81, 114, 179)', 
															'rgb(196, 185, 183)', 'rgb(13, 111, 62)', 'rgb(158, 129, 133)',
															'rgb(0,0,255)', 'rgb(121,121,127)', 'rgb(30,30,0)', 'rgb(127,39,39)',
															'rgb(50, 107, 126)'][value];
														return Ext.apply(attr, {
															fill: color
														});
													}
												}
											]
										})
									]
								})
							]
						}
						
					]
				})
				/*</overview>*/
				,

			
				
				/*<send>*/
				
				Ext.create('Ext.container.Container', {
					layout: { type: 'vbox', align: 'stretch' },
					title: "Send/Multi Send", iconCls: 'icon-send16',
					listeners: {
						activate: function() {
							var cmb= Ext.getCmp("FormSend"+_panelID).getForm().findField("sendTo");
							if ( cmb )
								cmb.bindStore(Ext.data.StoreManager.lookup('StoreAddressBook'+data.id));
							/*move source*/
							cmb= Ext.getCmp('FormMove'+_panelID).getForm().findField('MoveSourceAccount');
							if ( cmb )
								cmb.bindStore(Ext.data.StoreManager.lookup('StoreAccountList'+data.id));
							cmb= Ext.getCmp('FormMove'+_panelID).getForm().findField('MoveDestAccount');
							if ( cmb )
								cmb.bindStore(Ext.data.StoreManager.lookup('StoreAccountList'+data.id));
							
							/*sendmore*/
							cmb= Ext.getCmp('FormSendMore'+_panelID).getForm().findField('sendManyAccount');
							if ( cmb )
								cmb.bindStore(Ext.data.StoreManager.lookup('StoreAccountList'+data.id));
							
							
							
							
						
						}
					},
					items: [
						{
							xtype: 'container', flex: 1,
							layout: {  type: 'hbox', align: 'stretch' },
							items: [
								Ext.create('Ext.form.Panel', {
									title: 'Send', iconCls: 'icon-send16',
									id: 'FormSend'+_panelID,
									layout: 'anchor',
									margin: 8,
									bodyPadding: 5, flex:1,
									url: 'client2api.php',
									defaults: { anchor: '100%', margin: "10 0 0 0"}, defaultType: 'textfield',
									items: [
										{ xtype : 'hidden', name: 'action', value: 'sendtoaddress' },
										{ xtype : 'hidden', name: 'serverID', value: data.id },
										{
											xtype: 'combobox',
											name : 'sendTo',
											fieldLabel: 'To',
											displayField: 'address',
											queryMode: 'remote',
											valueField: 'address',
											store: null,
											tpl: '<tpl for="."><div class="x-boundlist-item" >{account},  <span style="color:#CCCCCC">{address}</span></div></tpl>',
											
											editable: true,
											allowBlank: false
										},
										{
											xtype: 'numberfield',
											name : 'sendAmount',
											fieldLabel: 'Amount',
											decimalPrecision:8,
											allowBlank: false,
											minValue: 0
										},
										
										
									],
									buttons: [
										{ text: 'Send',  formBind: true, disabled: true,handler: function() {
											var form = this.up('form').getForm();
											if (form.isValid()) {
												var objSend={
													to:form.findField('sendTo').getValue(),
													amount: form.findField('sendAmount').getValue()
												};
												Ext.MessageBox.confirm('Confirm', "Are you sure you want to send?<br />To:"+objSend.to+"<br />Amount: "+objSend.amount,
													function (id, value) {
														if (id === 'yes'){
															form.submit({
																success: function(form, action) {
																	Ext.Msg.alert('Success', 'Transaction completed!');
																	form.reset();
																},
																failure: function(form, action) {
																	Ext.Msg.alert(action.result.error.title, action.result.error.reason);
																}
															});
														}
													}
												);
											}
										}}
									]
								}),
								Ext.create('Ext.form.Panel', {
									title: 'Move', iconCls: 'icon-send16',
									id: 'FormMove'+_panelID,
									layout: 'anchor',
									url: 'client2api.php',
									margin: 8,
									bodyPadding: 5, flex:1,
									defaults: { anchor: '100%', margin: "10 0 0 0"}, defaultType: 'textfield',
									items: [
										{ xtype : 'hidden', name: 'action', value: 'move' },
										{ xtype : 'hidden', name: 'serverID', value: data.id },
										{
											xtype: 'combobox',
											name : 'MoveSourceAccount',
											fieldLabel: 'Source',
											queryMode: 'local',
											valueField: 'account',
											displayField: 'account',
											store: null,
											editable: false,
											allowBlank: false
										},
										{
											xtype: 'combobox',
											name : 'MoveDestAccount',
											fieldLabel: 'Destination',
											displayField: 'account',
											queryMode: 'local',
											valueField: 'account',
											store: null,
											editable: false,
											allowBlank: false
										},
										{
											xtype: 'numberfield',
											name : 'MoveAmount',
											fieldLabel: 'Amount',
											allowBlank: false,
											decimalPrecision:8
										}
									],
									buttons: [
										{ text: 'Move', formBind: true, disabled: true, handler: function() {
											var form = this.up('form').getForm();
											if (form.isValid()) {
												var objMove={
													source:form.findField('MoveSourceAccount').getValue(),
													dest: form.findField('MoveDestAccount').getValue(),
													amount: form.findField('MoveAmount').getValue()
												};
												Ext.MessageBox.confirm('Confirm', "Are you sure you want to move balance?<br />Source:"+objMove.source+"<br />Destination:"+objMove.dest+"<br />Amount: "+objMove.amount,
													function (id, value) {
														if (id === 'yes') {
															form.submit({
																success: function(form, action) {
																	Ext.Msg.alert('Success', 'Moved successfully!');
																	form.reset();
																},
																failure: function(form, action) {
																	Ext.Msg.alert(action.result.error.title, action.result.error.reason);
																}
															});
														}
													}
												);
											}
										} 
									}]
								})
							]
						},
						{
							xtype: 'container', flex: 1,
							layout: {  type: 'hbox', align: 'stretch' },
							items: [
								Ext.create('Ext.form.Panel', {
									id: 'FormSendMore'+_panelID,
									title: 'Send More', iconCls: 'icon-send16',
									layout: 'anchor', margins: '8 2 8 0',
									url:'client2api.php', bodyPadding: 5, flex:1,
									defaults: { anchor: '100%', margin: "10 0 0 0"}, defaultType: 'textfield',
									items: [
										{ xtype : 'hidden', name: 'action', value: 'sendmany' },
										{ xtype : 'hidden', name: 'serverID', value: data.id },
										{ xtype: 'combobox', name : 'sendManyAccount',
											fieldLabel: 'From Account', queryMode: 'local',
											displayField: 'account', valueField: 'account',
											editable: false, allowBlank: false,
											store: null
										},
										{ fieldLabel: 'Address List', name: 'sendManyList', allowBlank: false, xtype: 'textareafield' },
										{ xtype: 'displayfield', fieldLabel: 'Usage', value:'address1:amount1,address2:amount2,...'}
									],
									buttons: [
										{ text: 'Multiple Send', formBind: true, disabled: true, handler: function() {
											var form = this.up('form').getForm();
											if (form.isValid()) {
												var totalamount=0;
												var objMultiSend=form.findField('sendManyList').getValue();
												var sendList=objMultiSend.split(",");
												for(i = 0; i < sendList.length; i++){
													var tmp= sendList[i];
													var tmp2= tmp.split(":");
													if ( tmp2.length != 2 ) {
														Ext.MessageBox.alert('Error', "Your list invalid, please make sure your list is valid");
														return false;
													}
													totalamount+= parseFloat(tmp2[1]);
												}
												Ext.MessageBox.confirm('Confirm', "Are you sure you want to send use sendmany function for<br />Total Amount:<b>"+totalamount+"</b>",
													function (id, value) {
														if (id === 'yes') {
															form.submit({
																success: function(form, action) {
																	Ext.Msg.alert('Success', 'Transaction completed!');
																	form.reset();
																},
																failure: function(form, action) {
																	console.dir(action);
																	Ext.Msg.alert(action.result.error.title, action.result.error.reason);
																}
															});
														}
													}
												);
											}
										}}
									]
								})
							]
						}
					]
				})
				
				
				/*</send>*/,
				
				
				Ext.create('Ext.grid.Panel', {
					id: "Transactions"+_panelID, title: "Transactions",
					flex: 1, iconCls: 'icon-transaction16', region: 'center', layout: 'fit',
					split:true, closable:false, stateful: false, frame: false, multiSelect: false,
					store:  CreateStore(
							'StoreTransactionList'+data.id,
							'transactionlist_t',
							'api2client.php?action=transactionlist&serverID='+ data.id,
							'bbarTransactionList'+_panelID
					),
					features: [  { ftype: 'filters', local: true, encode: false } ],
					columns: [
						{ text: 'CONFIRM', dataIndex: 'confirmations', filterable: true, width: 30, filter: { type: 'numeric' }},
						{ text: 'TIME RECEIVED', dataIndex: 'timereceived', flex:1, format: "d-M-Y",
							filter: { type: 'date' }
						},
						{text: 'TYPE', dataIndex: 'category', flex:1, filterable: true, width: 30},
						{text: 'ACCOUNT', dataIndex: 'account', flex:1, filterable: true, width: 30 },
						{text: 'ADDRESS', dataIndex: 'address', flex:1, filterable: true, width: 30},
						{text: 'AMOUNT', dataIndex: 'amount',  flex:1, filterable: true, width: 30, filter: { type: 'numeric' }},
						{text: 'BLOCK HASH', dataIndex: 'blockhash', hidden:true},
						{text: 'BLOCK INDEX', dataIndex: 'blockindex', hidden:true},
						{text: 'BLOCK TIME', dataIndex: 'blocktime', hidden:true},
						{text: 'TXID', dataIndex: 'txid', hidden:true},
						{text: 'TIME', dataIndex: 'time', hidden:true}
					],
					dockedItems: [
						{ xtype: 'toolbar', dock: 'top', 
							items: [
								{iconCls: 'icon-refresh', text: 'Refresh', disabled: false, 
									handler: function(){ Ext.getCmp('Transactions'+_panelID).store.load(); }
								}
							]
						}
					],
					listeners : {
						itemdblclick: function(dv, record, item, index, e) {
							if ( record.data.txid.length < 8 ) {
								Ext.MessageBox.alert('Invalid Transaction', 'Local transfers has no transaction details.');
								return false;
							}
							else {
								TransactionDetails(data, record.data.txid);
							}
						},
						activate: function() {
							Ext.getCmp('Transactions'+_panelID).store.load();
						}
					},
					bbar: [
						{html:'Status:', xtype:'label', style:'font-weight:bold;'},
						{id:'bbarTransactionList'+_panelID, xtype:'label', html: '&nbsp;&nbsp;<img src="images/loading-balls.gif" alt="Sorgulan?yor..." />'}
					]
				}),
				
				Ext.create('Ext.grid.Panel', {
					id: "AddressBook"+_panelID,
					title: "Address Book", iconCls: 'icon-addressbook16',
					flex: 1, region: 'center', layout: 'fit',
					split:false, closable:false, stateful: false, frame: false, multiSelect: false,
					store:  CreateStore(
							'StoreAddressBook'+data.id,
							'address_t',
							'api2client.php?action=listreceivedbyaddress&serverID='+ data.id,
							'bbarAddressBook'+_panelID
					),
					listeners: {
						activate: function() {
							Ext.getCmp("AddressBook"+_panelID).store.load();
						}
					},
					plugins:[
						Ext.create('Ext.grid.plugin.RowEditing',{ 
							clicksToEdit:1,
							listeners: {
								edit: function (editor,e) {
									 var grid = e.grid;
									 var record = e.record;
									 var opt={ action: "setaccount",
										 serverID: data.id,
										 address: record.data.address,
										 account: record.data.account
									};
									Request2Api(opt, Ext.getCmp('AddressBook'+_panelID).store );
								}
							}
						})
					],
					columns: [ { text: 'LABEL', dataIndex: 'account', flex:1, field: 'textfield'}, { text: 'ADDRESS', dataIndex: 'address', flex:1} ],
					dockedItems: [
						{ xtype: 'toolbar', dock: 'top',
							items: [
								{iconCls: 'icon-refresh', text: 'Refresh', disabled: false, 
									handler: function(){ Ext.getCmp('AddressBook'+_panelID).store.load(); }
								}
							]
						}
					],
					bbar: [
						{html:'Status:', xtype:'label', style:'font-weight:bold;'},
						{id:'bbarAddressBook'+_panelID, xtype:'label', html: '&nbsp;&nbsp;<img src="images/loading-balls.gif" alt="Sorgulan?yor..." />'}
					]
				}),
				Ext.create('Ext.grid.Panel', {
					id: "Peers"+_panelID, title: "Peers",
					flex: 1, iconCls: 'icon-peer16', region: 'center', layout: 'fit',
					split:true, closable:false, stateful: false, frame: false, multiSelect: false,
					deferredRender: true,
					store:  CreateStore(
						'StorePeers'+data.id,
						'p2p_t',
						'api2client.php?action=p2p&serverID='+ data.id,
						'bbarP2PList'+_panelID
					),
					listeners: {
						activate: function() {
							Ext.getCmp("Peers"+_panelID).store.load();
						}
					},
					columns: [
						{ text: 'ADDRESS', dataIndex: 'addr', flex:1 },
						{ text: 'SERVICES', dataIndex: 'services' },
						{ text: 'LAST SEND', dataIndex: 'lastsend' },
						{ text: 'LAST RECV', dataIndex: 'lastrecv' },
						{ text: 'BYTES SEND', dataIndex: 'bytessent' },
						{ text: 'BYTES RECV', dataIndex: 'bytesrecv' },
						{ text: 'CONNTIME', dataIndex: 'conntime' },
						{ text: 'VERSION', dataIndex: 'version' },
						{ text: 'SUB VERSION', dataIndex: 'subver' },
						{ text: 'INBOUND', dataIndex: 'inbound' },
						{ text: 'STARTING HEIGHT', dataIndex: 'startingheight', hidden:true },
						{ text: 'BANSCORE', dataIndex: 'banscore'}
					],
					dockedItems: [
						{ xtype: 'toolbar', dock: 'top', 
							items: [
								{iconCls: 'icon-refresh', text: 'Refresh', disabled: false, 
									handler: function(){ Ext.getCmp('Peers'+_panelID).store.load(); }
								}
							]
						}
					],
					bbar: [
						{html:'Status:', xtype:'label', style:'font-weight:bold;'},
						{id:'bbarP2PList'+_panelID, xtype:'label', html: '&nbsp;&nbsp;<img src="images/loading-balls.gif" alt="Sorgulan?yor..." />'}
					]
				})
			]
		});
		Ext.getCmp('TabPanelCenter').add(newTab);
		Ext.getCmp('TabPanelCenter').setActiveTab(_panelID );
		
	}
	
	var PanelCenter=Ext.create('Ext.tab.Panel', {
		region: 'center',
		deferredRender: false,
		id: 'TabPanelCenter',
		stateId: 'TabPanelCenter',
		//layout: 'fit',
		split:false, closable:false, collapsible: false, stateful: false, frame: true,
		items:[
			{
				xtype:'panel',
				id: "TbPnlAbout", title:"Information", frame: true, iconCls:"icon-orca", split: true,
				region: 'center', autoHeight: true, layout: 'fit',
				items:[
				/**start**/
				Ext.create('Ext.container.Container', {
					layout: { type: 'vbox', align: 'stretch' },
					items: [
						{
							xtype: 'container', flex: 1,
							layout: {  type: 'hbox', align: 'stretch' },
							items: [
							
								/**chart2*/
								
								Ext.create('widget.panel', {
									title: 'Rpc Server Balance Graph',
									width: '100%', layout: 'fit',
									iconCls: 'icon-money16',
									tools: [{ type: 'refresh', handler: function() { Ext.getCmp('RpcServerGraph').store.load(); } }],
									margins: 8,
									items:[
									/*
									 * 
									 * 	type: 'column', axis: 'left', gutter: 80, xField: 'account', yField: 'balance',
													highlight: true,
													tips: {
														trackMouse: true,
														width: 250, height: 28,
														renderer: function(storeItem, item) {
															this.setTitle(storeItem.get('account') + ': #' + storeItem.get('balance') );
														}
													},
													* 
													* */
										Ext.create('Ext.chart.Chart', {
											animate: true, 
											shadow: true,
											
											layout: 'fit',
											id: 'RpcServerGraph',
											store: Ext.create('Ext.data.Store', {  model: 'rpc_server_balance_t', autoLoad: true, single: true}),
											axes: [
											{ type: 'Numeric', position: 'left', fields: ['balance'], grid: true },
											{ type: 'Category', position: 'bottom', fields: ['title'], label: { rotate: { degrees: 270 } } }
											],
											series: [
												{ type: 'column', axis: 'left', gutter: 80, xField: 'title', yField: 'balance',
													highlight: true,
													label: {
														display: 'insideEnd',
														field: 'balance',
														orientation: 'vertical',
														color: '#FFFFFF', 
														'text-anchor': 'middle'
													},
													tips: {
														width: 250, height:20,
														trackMouse: true,
														renderer: function(storeItem, item) {
															this.setTitle(storeItem.get('title') + ': #' + storeItem.get('balance') );
														}
													},
													renderer: function(sprite, record, attr, index, store) {
														var value =  Math.abs(Math.floor((record.data.balance%11)));
														var color = [
															'rgb(114, 19, 15)',  'rgb(92, 48, 71)',  'rgb(81, 114, 179)', 
															'rgb(196, 185, 183)', 'rgb(13, 111, 62)', 'rgb(158, 129, 133)',
															'rgb(0,0,255)', 'rgb(121,121,127)', 'rgb(30,30,0)', 'rgb(127,39,39)',
															'rgb(50, 107, 126)'][value];
														return Ext.apply(attr, {
															fill: color
														});
													}
												}
											]
										})
									]
								})
							]
						}
					]
				})
				
				/*** end */
				]
			}
		]
	});
	var viewport = Ext.create('Ext.Viewport', {
		id: 'ViewPort', layout: 'border',
		items: [ 
			{
				id: 'PanelNorth', region: 'north', height: 69, layout: {  type: 'hbox', align : 'middle' }, baseCls: 'x-plain',
				defaults: { baseCls: 'x-plain'},  border: false, margins: '2 0 5 0',
				items: [
					{ margins: '0 0 0 0', html: '<img height="60" width="150" src="images/logo.png"/>' },
					{ minWidth: 200, flex: 1, html: 
						Ext.String.format('<span class="x-panel-header-text"><h2>{0}<br>Ver: {1}</h2></span>', scriptname, scriptver) },
					new Ext.Button({ id: 'MenuBtnLogout', visible: true,margins: '0 3 0 0',width:75, text: 'Logout', scale: 'large', rowspan: 3, iconCls: 'icon-logout', iconAlign: 'top',cls: 'x-btn-as-arrow', handler: function() { 
						window.location.href='logout.php';} }),
					new Ext.Button({ id: 'MenuBtnBackup', visible: true, margins: '0 3 0 0',width:75,text: 'Backup!', scale: 'large', rowspan: 3, iconCls: 'icon-backup', iconAlign: 'top',cls: 'x-btn-as-arrow', handler: function() { 
							Ext.MessageBox.confirm('Confirm', "Are you sure you want backup all wallets?",
								function (id, value) {
									if (id === 'yes') {
										WalletBackup();
									}
								}, this);
						}
					}),
					new Ext.Button({ id: 'MenuBtnDonate', visible: true, margins: '0 3 0 0',width:75,text: 'Donate', scale: 'large', rowspan: 3, iconCls: 'icon-donate', iconAlign: 'top',cls: 'x-btn-as-arrow', handler: function() { showDonateWindow();} }),
					new Ext.Button({ id: 'MenuBtnPanic', visible: true, margins: '0 3 0 0',width:75,text: '!!! PANIC !!!', scale: 'large', rowspan: 3, iconCls: 'icon-panic', iconAlign: 'top',cls: 'x-btn-as-arrow', handler: function() {
						 window.location.href='panic.php';
						 }})
				]
			},
			PanelSouth, PanelCenter,PanelWest,PanelEast
		]
	});
});

