(function ($)
{
    /**
     * Creates jQuery plugin for crypto fields
     * @returns DxCryptoUserPanel
     */
    $.fn.DxCryptoUserPanel = function ()
    {
        return this.each(function ()
        {
            this.crypto = new $.DxCryptoUserPanel($(this));
        });
    };

    /**
     * Class for managing crypto fields
     * @type DxCryptoUserPanel 
     */
    $.DxCryptoUserPanel = function (domObject) {
        /**
         * Field's DOM object which is related to this class
         */
        this.domObject = domObject;

        // Initializes class
        this.init();
    };
    /**
     * Extends crypto library prototype
     * @param {object} param1 Crypto library
     * @param {function} param2 Extended functionality
     */
    $.extend($.DxCryptoUserPanel.prototype, {
        /**
         * Initializes user panel
         * @returns {undefined}
         */
        init: function () {
            var self = this;

            if (!window.crypto || !window.crypto.subtle) {
                return;
            }

            if (!window.TextEncoder) {
                return;
            }

            var self = this;

            if (self.domObject.data('dx_is_init') == 1) {
                return;
            }

            self.domObject.data('dx_is_init', 1);

            self.domObject.find('.dx-crypto-generate-cert-btn').click(this.openGenerateCertificate);

            self.domObject.find('.dx-crypto-generate-new-cert-btn').click(function () {
                var title = Lang.get('crypto.btn_generate_new_cert');
                var body = Lang.get('crypto.w_confirm_generate_new_cert');

                PageMain.showConfirm(self.openGenerateCertificate, null, title, body);
            });

            $('#dx-crypto-modal-generate-cert').on('click', '.dx-crypto-modal-gen-accept', this.validateCertPassword);
        },
        /**
         * Opens modal windows where user inputs password for certificate
         * @returns {undefined}
         */
        openGenerateCertificate: function () {
            var modal = $('#dx-crypto-modal-generate-cert');

            modal.on('shown.bs.modal', function () {
                modal.find('#dx-crypto-modal-gen-input-password').focus();
            });

            modal.modal('show');
        },
        /**
         * Validates users password
         * @returns {Boolean}
         */
        validateCertPassword: function () {
            var password = $('#dx-crypto-modal-gen-input-password').val();
            var password_2 = $('#dx-crypto-modal-gen-input-password-again').val();

            if (!password || password.length <= 0 || !password_2 || password_2.length <= 0) {
                notify_err(Lang.get('crypto.e_cert_both_psw'));
                return false;
            }

            if (password.length < 8) {
                notify_err(Lang.get('crypto.e_cert_psw_short'));
                return false;
            }

            if (password != password_2) {
                notify_err(Lang.get('crypto.e_cert_psw_not_match'));
                return false;
            }

            show_page_splash(1);

            window.DxCrypto.createPasswordKey(password)
                    .then(function (passwordKey) {
                        window.DxCrypto.generateUserCert(passwordKey);
                    })
                    .catch(window.DxCrypto.catchError);

            $('#dx-crypto-modal-generate-cert').modal('hide');
            $('#dx-crypto-modal-gen-input-password').val('');
            $('#dx-crypto-modal-gen-input-password-again').val('');
        }
    });
})(jQuery);

// ajaxComplete ready
$(document).ready(function () {
    // Initializes all found worklfow containers
    $('.dx-crypto-user_panel-page[data-dx_is_init!=1]').DxCryptoUserPanel();
});

$(document).ajaxComplete(function () {
    // Initializes all found worklfow containers
    $('.dx-crypto-user_panel-page[data-dx_is_init!=1]').DxCryptoUserPanel();
});
