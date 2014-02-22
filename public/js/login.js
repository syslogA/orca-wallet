Ext.onReady(function(){
    Ext.QuickTips.init();
    var pwin = new Ext.create('Ext.window.Window', {
		id: 'WindowLogin', modal: true,
		closable: false, closeAction: 'destroy',
		title: 'Login', layout: 'fit',
		resizable: false,
		items:[
			{ xtype : 'form', id : 'FormLogin', defaultType : 'field', frame: false, layout: 'fit',
				bodyPadding: 5, width: 400, url: "client2api.php", layout: 'anchor', defaults: { anchor: '100%'}, defaultType: 'textfield',
				items: [
					{ xtype:'hidden', name: 'action', value:'Auth', allowBlank: false, hidden:true},
					{ fieldLabel: 'Username', name: 'username', allowBlank: false},
					{ fieldLabel: 'Password', name: 'password', inputType: 'password', allowBlank: false}
				],
				buttons: [
					{text: 'Log In', formBind: true,  disabled: true,
						handler: function() {
							var form = this.up('form').getForm();
							if (form.isValid()) {
								var myMask = new Ext.LoadMask(Ext.getBody(), {id:'IDLoadingMask', msg:"Please wait..."}).show();
								
								form.submit({
									success: function(form, action) {
										var result = Ext.JSON.decode(action.response.responseText);
										var pMask=Ext.getCmp('IDLoadingMask');
										if ( pMask ) pMask.hide();
										if ( result.success ) {
											window.location='service.php';
										}
										else {
											Ext.Msg.alert(action.result.error.title, action.result.error.reason);
										}
									},
									failure: function(form, action) {
										var pMask=Ext.getCmp('IDLoadingMask');
										if ( pMask ) pMask.hide();
										
										if (action.failureType === Ext.form.Action.CONNECT_FAILURE)
											Ext.Msg.alert('Failure', 'Server reported:'+action.response.status+' '+action.response.statusText);
										else if (action.failureType === Ext.form.Action.SERVER_INVALID) {
											Ext.Msg.alert(action.result.error.title, action.result.error.reason);
										}
									}
								});
							}
						}
					}
				] 
			}
		]
	}).show();
});
