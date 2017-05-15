(function ($)
{
    /**
     * Creates jQuery plugin for crypto fields
     * @returns DxCryptoField
     */
    $.fn.DxCryptoField = function ()
    {
        return this.each(function ()
        {
            if ($(this).data('dx_is_init') == 1) {
                return;
            }

            this.crypto = new $.DxCryptoField($(this));

            if (window.DxCrypto.certificate &&
                    window.DxCrypto.certificate.privateKey &&
                    $(this).data('is-decrypted') != 1) {
                window.DxCrypto.decryptFields($(this));
            }
        });
    };

    /**
     * Class for managing crypto fields
     * @type DxCryptoField 
     */
    $.DxCryptoField = function (domObject) {
        /**
         * Field's DOM object which is related to this class
         */
        this.domObject = domObject;

        // Initializes class
        this.init();
    };

    /**
     * Initializes component
     * @returns {undefined}
     */
    $.extend($.DxCryptoField.prototype, {
        /**
         * Initializes field
         * @returns {undefined}
         */
        init: function () {
            var self = this;

            self.domObject.data('dx_is_init', 1);

            self.addDecryptButton();
        },
        /**
         * Replaces field's html with button for decryption
         * @returns {undefined}
         */
        addDecryptButton: function () {
            var self = this;

            self.domObject.hide();

            var button = $('<button class="btn btn-xs default dx-crypto-decrypt-btn"> ' +
                    Lang.get('crypto.decrypt_btn') +
                    ' <i class="fa fa-lock"></i></button>');

            self.domObject.after(button);

            button.click(function (event) {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation();
                window.DxCrypto.decryptFields($('.dx-crypto-field'));
            });
        },
        setAccessError: function () {
            var label = '<span class="label label-danger"> ' + Lang.get('crypto.e_no_access') + ' </span>';

            this.domObject.next('.dx-crypto-decrypt-btn').remove();
            this.domObject.after(label);
        },
        /**
         * Gets value of current element. It can be input or other container (e.g. div, span)
         * @returns {string}
         */
        getValue: function (callback) {
            var value;
            if (this.domObject.is('input') || this.domObject.is('textarea')) {
                value = this.domObject.val();
            } else {
                value = this.domObject.html();
            }

            if (this.domObject.data('is-decrypted')) {
                value = window.DxCrypto.stringToArrayBuffer(value);
            } else {
                value = window.DxCrypto.hexStringToArrayBuffer(value);
            }

            callback(value);
        },
        /**
         * Sets value of current element. It can be input or other container (e.g. div, span)
         * @param {string} value It will be set to element
         * @param {boolean} isVisible Shows element and hides decrypt button
         * @returns {undefined}
         */
        setValue: function (value, isVisible) {
            if (value != '') {
                if (this.domObject.data('is-decrypted')) {
                    value = window.DxCrypto.arrayBufferToHexString(value);
                } else {
                    value = window.DxCrypto.arrayBufferToString(value);
                }
            }

            if (this.domObject.is('input') || this.domObject.is('textarea')) {
                this.domObject.val(value);
            } else {
                this.domObject.html(value);
            }

            if (isVisible) {
                // Shows field
                this.showField();
            } else {
                this.addDecryptButton();
            }
        },
        /**
         * Shows field
         * @returns {undefined}
         */
        showField: function () {
            this.domObject.next('.dx-crypto-decrypt-btn').remove();
            this.domObject.show();
        }
    });
})(jQuery);

// ajaxComplete ready
$(document).ready(function () {
    // Initializes all found worklfow containers
    $('.dx-crypto-field').DxCryptoField();
});

$(document).ajaxComplete(function () {
    // Initializes all found worklfow containers
    $('.dx-crypto-field').DxCryptoField();
});
