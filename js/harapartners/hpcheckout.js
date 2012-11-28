var HpCheckout = Class.create();
HpCheckout.prototype = {
	/**************************
	* Constructor
	*/
	initialize: function( updateUrl, submitUrl, successUrl, blocks ) {
		this.data.updateUrl = updateUrl;
		this.data.submitUrl = submitUrl;
		this.data.successUrl = successUrl;
		for( var blockIndex = 0; blockIndex < blocks.length; blockIndex ++ ) {
			this.data.blocks[ blocks[ blockIndex ].code ] = {
				status: '',
				html: '',
				message: '',
				wrapperId: blocks[ blockIndex ].wrapperId,
				formId: blocks[ blockIndex ].formId
			}
			this.data.steps.push( blocks[ blockIndex ].code );
			this.data.forms[ blocks[ blockIndex ].formId ] = blocks[ blockIndex ].code;
		}
	},
	
	//========================//
	//=========Data===========//
	//========================//
	data: {
		blocks: {},
		steps: [],
		forms: {},
		updateUrl: '',
		submitUrl: '',
		successUrl: ''
	},
	
	//========================//
	//=========Model==========//
	//========================//
	/******************************
	* @param string|array|null blockCodes
	* @return an array of related blocks
	*/
	getBlocks: function( blockCodes ) {
		var returnBlocks = {};
		preparedBlockCodes = this._prepareMixinParams( blockCodes );
		for( var blockIndex = 0; blockIndex < preparedBlockCodes.length; blockIndex ++ ) {
			if( this.data.blocks.hasOwnProperty( preparedBlockCodes[ blockIndex ] ) ) {
				returnBlocks[ preparedBlockCodes[ blockIndex ] ] = this.data.blocks[ preparedBlockCodes[ blockIndex ] ];
			}
		}
		return returnBlocks;
	},
	
	/*********************************
	* @param array blockInfo
	*/
	setBlocks: function( blocksData ) {
		for( var blockCode in blocksData ) {
			if( this.data.blocks.hasOwnProperty( blockCode ) ) {
				for( var attribute in blocksData[ blockCode ] ) {
					if( this.data.blocks[ blockCode ].hasOwnProperty( attribute ) ) {
						this.data.blocks[ blockCode ][ attribute ] = blocksData[ blockCode ][ attribute ];
					}
				}
			}
		}
	}, 
	
	//===========================//
	//============View===========//
	//===========================//
	/***************************
	* @param array|string|null blockCode
	*/
	renderBlocks: function( blockCodes ) {
		var blocksData = this.getBlocks( blockCodes );
		for( var blockCode in blocksData ) {
			var errorMessage = '';
			if( blocksData[ blockCode ].message ) {
				if( blocksData[ blockCode ].message instanceof Array ) {
					var message = blocksData[ blockCode ].message.join( '<br />' );
				} else {
					var message = blocksData[ blockCode ].message;
				}
				errorMessage = '<div class="hpcheckout-error-message">' + message + '</div>';
			}
            var messageHtml = blocksData[blockCode].html;
			jQuery( '#' + blocksData[ blockCode ].wrapperId + ' .checkout-content' ).html( messageHtml );
		}
	},
	
	//================================//
	//===========View Helper==========//
	//================================//
	/********************************
	* show & hide
	*/
	copyBillingToShipping: function() {
		var billingFormId = this.data.blocks.billing.formId;
		var shippingFormId = this.data.blocks.shipping.formId;
		var billingData = jQuery( 'input, select', '#' + billingFormId ).serializeArray();
		var shippingFormFields = jQuery( 'input, select', '#' + shippingFormId );
		
		//country need to be updated before everything
		jQuery('select#shipping\\:country_id').val(jQuery('select#billing\\:country_id').val());
		shippingRegionUpdater.update();
		
		shippingFormFields.each( function() {
			for( var billingIndex = billingData.length - 1; billingIndex >= 0 ; billingIndex -- ) {
				if( jQuery( this ).attr( 'name' ).replace( 'shipping', 'billing' ) == billingData[ billingIndex ].name ) {
					jQuery( this ).val( billingData[ billingIndex ].value );
				}
			}
		});

		jQuery( '#shipping\\:postcode' ).change();
	}, 
	
    switchPaymentMethod: function(payment_method) {
        if (payment_method=="paypal_express") {
            jQuery("#cc_data").hide();
            jQuery().hide();
        }        
        jQuery("#payment_form_" + payment_method).show();
	},
	
	switchAddress: function() {
		var clickedAddress = jQuery( this ); 
		var blockType = '';
		var hpcheckoutObject = HpCheckout.prototype;
				
		if( clickedAddress.attr( 'id' ) == 'billing-address-select' ) {
			blockType = 'billing';
		} else if( clickedAddress.attr( 'id' ) == 'shipping-address-select' ) {
			blockType = 'shipping';
		}
		
		if( clickedAddress.val() == '' ) {
			jQuery('#hpcheckout-billing-form :input').each(function(i) {
				if(this.id != 'button_ship_to') {
					jQuery("[id='" + this.id + "']").attr('disabled',false);
				}
			});
		
			jQuery( '#' + hpcheckout.data.blocks[ blockType ].formId + ' input' ).val( '' );
            if(blockType == 'billing') {
                jQuery( '#billing\\:selected' ).val('');
            }
		} else {
			jQuery('#hpcheckout-billing-form :input').each(function(i) {
				if(this.id != 'button_ship_to') {
					jQuery("[id='" + this.id + "']").attr('disabled',true);
				}
			});
			
			if( hpcheckoutAddresses[ clickedAddress.val() ] ) {
				jQuery('select#' + blockType +'\\:country_id').val( hpcheckoutAddresses[ clickedAddress.val() ][ 'country_id' ] );

				if( blockType == 'billing' ) {
					billingRegionUpdater.update();
				} else if ( blockType == 'shipping' ) {
					shippingRegionUpdater.update();
				}
				
				jQuery( 'input, select', '#' + hpcheckout.data.blocks[ blockType ].formId ).each( function(){
					jQuery( this ).val( hpcheckoutAddresses[ clickedAddress.val() ][ jQuery( this ).attr( 'id' ).replace( blockType + ':', '' ) ] );
				});
								
				if( blockType == 'shipping' ) {
					jQuery( '#shipping\\:postcode' ).change();
				}	
								
                if(blockType == 'billing') {
                    jQuery( '#billing\\:selected' ).val(jQuery( '#billing-address-select' ).val());
                }
			} 
		}
	},

	renderErrorMessage: function( message ) {
		jQuery( '#error-message-wrapper' ).html( message );
	},
	//==============================//
	//==========Controller==========//
	//==============================//
	/*******************************
	* Handler when listened fields are updated
	*/
	update: function( doUpdatePayment ) {
		var formId = jQuery( this ).parents( 'form' ).eq( 0 ).attr( 'id' );
		var hpcheckoutObject = HpCheckout.prototype;
		var step = hpcheckoutObject.data.forms[ formId ];
		var blocksToUpdate = "";
		
		if(!doUpdatePayment) {		
            blocksToUpdate = hpcheckoutObject.getBlocksToUpdate( step );
		} else {
		    blocksToUpdate = ['shipping','review'];
		}
				
		if( hpcheckoutObject.validate( step ) ) {
			// var postData = hpcheckoutObject.getFormData( step );
			var postData = hpcheckoutObject.getFormData();
			postData += '&currentStep=' + step;
			hpcheckoutObject.ajaxRequest( postData, doUpdatePayment );
		}
	},
		
	updatePayment: function() {
		var formId = jQuery( this ).parents( 'form' ).eq( 0 ).attr( 'id' );
		var hpcheckoutObject = HpCheckout.prototype;
		var step = hpcheckoutObject.data.forms[ formId ];
		var blocksToUpdate = hpcheckoutObject.getBlocksToUpdate( step );
		// var postData = hpcheckoutObject.getFormData( step );
		var postData = hpcheckoutObject.getFormData();
		postData += '&currentStep=' + step + '&updatePayment=true';
		hpcheckoutObject.ajaxRequest( postData );
	},
	
	submit: function() {
	    //good time to validate CC types
		jQuery(".cc_types input[type='radio']").addClass("validate-one-required");
	
		if( ! this.validate() ) {
			return;
		}
		
        //IE grabs placeholder text from orders in lew of of an actual value
        //this fix removes values explicitly when they match their placeholder text
        jQuery("#hpcheckout-wrapper").find('input[placeholder]').each(function() {
            var e = $(this);
            if (e.id) {
                if (jQuery("[id='" + e.id + "']").attr('value') === jQuery("[id='" + e.id + "']").attr('placeholder')) {
                    jQuery("[id='" + e.id + "']").val('');
                }
            }
        });

        var checkoutObject = this;
		var postData = this.getFormData();
		
		postData += '&updatePayment=true';
		this.throbberOn();		
		
		jQuery.ajax({
			url: this.data.submitUrl,
			dataType: "json",
			type: "POST",
			data: postData,
			error: function(data) {
				checkoutObject.throbberOff();
				checkoutObject.renderErrorMessage( 'Please refresh the current page.' );
			},
			success: function( response ) {
			     //console.log(response);
				 if ( response.redirect ) {
					 location.href = response.redirect;
					 return;
				 }
				if( ! response.status ) {
					window.location = checkoutObject.data.successUrl;
				} else {
					if( response.message ) {
						checkoutObject.renderErrorMessage( response.message );
						checkoutObject.throbberOff();
					}else{
						checkoutObject.setBlocks( response.blocks );
						checkoutObject.throbberOff();
						checkoutObject.renderBlocks();
					}
				}
			}
		});
	}, 
	
	//===============================//
	//=======Controller Helper=======//
	//===============================//
	/********************************
	*
	*/
	getFormIds: function( blockCodes ) {
		var updateBlocks = this._prepareMixinParams( blockCodes );
		var returnFormIdArray = [];
		for( var blockIndex = 0; blockIndex < updateBlocks.length; blockIndex ++ ) {
			if( this.data.blocks.hasOwnProperty( updateBlocks[ blockIndex ] ) && this.data.blocks[ updateBlocks[ blockIndex ] ].formId ) {
				returnFormIdArray.push( this.data.blocks[ updateBlocks[ blockIndex ] ].formId );
			}
		}
		return returnFormIdArray;
	},
	
	getWrapperIds: function( blockCodes ) {
		var updateBlocks = this._prepareMixinParams( blockCodes );
		var returnWrapperIdArray = [];
		for( var blockIndex = 0; blockIndex < updateBlocks.length; blockIndex ++ ) {
			if( this.data.blocks.hasOwnProperty( updateBlocks[ blockIndex ] ) && this.data.blocks[ updateBlocks[ blockIndex ] ].wrapperId ) {
				returnWrapperIdArray.push( this.data.blocks[ updateBlocks[ blockIndex ] ].wrapperId );
			}
		}
		return returnWrapperIdArray;
	},
	
	getBlocksToUpdate: function( blockCodes ) {
		var updateBlocks = this._prepareMixinParams( blockCodes );
		var pilot = this.data.steps.length;
		var returnBlocks = [];
		for( var blockIndex = 0; blockIndex < updateBlocks.length; blockIndex ++ ) {
			currentStep = this.data.steps.indexOf( updateBlocks[ blockIndex ] );
			if( pilot >= currentStep ) {
				pilot = currentStep;
			}
		}
		for( var i = pilot; i < this.data.steps.length; i ++ ) {
			returnBlocks.push( this.data.steps[ i ] );
		}
		return returnBlocks;
	},
	
	validate: function( blockCodes ) {
		var affectedFormIds = this.getFormIds( blockCodes );
		for( var formIndex = 0; formIndex < affectedFormIds.length; formIndex ++ ) {
			var validator = new Validation( affectedFormIds[ formIndex ] );
			if ( ! validator || ! validator.validate()) {
				return false;
			}
		}
		return true;
	},
	
	throbberOn: function( blockCodes ) {
		var affectedBlocks = this.getBlocks( blockCodes );
		for( var blockIndex in affectedBlocks ) {
			jQuery( '#' + affectedBlocks[ blockIndex ].wrapperId + ' .spinner' ).show();
			jQuery( 'input, select, button', '#' + affectedBlocks[ blockIndex ].wrapperId ).attr( 'disabled', 'disabled' );
		}
	},
	
	throbberOff: function( blockCodes ) {
		var affectedBlocks = this.getBlocks( blockCodes );
		for( var blockIndex in affectedBlocks ) {
			jQuery( 'input, select, button', '#' + affectedBlocks[ blockIndex ].wrapperId ).removeAttr( 'disabled' );
			jQuery( '#' + affectedBlocks[ blockIndex ].wrapperId + ' .spinner' ).hide();
		}
	},
	
	getFormData: function( blockCodes ) {
		var affectedFormIds = this.getFormIds( blockCodes ) ;
		var returnFormDataArray = [];
		for( var blockIndex = 0; blockIndex < affectedFormIds.length; blockIndex ++ ) {
			returnFormDataArray.push( jQuery( '#' + affectedFormIds[ blockIndex ] ).serialize() );
		}
		return returnFormDataArray.join( '&' );
	}, 
	
	ajaxRequest: function( postData, doUpdatePayment ) {
		var checkoutObject = this;
		
		var blocksToUpdate = "";
		
		//chekig if payment block should be updated or not
		if(!doUpdatePayment) {
            blocksToUpdate = this.getBlocksToUpdate( postData[ 'currentStep' ] );
		} else {
            blocksToUpdate = ['shipping','review'];	
		}
				
		this.throbberOn( blocksToUpdate );
		jQuery.ajax({
			url: this.data.updateUrl,
			type: "POST",
			data: postData,
			dataType: "json",
			error: function() {
				checkoutObject.throbberOff();
				checkoutObject.renderErrorMessage( 'Please refresh the current page.' );
			},
			success: function( response ) {
				if(response.status && response.message) {
					checkoutObject.renderErrorMessage( response.message );
					checkoutObject.throbberOff();
				}else{
					checkoutObject.setBlocks( response );
					checkoutObject.throbberOff();
					checkoutObject.renderBlocks( blocksToUpdate );
				}
			}
		});
	},
	
	//==============================//
	//===========Utility============//
	//==============================//
	_prepareMixinParams: function( blockCodes ) {
		preparedBlockCodes = new Array();
		if( ! blockCodes ) {
			for( var blockCode in this.data.blocks ) {
				preparedBlockCodes.push( blockCode );
			}
		} else if( typeof( blockCodes ) == 'string' ) {
			preparedBlockCodes[ 0 ] = blockCodes;
		} else if( blockCodes instanceof Array ) {
			preparedBlockCodes = blockCodes;
		}
		return preparedBlockCodes;
	}
}
