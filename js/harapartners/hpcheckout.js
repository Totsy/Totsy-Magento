function hideTopCart() {
    jQuery("#topCartContent").stop().fadeTo(200, 0).hide()
}
var HpCheckout = Class.create();
HpCheckout.prototype = {
    initialize: function(a, b, c, d) {
        this.data.updateUrl = a;
        this.data.submitUrl = b;
        this.data.successUrl = c;
        for (var e = 0; e < d.length; e++) {
            this.data.blocks[d[e].code] = {
                status: "",
                html: "",
                message: "",
                wrapperId: d[e].wrapperId,
                formId: d[e].formId
            };
            this.data.steps.push(d[e].code);
            this.data.forms[d[e].formId] = d[e].code
        }
    },
    data: {
        blocks: {},
        steps: [],
        forms: {},
        updateUrl: "",
        submitUrl: "",
        successUrl: ""
    },
    getBlocks: function(a) {
        var b = {};
        preparedBlockCodes = this._prepareMixinParams(a);
        for (var c = 0; c < preparedBlockCodes.length; c++) {
            if (this.data.blocks.hasOwnProperty(preparedBlockCodes[c])) {
                b[preparedBlockCodes[c]] = this.data.blocks[preparedBlockCodes[c]]
            }
        }
        return b
    },
    setBlocks: function(a) {
        for (var b in a) {
            if (this.data.blocks.hasOwnProperty(b)) {
                for (var c in a[b]) {
                    if (this.data.blocks[b].hasOwnProperty(c)) {
                        this.data.blocks[b][c] = a[b][c]
                    }
                }
            }
        }
    },
    renderBlocks: function(a) {
        var b = this.getBlocks(a);
        for (var c in b) {
            var d = "";
            if (b[c].message) {
                if (b[c].message instanceof Array) {
                    var e = b[c].message.join("<br />")
                } else {
                    var e = b[c].message
                }
                d = '<div class="hpcheckout-error-messages error-msg onepage">' + e + "</div>"
            }
            var f = b[c].html;
            jQuery("#" + b[c].wrapperId + " .checkout-content").html(f);
        }
    },
    copyBillingToShipping: function() {
        var a = this.data.blocks.billing.formId;
        var b = this.data.blocks.shipping.formId;
        var c = jQuery("input, select", "#" + a).serializeArray();
        var d = jQuery("input, select", "#" + b);
        jQuery("select#shipping\\:country_id").val(jQuery("select#billing\\:country_id").val());
        shippingRegionUpdater.update();
        d.each(function() {
            for (var a = c.length - 1; a >= 0; a--) {
                if (jQuery(this).attr("name").replace("shipping", "billing") == c[a].name) {
                    jQuery(this).val(c[a].value)
                }
            }
        });
        jQuery("#shipping\\:postcode").change()
    },
    switchPaymentMethod: function(payment_method) {
        
        if (payment_method=="paypal_express") {
            jQuery("#cc_data").hide();
        }
    
        //jQuery('[id^="payment_form_"]').hide();
        jQuery("#payment_form_" + payment_method).show();
    },
    switchAddress: function() {
        var a = jQuery(this);
        var b = "";
        if (a.attr("id") == "billing-address-select") {
            b = "billing"
        } else if (a.attr("id") == "shipping-address-select") {
            b = "shipping"
        }
        if (a.val() == "") {
            jQuery("#" + hpcheckout.data.blocks[b].formId + " input").val("");
            if (b == "billing") {
                jQuery("#billing\\:selected").val("")
            }
        } else {
            if (hpcheckoutAddresses[a.val()]) {
                jQuery("select#" + b + "\\:country_id").val(hpcheckoutAddresses[a.val()]["country_id"]);
                if (b == "billing") {
                    billingRegionUpdater.update()
                } else if (b == "shipping") {
                    shippingRegionUpdater.update()
                }
                jQuery("input, select", "#" + hpcheckout.data.blocks[b].formId).each(function() {
                    jQuery(this).val(hpcheckoutAddresses[a.val()][jQuery(this).attr("id").replace(b + ":", "")])
                });
                if (b == "shipping") {
                    jQuery("#shipping\\:postcode").change()
                }
                if (b == "billing") {
                    jQuery("#billing\\:selected").val(jQuery("#billing-address-select").val())
                }
            }
        }
    },
    renderErrorMessage: function(a) {
        jQuery("#error-message-wrapper").html(a)
    },
    update: function() {
        var a = jQuery(this).parents("form").eq(0).attr("id");
        var b = HpCheckout.prototype;
        var c = b.data.forms[a];
        var d = b.getBlocksToUpdate(c);
        if (b.validate(c)) {
            var e = b.getFormData();
            e += "&currentStep=" + c;
            b.ajaxRequest(e)
        }
    },
    updatePayment: function() {
        var a = jQuery(this).parents("form").eq(0).attr("id");
        var b = HpCheckout.prototype;
        
        console.log(b);
        
        var c = b.data.forms[a];
        var d = b.getBlocksToUpdate(c);
        var e = b.getFormData();
        e += "&currentStep=" + c + "&updatePayment=true";
        b.ajaxRequest(e)
    },
    submit: function() {
        if (!this.validate()) {
            return
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
        
        var a = this;
        var b = this.getFormData();
        b += "&updatePayment=true";
        this.throbberOn();
        jQuery.ajax({
            url: this.data.submitUrl,
            dataType: "json",
            type: "POST",
            data: b,
            error: function() {
                a.throbberOff();
                a.renderErrorMessage("Please refresh the current page.")
            },
            success: function(b) {
                if (!b.status) {
                    window.location = a.data.successUrl;
                } else {
                    if (b.message) {
                        a.renderErrorMessage(b.message);
                        a.throbberOff()
                    } else {
                        a.setBlocks(b.blocks);
                        a.throbberOff();
                        a.renderBlocks()
                    }
                }
            }
        })
    },
    getFormIds: function(a) {
        var b = this._prepareMixinParams(a);
        var c = [];
        for (var d = 0; d < b.length; d++) {
            if (this.data.blocks.hasOwnProperty(b[d]) && this.data.blocks[b[d]].formId) {
                c.push(this.data.blocks[b[d]].formId)
            }
        }
        return c
    },
    getWrapperIds: function(a) {
        var b = this._prepareMixinParams(a);
        var c = [];
        for (var d = 0; d < b.length; d++) {
            if (this.data.blocks.hasOwnProperty(b[d]) && this.data.blocks[b[d]].wrapperId) {
                c.push(this.data.blocks[b[d]].wrapperId)
            }
        }
        return c
    },
    getBlocksToUpdate: function(a) {
        var b = this._prepareMixinParams(a);
        var c = this.data.steps.length;
        var d = [];
        for (var e = 0; e < b.length; e++) {
            currentStep = this.data.steps.indexOf(b[e]);
            if (c >= currentStep) {
                c = currentStep
            }
        }
        for (var f = c; f < this.data.steps.length; f++) {
            d.push(this.data.steps[f])
        }
        return d
    },
    validate: function(a) {
        var b = this.getFormIds(a);
        for (var c = 0; c < b.length; c++) {
            var d = new Validation(b[c]);
            if (!d || !d.validate()) {
                return false
            }
        }
        return true
    },
    throbberOn: function(a) {
        var b = this.getBlocks(a);
        for (var c in b) {
            jQuery("#" + b[c].wrapperId + " .spinner").show();
            jQuery("input, select, button", "#" + b[c].wrapperId).attr("disabled", "disabled")
        }
    },
    throbberOff: function(a) {
        var b = this.getBlocks(a);
        for (var c in b) {
            jQuery("input, select, button", "#" + b[c].wrapperId).removeAttr("disabled");
            jQuery("#" + b[c].wrapperId + " .spinner").hide()
        }
    },
    getFormData: function(a) {
        var b = this.getFormIds(a);
        var c = [];
        for (var d = 0; d < b.length; d++) {
            c.push(jQuery("#" + b[d] + ' :input[value!="."]').serialize())
        }
        return c.join("&")
    },
    ajaxRequest: function(a) {
        var b = this;
        var c = this.getBlocksToUpdate(a["currentStep"]);
        this.throbberOn(c);
        jQuery.ajax({
            url: this.data.updateUrl,
            type: "POST",
            data: a,
            dataType: "json",
            error: function() {
                b.throbberOff();
                b.renderErrorMessage("Please refresh the current page.")
            },
            success: function(a) {
                if (a.status && a.message) {
                    b.renderErrorMessage(a.message);
                    b.throbberOff()
                } else {
                    b.setBlocks(a);
                    b.throbberOff();
                    b.renderBlocks(c);
                    jQuery("input[placeholder], textarea[placeholder]").placeholder()
                }
            }
        })
    },
    _prepareMixinParams: function(a) {
        preparedBlockCodes = new Array;
        if (!a) {
            for (var b in this.data.blocks) {
                preparedBlockCodes.push(b)
            }
        } else if (typeof a == "string") {
            preparedBlockCodes[0] = a
        } else if (a instanceof Array) {
            preparedBlockCodes = a
        }
        return preparedBlockCodes
    }
}