Ext.onReady(function() {
	Ext.QuickTips.init();
	
	Ext.define('rpc_server_t', {
		extend: 'Ext.data.Model',
		fields: ['id', 'host', 'port', 'useSSL', 'username', 'password', 'created', 'title', 'icon', 'ca_path', 'backup_path'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=ListRpcServers',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: { 
				exception: function (proxy, request, operation) { 
					ShowErrorMessage(request); 
				}
			}
		}
	});
	Ext.define('rpc_server_balance_t', {
		extend: 'Ext.data.Model',
		fields: ['server', 'title', 'balance'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=getsrvtotal',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: { 
				exception: function (proxy, request, operation) { 
					ShowErrorMessage(request); 
				}
			}
		}
	});
	Ext.define('rpc_server_backup_info_t', {
		extend: 'Ext.data.Model',
		fields: ['title', 'server', 'backupfilepath', 'status'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=backup',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: { 
				exception: function (proxy, request, operation) { 
					ShowErrorMessage(request); 
				}
			}
		}
	});
	


		
	Ext.define('log_t', {
		extend: 'Ext.data.Model',
		fields: ['id', 'created', 'node', 'message', 'extrainfo', 'status'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=logs',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});

	
	
	Ext.define('address_t', {
		extend: 'Ext.data.Model',
		fields: ['address', 'account', 'amount', 'confirmations'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=listreceivedbyaddress',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	
	Ext.define('p2p_t', {
		extend: 'Ext.data.Model',
		fields: ['addr', 'services', 'lastsend', 'lastrecv', 'bytessent', 'bytesrecv', 'conntime', 'version', 'subver', 'inbound', 'startingheight', 'banscore'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=listreceivedbyaddress',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	
	Ext.define('basicinfo_t', {
		extend: 'Ext.data.Model',
		fields: [
        'version', 'protocolversion', 'walletversion', 'balance', 'blocks', 'timeoffset', 'connections', 'proxy', 'difficulty',
		'testnet', 'keypoololdest', 'keypoolsize', 'paytxfee', 'errors'
		],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=getinfo',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	
	Ext.define('transactionlist_t', {
		extend: 'Ext.data.Model',
		fields: [
			'account', 'address', 'category', 'amount', 'confirmations',
			'blockhash', 'blockindex', 'blocktime', 'txid', 'time', 
			'timereceived'
		],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=transactionlist',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	Ext.define('transaction_t', {
		extend: 'Ext.data.Model',
		fields: ['amount', 'confirmations', 'confirmations', 'time', 'details'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=transaction',//set this into blackhole
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	Ext.define('transaction_details_t', {
		extend: 'Ext.data.Model',
		fields: ['account', 'address', 'category', 'amount', 'fee'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=transaction',//set this into blackhole
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	Ext.define('wallet_info_t', {
		extend: 'Ext.data.Model',
		fields: ['key', 'value'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=getinfo',//set this into blackhole
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	
	Ext.define('account_info_t', {
		extend: 'Ext.data.Model',
		fields: ['account', 'balance'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=getinfo',//set this into blackhole
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	Ext.define('brutes_t', {
		extend: 'Ext.data.Model',
		fields: ['id', 'created', 'ip', 'logindata'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=getbrutes',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	
	
	Ext.define('icons_t', {
		extend: 'Ext.data.Model',
		fields: ['key', 'value'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=getCoinIcons',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});
	
	Ext.define('account_address_t', {
		extend: 'Ext.data.Model',
		fields: ['address'],
		proxy: {
			type: 'ajax', url: 'api2client.php?action=blackhole',
			timeout: 7200000,
			reader: { type: 'json', root: 'root' },
			listeners: {
				exception: function (proxy, request, operation) {
					ShowErrorMessage(request);
				}
			}
		}
	});


		
		



});


